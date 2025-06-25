const namaModulIndex = columns.indexOf("nama_modul");
const componentIndex = columns.indexOf("component");
const typeIndex = columns.indexOf("type");

// Inisialisasi Univer
const { createUniver } = UniverPresets;
const { LocaleType, merge, BooleanNumber } = UniverCore;
const { defaultTheme } = UniverDesign;
const { UniverSheetsCorePreset } = UniverPresetSheetsCore;
const { UniverSheetsDataValidationPreset } = UniverPresetSheetsDataValidation;

const { univerAPI } = createUniver({
    locale: LocaleType.EN_US,
    locales: {
        [LocaleType.EN_US]: merge(
            {},
            UniverPresetSheetsCoreEnUS,
            UniverPresetSheetsDataValidationEnUS
        ),
    },
    theme: defaultTheme,
    presets: [UniverSheetsCorePreset(), UniverSheetsDataValidationPreset()],
});

// Fungsi untuk memetakan data ke kolom
function mapDataToColumns(comp) {
    let componentRow = {};

    componentRow[componentIndex] = comp.component || comp.name || "";

    if (typeIndex >= 0) {
        componentRow[typeIndex] = comp.code || "";
    }

    Object.entries(fieldMapping).forEach(([sourceField, targetColumn]) => {
        const colIndex = columns.indexOf(targetColumn);
        if (
            colIndex >= 0 &&
            comp[sourceField] !== undefined &&
            comp[sourceField] !== null
        ) {
            componentRow[colIndex] = comp[sourceField];
        }
    });

    columns.forEach((col, index) => {
        if (
            index !== componentIndex &&
            comp[col] !== undefined &&
            comp[col] !== null
        ) {
            componentRow[index] = comp[col];
        }
    });

    return componentRow;
}

const formula = univerAPI.getFormula();

function prepareComponentSheetData() {
    let data = {};
    const modulStartRows = {};
    let currentRow = 1;

    data[0] = {};
    columns.forEach((col, index) => {
        data[0][index] = {
            v: col,
            s: {
                bl: 1,
                ht: 2,
                vt: 2,
                fs: 11,
            },
        };
    });

    const adjustFormula = (formula, modulStartRow, isFilled) => {
        return formula.replace(/([A-Z]+)(\d+)/g, (match, col, rowNum) => {
            const newRow = isFilled
                ? parseInt(rowNum)
                : modulStartRow + parseInt(rowNum) - 2;
            return `${col}${newRow}`;
        });
    };

    const modulRowStyle = {
        bg: { rgb: "#faf59b" },
        bl: 1,
        bd: { t: { s: 1 }, b: { s: 1 }, l: { s: 1 }, r: { s: 1 } },
    };

    if (groupedComponents?.array) {
        const uniqueGroups = [];
        const processedModuls = new Set();

        groupedComponents.array.forEach((group) => {
            const modulName = group.modul?.nama_modul || "";
            if (!processedModuls.has(modulName)) {
                uniqueGroups.push(group);
                processedModuls.add(modulName);
            }
        });

        uniqueGroups.forEach((group, modulIndex) => {
            const modulData = group.modul || {};
            const components = group.component || [];
            const modulName = modulData.nama_modul || "";

            modulStartRows[modulName] = currentRow + 1;
            data[currentRow] = {};

            columns.forEach((col, colIndex) => {
                if (modulData[col] !== undefined) {
                    data[currentRow][colIndex] = {
                        v: modulData[col],
                        s: modulRowStyle,
                    };
                } else if (colIndex === namaModulIndex) {
                    data[currentRow][colIndex] = {
                        v: modulName,
                        s: modulRowStyle,
                    };
                } else {
                    data[currentRow][colIndex] = {
                        v: "",
                        s: modulRowStyle,
                    };
                }
            });
            currentRow++;

            if (Array.isArray(components)) {
                components.forEach((comp) => {
                    const rowData = mapDataToColumns(comp);
                    data[currentRow] = {};
                    const isFilled = group.isFilled || false;

                    Object.keys(rowData).forEach((col) => {
                        const colValue = rowData[col];
                        data[currentRow][col] =
                            typeof colValue === "string" &&
                            colValue.startsWith("=")
                                ? {
                                      f: adjustFormula(
                                          colValue,
                                          modulStartRows[modulName],
                                          isFilled
                                      ),
                                      v: "",
                                  }
                                : { v: colValue };
                    });
                    currentRow++;
                });
            }

            if (modulIndex < uniqueGroups.length - 1) {
                data[currentRow] = {};
                currentRow++;
            }
        });
    }

    setTimeout(() => formula.executeCalculation(), 100);

    return {
        data,
        mergeCells: [],
    };
}

function prepareValidationSheetData() {
    const formula = univerAPI.getFormula();
    let data = {};
    data[0] = {};

    // Baris 0 untuk header
    dataValidationCol.forEach((col, index) => {
        data[0][index] = {
            v: col,
            s: {
                bl: 1,
                ht: 2,
                vt: 2,
                fs: 11,
            },
        };
    });

    let rowIndex = 1;

    // Fungsi untuk menyesuaikan formula
    const adjustFormula = (formulaText) => {
        return formulaText.replace(/([A-Z]+)(\d+)/g, (match, col, rowNum) => {
            return `${col}${parseInt(rowNum)}`; // Basic adjustment, modify as needed
        });
    };

    // Loop setiap part langsung (bukan per modul)
    partComponentsData.forEach((comp) => {
        const row = {};

        // Loop kolom sesuai header
        dataValidationCol.forEach((col, index) => {
            const fieldKey = Object.keys(dataValMap).find(
                (key) => dataValMap[key] === col
            );

            const value = fieldKey ? comp[fieldKey] || "" : "";

            // Handle formula cells
            if (typeof value === "string" && value.startsWith("=")) {
                row[index] = {
                    f: adjustFormula(value),
                    v: "", // Nilai akan dihitung oleh engine formula
                    s: {
                        ht: 2,
                        vt: 2,
                    },
                };
            } else {
                row[index] = {
                    v: value,
                    s: {
                        ht: 2,
                        vt: 2,
                    },
                };
            }
        });

        data[rowIndex] = row;
        rowIndex++;
    });

    // Eksekusi formula setelah data dimuat
    setTimeout(() => formula.executeCalculation(), 100);

    return {
        data,
        mergeCells: [],
    };
}

function prepareSpecSheetData() {
    let data = {};
    let rowIndex = 0;

    // Header (starting from column B)
    data[rowIndex] = {
        1: {
            v: "",
            s: { bl: 1, ht: 2, vt: 2, fs: 11 },
        },
        2: {
            v: "",
            s: { bl: 1, ht: 2, vt: 2, fs: 11 },
        },
        3: {
            v: "",
            s: { bl: 1, ht: 2, vt: 2, fs: 11 },
        },
        4: {
            v: "Val",
            s: { bl: 1, ht: 2, vt: 2, fs: 11 },
        },
        5: {
            v: "Note",
            s: { bl: 1, ht: 2, vt: 2, fs: 11 },
        },
        6: {
            v: "KS",
            s: { bl: 1, ht: 2, vt: 2, fs: 11 },
        },
    };
    rowIndex++;

    // Style untuk judul kategori
    const categoryStyle = {
        bl: 1,
        fs: 12,
        it: 1,
        ul: {
            s: 1,
        },
    };

    // Style untuk data
    const dataStyle = {
        bd: { t: { s: 1 }, b: { s: 1 }, l: { s: 1 }, r: { s: 1 } },
        fs: 11,
    };

    // Style untuk nilai null
    const nullValueStyle = {
        ...dataStyle,
    };

    // Format nama kategori
    const formatCategoryName = (name) => {
        return name
            .replace(/_/g, " ")
            .replace(/\b\w/g, (l) => l.toUpperCase())
            .trim();
    };

    // Loop melalui semua kategori spesifikasi
    console.log(projectData);
    Object.entries(projectData).forEach(([category, items]) => {
        // Tambahkan judul kategori (mulai dari kolom B)
        data[rowIndex] = {
            1: {
                v: formatCategoryName(category),
                s: categoryStyle,
            },
        };
        rowIndex++;

        // Tambahkan item-item dalam kategori
        items.forEach((item) => {
            if (item && (item.key !== null || item.value !== null)) {
                data[rowIndex] = {
                    1: { v: item.key || "", s: dataStyle }, // Deskripsi
                    2: {
                        v: ":",
                        s: {
                            bd: {
                                t: { s: 1 },
                                b: { s: 1 },
                                l: { s: 1 },
                                r: { s: 1 },
                            },
                            fs: 11,
                            bl: 1,
                            ht: 2,
                        },
                    }, // Colon
                    3: {
                        v: item.value !== null ? item.value : "",
                        s: item.value !== null ? dataStyle : nullValueStyle,
                    },
                    4: { v: item.val !== null ? item.val : "", s: dataStyle },
                    5: {
                        v: item.note !== null ? item.note : "",
                        s: dataStyle,
                    },
                    6: { v: "", s: dataStyle },
                };
                rowIndex++;
            }
        });

        // Tambahkan 1 baris kosong setelah setiap kategori
        data[rowIndex] = {}; // Baris kosong
        rowIndex++;
    });

    return {
        data,
        mergeCells: [],
        rowCount: rowIndex,
    };
}

const { data: componentData, mergeCells: componentMerge } =
    prepareComponentSheetData();
const { data: validationData, mergeCells: validationMerge } =
    prepareValidationSheetData();
const {
    data: specData,
    mergeCells: specMerge,
    rowCount: specRowCount,
} = prepareSpecSheetData();

const workbook = univerAPI.createWorkbook({
    name: "Components Sheet",
    sheetCount: 3,
    sheets: {
        sheet1: {
            id: "sheet1",
            name: "Breakdown",
            tabColor: "#FF0000",
            zoomRatio: 0.8,
            hidden: BooleanNumber.FALSE,
            freeze: {
                xSplit: 7,
                ySplit: 1,
                startRow: 1,
                startColumn: 7,
            },
            rowCount: Math.max(10, Object.keys(componentData).length),
            columnCount: columns.length,
            defaultColumnWidth: 30,
            defaultRowHeight: 25,
            mergeData: componentMerge,
            cellData: componentData,
            rowData: [],
            columnData: columns.map((col) => ({ name: col })),
            rowHeader: { width: 40 },
            columnHeader: { height: 20 },
        },
        sheet2: {
            id: "sheet2",
            name: "Spek",
            tabColor: "#fcc203",
            zoomRatio: 0.8,
            hidden: BooleanNumber.FALSE,
            rowCount: Math.max(10, specRowCount),
            columnCount: 7, // Kolom A sampai G
            defaultColumnWidth: 100,
            defaultRowHeight: 25,
            mergeData: specMerge,
            cellData: specData,
            rowData: [],
            columnData: [],
            rowHeader: { width: 40 },
            columnHeader: { height: 20 },
            freeze: {
                xSplit: 1, // Freeze kolom A
                ySplit: 1, // Freeze baris header
                startRow: 1,
                startColumn: 1,
            },
        },
        sheet3: {
            id: "sheet3",
            name: "Data Validation",
            tabColor: "#2563EB",
            zoomRatio: 0.8,
            hidden: BooleanNumber.FALSE,
            rowCount: Math.max(10, Object.keys(validationData).length),
            columnCount: dataValidationCol.length,
            defaultColumnWidth: 60,
            defaultRowHeight: 25,
            mergeData: validationMerge,
            cellData: validationData,
            rowData: [],
            columnData: dataValidationCol.map((col) => ({ name: col })),
            rowHeader: { width: 40 },
            columnHeader: { height: 20 },
        },
    },
});

const worksheet = workbook.getActiveSheet();

// Atur lebar kolom
columns.forEach((col, index) => {
    if (index === namaModulIndex || index === componentIndex) {
        worksheet.setColumnWidth(index, 200);
    } else {
        worksheet.setColumnWidth(index, 40);
    }
});

const breakdownSheet = workbook.getSheets("sheet1")[0];
if (breakdownSheet) {
    breakdownSheet.setRowHeight(0, 80);
}

const specSheet = workbook.getSheets("sheet2")[1];
if (specSheet) {
    specSheet.setColumnWidth(0, 40);
    specSheet.setColumnWidth(1, 200);
    specSheet.setColumnWidth(2, 15);
    specSheet.setColumnWidth(3, 250);
    specSheet.setColumnWidth(4, 30);

    // Get the cell data by reading the range
    const maxRows = specSheet.getMaxRows();
    const maxCols = specSheet.getMaxColumns();

    let kabinetRows = [];

    // Loop through rows to find Kabinet entries
    for (let row = 0; row < maxRows; row++) {
        // Get cell value from column B (index 1)
        const range = specSheet.getRange(row, 1, 1, 1); // Single cell at row, column 1 (B)
        const cellData = range.getCellDatas();

        if (
            cellData[0] &&
            cellData[0][0] &&
            cellData[0][0].v &&
            typeof cellData[0][0].v === "string" &&
            cellData[0][0].v.includes("Kabinet")
        ) {
            const kabinetNumber = cellData[0][0].v.match(/\d+/)?.[0] || "0";
            kabinetRows.push({
                row: row + 1, // Convert to 1-based index
                number: kabinetNumber,
            });
        }
    }

    console.log(groupedComponents);
    // Create defined names for each Kabinet's value (Column D - index 3)
    kabinetRows.forEach(({ row, number }) => {
        const definedName = `bahan${number}`;
        const columnLetter = "D"; // Column D
        const cellRef = `Spek!$${columnLetter}$${row}`;

        // Create the defined name
        specSheet.insertDefinedName(
            definedName,
            cellRef,
            `Nilai bahan untuk Kabinet ${number}`
        );
    });

    // Optional: Create defined name for entire product_spesification
    const lastRow = maxRows;
    if (lastRow) {
        specSheet.insertDefinedName(
            "product_spesification",
            `Spek!$B$1:$D$${lastRow}`,
            "Range seluruh spesifikasi produk"
        );
    }
}

const validationSheet = workbook.getSheets()[2];
if (validationSheet) {
    validationSheet.setColumnWidth(2, 300);
    const definedNamed = JSON.parse(definedNames);
    console.log("names: ", definedNamed);
    definedNamed.forEach((defName) => {
        try {
            validationSheet.insertDefinedName(
                defName.name,
                defName.formulaOrRefString,
                `Defined name untuk ${defName.sheetReference}`
            );
        } catch (error) {
            console.error(`Gagal membuat defined name ${defName.name}:`, error);
        }
    });

    // Contoh tambahan untuk membuat defined name khusus jika diperlukan
    const maxRows = validationSheet.getMaxRows();
    if (maxRows > 0) {
        validationSheet.insertDefinedName(
            "data_validation_range",
            `'Data Validation'!$A$1:$Z$${maxRows}`,
            "Range seluruh data validasi"
        );
    }
}

// Fungsi untuk dropdown component
function applyComponentDropdown() {
    if (componentIndex >= 0 && componentOptions.length > 0) {
        const componentDropdownRule = univerAPI
            .newDataValidation()
            .requireValueInList(componentOptions.map((option) => option.value))
            .setOptions({
                renderMode: univerAPI.Enum.DataValidationRenderMode.ARROW,
                allowInvalid: true,
                showDropDown: true,
                showErrorMessage: false,
            })
            .build();

        worksheet
            .getRange(1, componentIndex, worksheet.getMaxRows(), 1)
            .setDataValidation(componentDropdownRule);
    }
}

// Fungsi untuk dropdown type
function applyTypeDropdown() {
    if (typeIndex >= 0 && componentTypes.length > 0) {
        const typeDropdownRule = univerAPI
            .newDataValidation()
            .requireValueInList(componentTypes.map((type) => type.value))
            .setOptions({
                renderMode: univerAPI.Enum.DataValidationRenderMode.ARROW,
                allowInvalid: true,
                showDropDown: true,
                showErrorMessage: false,
            })
            .build();

        worksheet
            .getRange(1, typeIndex, worksheet.getMaxRows(), 1)
            .setDataValidation(typeDropdownRule);
    }
}

// Terapkan dropdown
applyComponentDropdown();
applyTypeDropdown();

// Fungsi untuk mendapatkan semua data
function getAllData() {
    const workbook = univerAPI.getActiveWorkbook();
    const worksheet = workbook.getActiveSheet();
    const range = worksheet.getRange(
        0,
        0,
        worksheet.getMaxRows(),
        worksheet.getMaxColumns()
    );

    const cellDatas = range.getCellDatas();
    const formulas = range.getFormulas();

    const result = [];

    cellDatas.forEach((row, rowIndex) => {
        const rowData = {};
        row.forEach((cell, colIndex) => {
            // Jika ada formula, simpan formula aslinya
            if (formulas[rowIndex][colIndex]) {
                rowData[colIndex] = formulas[rowIndex][colIndex];
            }
            // Jika tidak ada formula, simpan nilai biasa
            else if (cell?.v !== undefined) {
                rowData[colIndex] = cell.v || "";
            } else {
                rowData[colIndex] = "";
            }
        });
        result.push(rowData);
    });

    return result;
}

// Event handler untuk tombol update
$(document).on("click", "#key-bindings-2", function () {
    const spreadsheetData = getAllData();

    // Format data untuk modul_breakdown sesuai struktur baru
    const modulBreakdown = [];
    let currentModul = null;
    let currentModulObject = {};
    let currentComponents = [];

    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Skip baris kosong
        if (Object.values(row).every((val) => val === "")) continue;

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== "") {
            // Simpan modul sebelumnya jika ada
            if (currentModul) {
                modulBreakdown.push({
                    modul: currentModulObject,
                    components: currentComponents,
                });
            }

            // Mulai modul baru
            currentModul = row[namaModulIndex];
            currentModulObject = { nama_modul: currentModul };
            currentComponents = [];

            // Isi data modul dari baris ini
            columns.forEach((col, colIndex) => {
                if (row[colIndex] !== undefined && row[colIndex] !== "") {
                    currentModulObject[col] = row[colIndex];
                }
            });
            continue;
        }

        // Proses baris komponen
        const componentData = {};
        columns.forEach((col, colIndex) => {
            if (row[colIndex] !== undefined && row[colIndex] !== "") {
                componentData[col] = row[colIndex];
            }
        });

        if (Object.keys(componentData).length > 0) {
            currentComponents.push(componentData);
        }
    }

    // Simpan modul terakhir
    if (currentModul) {
        modulBreakdown.push({
            modul: currentModulObject,
            components: currentComponents,
        });
    }

    const payload = {
        modul_breakdown: modulBreakdown,
        columns: columns,
        recordId: recordId,
    };

    console.log("Payload untuk update:", payload);

    $.ajax({
        url: "/update-project",
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "Content-Type": "application/json",
        },
        data: JSON.stringify(payload),
        success: function (response) {
            if (response.status === "success") {
                alert("Data berhasil diupdate!");
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function (xhr) {
            let errorMsg = "Terjadi kesalahan";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            alert(errorMsg);
        },
    });
});

function addModulToSpreadsheet(modulName) {
    try {
        const breakdownSheet = workbook.getSheets()[0];

        // 1. Find the selected module data from allModuls
        let selectedModulData = null;
        let selectedComponents = [];

        if (allModuls && allModuls.array) {
            for (const modulGroup of allModuls.array) {
                if (modulGroup.modul?.nama_modul === modulName) {
                    selectedModulData = modulGroup.modul;
                    selectedComponents = modulGroup.component || [];
                    break;
                }
            }
        }

        if (!selectedModulData) {
            console.error("Modul data not found in allModuls");
            return false;
        }

        // Define adjustFormula function
        const adjustFormula = (formula, modulStartRow, isFilled) => {
            return formula.replace(/([A-Z]+)(\d+)/g, (match, col, rowNum) => {
                const newRow = isFilled
                    ? parseInt(rowNum)
                    : modulStartRow + parseInt(rowNum) - 1;
                return `${col}${newRow}`;
            });
        };

        // 2. Find the true last row with data (scan all columns)
        let lastDataRow = 0;
        const maxRows = breakdownSheet.getMaxRows();
        for (let i = maxRows - 1; i >= 0; i--) {
            let hasData = false;
            for (let col = 0; col < columns.length; col++) {
                const cellData = breakdownSheet
                    .getRange(i, col, 1, 1)
                    .getCellDatas()[0][0];
                if (
                    cellData &&
                    cellData.v !== undefined &&
                    String(cellData.v).trim() !== ""
                ) {
                    hasData = true;
                    break;
                }
            }
            if (hasData) {
                lastDataRow = i;
                break;
            }
        }

        // 3. Calculate positions
        const newModulRow = lastDataRow === 0 ? 1 : lastDataRow + 2;
        const componentRows = selectedComponents.length;
        const lastComponentRow = newModulRow + componentRows;
        const totalRowsNeeded = 1 + componentRows + 1; // Module + components + spacing

        // 4. Ensure we have enough rows
        const currentLastRow = breakdownSheet.getMaxRows();
        if (lastComponentRow + 1 > currentLastRow) {
            const rowsToAdd = lastComponentRow + 1 - currentLastRow;
            breakdownSheet.insertRows(currentLastRow, rowsToAdd);
        }

        // 5. Add empty row after last component if it contains data
        if (lastComponentRow + 1 <= breakdownSheet.getMaxRows()) {
            const nextRowData = breakdownSheet
                .getRange(lastComponentRow + 1, 0, 1, columns.length)
                .getCellDatas()[0];
            const hasData = nextRowData.some(
                (cell) =>
                    cell && cell.v !== undefined && String(cell.v).trim() !== ""
            );

            if (hasData) {
                breakdownSheet.insertRows(lastComponentRow + 1, 1);
            }
        }

        // 6. Module row style
        const modulStyle = {
            bg: { rgb: "#faf59b" },
            bl: 1,
            bd: { t: { s: 1 }, b: { s: 1 }, l: { s: 1 }, r: { s: 1 } },
            fs: 11,
        };

        // 7. Add module row
        breakdownSheet.getRange(newModulRow, namaModulIndex).setValue([
            [
                {
                    v: selectedModulData.nama_modul,
                    s: modulStyle,
                },
            ],
        ]);

        // 8. Add other module data
        columns.forEach((col, colIndex) => {
            breakdownSheet.getRange(newModulRow, colIndex).setValue([
                [
                    {
                        s: modulStyle,
                    },
                ],
            ]);
        });

        // 9. Add components with formula adjustment
        selectedComponents.forEach((component, compIndex) => {
            const componentRow = newModulRow + 1 + compIndex;
            const mappedData = mapDataToColumns(component);

            columns.forEach((col, colIndex) => {
                if (mappedData[colIndex] !== undefined) {
                    const value = mappedData[colIndex];

                    if (typeof value === "string" && value.startsWith("=")) {
                        // Handle formula cells with adjustment
                        const adjustedFormula = adjustFormula(
                            value,
                            newModulRow,
                            false
                        );
                        breakdownSheet
                            .getRange(componentRow, colIndex)
                            .setValue({
                                f: adjustedFormula,
                                v: "",
                            });
                    } else {
                        // Handle regular values
                        breakdownSheet
                            .getRange(componentRow, colIndex)
                            .setValue(value);
                    }
                }
            });
        });

        // 10. Auto-resize columns
        breakdownSheet.setColumnWidth(namaModulIndex, 200);
        breakdownSheet.setColumnWidth(componentIndex, 200);

        // 11. Scroll to new module
        breakdownSheet.scrollToCell(newModulRow, namaModulIndex);

        // 12. Execute calculations after adding data
        setTimeout(() => formula.executeCalculation(), 100);

        console.log(
            `Added module "${modulName}" at row ${newModulRow} with components until row ${lastComponentRow}`
        );
        return true;
    } catch (error) {
        console.error("Failed to add module:", error);
        return false;
    }
}

function addPartToSpreadsheet(partName) {
    try {
        const breakdownSheet = workbook.getSheets()[0];

        // 1. Find the selected part data from allParts
        let selectedPartData = null;
        let selectedComponents = [];

        if (allParts && allParts.array) {
            for (const partGroup of allParts.array) {
                if (partGroup.part?.part_name === partName) {
                    selectedPartData = partGroup.part;
                    selectedComponents = partGroup.component || [];
                    break;
                }
            }
        }

        if (!selectedPartData) {
            console.error("Part data not found in allParts");
            return false;
        }

        // Define adjustFormula function
        const adjustFormula = (formula, partStartRow, isFilled) => {
            return formula.replace(/([A-Z]+)(\d+)/g, (match, col, rowNum) => {
                const newRow = isFilled
                    ? parseInt(rowNum)
                    : partStartRow + parseInt(rowNum) - 1;
                return `${col}${newRow}`;
            });
        };

        // 2. Find the true last row with data (scan all columns)
        let lastDataRow = 0;
        const maxRows = breakdownSheet.getMaxRows();
        for (let i = maxRows - 1; i >= 0; i--) {
            let hasData = false;
            for (let col = 0; col < columns.length; col++) {
                const cellData = breakdownSheet
                    .getRange(i, col, 1, 1)
                    .getCellDatas()[0][0];
                if (
                    cellData &&
                    cellData.v !== undefined &&
                    String(cellData.v).trim() !== ""
                ) {
                    hasData = true;
                    break;
                }
            }
            if (hasData) {
                lastDataRow = i;
                break;
            }
        }

        // 3. Calculate positions
        const newPartRow = lastDataRow === 0 ? 1 : lastDataRow + 2;
        const componentRows = selectedComponents.length;
        const lastComponentRow = newPartRow + componentRows;
        const totalRowsNeeded = 1 + componentRows + 1;

        // 4. Ensure have enough rows
        const currentLastRow = breakdownSheet.getMaxRows();
        if (lastComponentRow + 1 > currentLastRow) {
            const rowsToAdd = lastComponentRow - currentLastRow;
            breakdownSheet.insertRows(currentLastRow, rowsToAdd);
        }

        // 5. Add empty row after last component if it contains data
        if (lastComponentRow + 1 <= breakdownSheet.getMaxRows()) {
            const nextRowData = breakdownSheet
                .getRange(lastComponentRow + 1, 0, 1, columns.length)
                .getCellDatas()[0];
            const hasData = nextRowData.some(
                (cell) =>
                    cell && cell.v !== undefined && String(cell.v).trim() !== ""
            );

            if (hasData) {
                breakdownSheet.insertRows(lastComponentRow + 1, 1);
            }
        }

        // 6. Part row style
        const partStyle = {
            bg: { rgb: "#faf59b" }, // Yellow background
            bl: 1,
            bd: { t: { s: 1 }, b: { s: 1 }, l: { s: 1 }, r: { s: 1 } }, // Borders
            fs: 11, // Font size
        };

        // 9. Add components with formula adjustment
        selectedComponents.forEach((component, compIndex) => {
            const componentRow = newPartRow + compIndex;
            const mappedData = mapDataToColumns(component);

            columns.forEach((col, colIndex) => {
                if (mappedData[colIndex] !== undefined) {
                    const value = mappedData[colIndex];

                    if (typeof value === "string" && value.startsWith("=")) {
                        // Handle formula cells with adjustment
                        const adjustedFormula = adjustFormula(
                            value,
                            newPartRow,
                            false
                        );
                        breakdownSheet
                            .getRange(componentRow, colIndex)
                            .setValue({
                                f: adjustedFormula,
                                v: "",
                            });
                    } else {
                        // Handle regular values
                        breakdownSheet
                            .getRange(componentRow, colIndex)
                            .setValue(value);
                    }
                }
            });
        });

        // 10. Auto-resize columns
        breakdownSheet.setColumnWidth(namaModulIndex, 200); // Adjust column widths as needed
        breakdownSheet.setColumnWidth(componentIndex, 200);

        // 11. Scroll to new part
        breakdownSheet.scrollToCell(newPartRow, 0);

        // 12. Execute calculations after adding data
        setTimeout(() => formula.executeCalculation(), 100);

        console.log(
            `Added part "${partName}" at row ${newPartRow} with components until row ${lastComponentRow}`
        );
        return true;
    } catch (error) {
        console.error("Failed to add part:", error);
        return false;
    }
}

// 2. Event handler untuk tombol Tambah
$(document).on(
    "click",
    "#modulModal .btn-primary:not([data-bs-dismiss])",
    function (e) {
        e.preventDefault();
        e.stopPropagation();

        const selectElement = $("#modulSelect");
        const selectedModul = selectElement.val();

        if (!selectedModul) {
            alert("Silakan pilih modul terlebih dahulu");
            return;
        }

        console.log("Memproses modul:", selectedModul);

        // Tambahkan ke spreadsheet
        if (addModulToSpreadsheet(selectedModul)) {
            // Tutup modal
            $("#modulModal").modal("hide");

            // Reset select
            selectElement.val(null).trigger("change");

            alert("Modul berhasil ditambahkan!");
        } else {
            alert("Gagal menambahkan modul ke spreadsheet");
        }
    }
);

$(document).on(
    "click",
    "#partModal .btn-primary:not([data-bs-dismiss])",
    function (e) {
        e.preventDefault();
        e.stopPropagation();

        const selectElement = $("#partSelect");
        const selectedPart = selectElement.val();

        if (!selectedPart) {
            alert("Silakan pilih Part terlebih dahulu");
            return;
        }

        console.log("Memproses modul:", selectedPart);

        // Tambahkan ke spreadsheet
        if (addPartToSpreadsheet(selectedPart)) {
            // Tutup modal
            $("#partModal").modal("hide");

            // Reset select
            selectElement.val(null).trigger("change");

            alert("part berhasil ditambahkan!");
        } else {
            alert("Gagal menambahkan part ke spreadsheet");
        }
    }
);
