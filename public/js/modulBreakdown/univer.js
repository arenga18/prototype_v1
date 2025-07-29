const namaModulIndex = columns.indexOf("nama_modul");
const componentIndex = columns.indexOf("component");
const typeIndex = columns.indexOf("type");

const projectInformation = {
    date: projectData.date,
    recap_number: projectData.recap_number,
    no_contract: projectData.no_contract,
    nip: projectData.nip,
    product_name: projectData.product_name,
    project_name: projectData.project_name,
    estimator: projectData.estimator,
    recap_coordinator: projectData.recap_coordinator,
    project_status: projectData.project_status,
};

console.log("Grouped Components : ", groupedComponents);

// Inisialisasi Univer
const { createUniver } = UniverPresets;
const { LocaleType, merge, BooleanNumber } = UniverCore;
const { defaultTheme } = UniverDesign;
const { UniverSheetsCorePreset } = UniverPresetSheetsCore;
const { UniverSheetsDataValidationPreset } = UniverPresetSheetsDataValidation;
const { UniverSheetsFindReplacePreset } = UniverPresetSheetsFindReplace;
const { UniverSheetsFilterPreset } = UniverPresetSheetsFilter;
const { UniverSheetsConditionalFormattingPreset } =
    UniverPresetSheetsConditionalFormatting;

const { univerAPI } = createUniver({
    locale: LocaleType.EN_US,
    locales: {
        [LocaleType.EN_US]: merge(
            {},
            UniverPresetSheetsCoreEnUS,
            UniverPresetSheetsDataValidationEnUS,
            UniverPresetSheetsFindReplaceEnUS,
            UniverPresetSheetsFilterEnUS,
            UniverPresetSheetsConditionalFormattingEnUS
        ),
    },
    theme: defaultTheme,
    presets: [
        UniverSheetsCorePreset(),
        UniverSheetsDataValidationPreset(),
        UniverSheetsFindReplacePreset(),
        UniverSheetsFilterPreset(),
        UniverSheetsConditionalFormattingPreset(),
    ],
});

// Fungsi untuk memetakan data ke kolom
function mapDataToColumns(comp) {
    if (!comp || !comp.data || typeof comp.data !== "object") {
        console.error("Invalid component data:", comp);
        return {};
    }

    let componentRow = {};
    const compData = comp.data; // Reference to component data

    // Map standard fields from comp.data
    componentRow[componentIndex] = compData.component || compData.name || "";

    if (typeIndex >= 0) {
        componentRow[typeIndex] = compData.code || "";
    }

    if (namaModulIndex >= 0) {
        componentRow[namaModulIndex] = compData.modul || "";
    }

    // Map all fields from the component data to their respective columns
    Object.keys(compData).forEach((key) => {
        const colIndex = columns.indexOf(key);
        if (
            colIndex >= 0 &&
            compData[key] !== undefined &&
            compData[key] !== null &&
            compData[key] !== ""
        ) {
            componentRow[colIndex] = compData[key];
        }
    });

    // Map fields according to fieldMapping (now checking comp.data instead of comp)
    Object.entries(fieldMapping).forEach(([sourceField, targetColumn]) => {
        const colIndex = columns.indexOf(targetColumn);
        if (
            colIndex >= 0 &&
            compData[sourceField] !== undefined &&
            compData[sourceField] !== null &&
            compData[sourceField] !== ""
        ) {
            componentRow[colIndex] = compData[sourceField];
        }
    });

    // Optional: Add styles if they exist in the component
    if (comp.styles && typeof comp.styles === "object") {
        componentRow.styles = comp.styles;
    }

    return componentRow;
}

const formula = univerAPI.getFormula();

function prepareBreakdownSheetData() {
    let data = {};
    const modulStartRows = {};
    let currentRow = 1;

    // Create header row
    data[0] = {};
    columns.forEach((col, index) => {
        data[0][index] = {
            v: col,
            s: {
                bl: 1, // bold
                ht: 2, // horizontal alignment
                vt: 2, // vertical alignment
                fs: 11, // font size
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

    const modulStyle = {
        bg: { rgb: "#faf59b" }, // yellow background
        bl: 1, // bold
        bd: { t: { s: 1 }, b: { s: 1 }, l: { s: 1 }, r: { s: 1 } }, // borders
        fs: 11, // font size
    };

    const componentStyle = {
        fs: 11, // font size
    };

    if (groupedComponents?.array) {
        groupedComponents.array.forEach((group, modulIndex) => {
            // Handle main module
            const mainModulData = group.modul || {};
            const mainModulName = mainModulData.nama_modul || "";

            // if (mainModulName) {
            //     // Store starting row for formula adjustment
            //     modulStartRows[mainModulName] = currentRow + 1;

            //     // Create main modul row with style
            //     data[currentRow] = {};
            //     columns.forEach((col, colIndex) => {
            //         if (mainModulData[col] !== undefined) {
            //             data[currentRow][colIndex] = {
            //                 v: mainModulData[col],
            //                 s: modulStyle,
            //             };
            //         } else if (colIndex === namaModulIndex) {
            //             data[currentRow][colIndex] = {
            //                 v: mainModulName,
            //                 s: modulStyle,
            //             };
            //         } else {
            //             data[currentRow][colIndex] = {
            //                 v: "",
            //                 s: modulStyle,
            //             };
            //         }
            //     });
            //     currentRow++;
            // }

            // Process all component groups
            if (Array.isArray(group.component)) {
                group.component.forEach(
                    (componentGroup, componentGroupIndex) => {
                        // Handle nested module if exists
                        const nestedModulData = componentGroup.modul || {};
                        const nestedModulName =
                            nestedModulData.nama_modul || "";

                        if (nestedModulName) {
                            // Store starting row for formula adjustment
                            modulStartRows[nestedModulName] = currentRow + 1;

                            // Create nested modul row with style
                            data[currentRow] = {};
                            columns.forEach((col, colIndex) => {
                                if (nestedModulData[col] !== undefined) {
                                    data[currentRow][colIndex] = {
                                        v: nestedModulData[col],
                                        s: modulStyle,
                                    };
                                } else if (colIndex === namaModulIndex) {
                                    data[currentRow][colIndex] = {
                                        v: nestedModulName,
                                        s: modulStyle,
                                    };
                                } else {
                                    data[currentRow][colIndex] = {
                                        v: "",
                                        s: modulStyle,
                                    };
                                }
                            });
                            currentRow++;
                        }

                        // Process components within each component group
                        if (Array.isArray(componentGroup.components)) {
                            componentGroup.components.forEach((component) => {
                                data[currentRow] = {};
                                const componentData = component.data || {};
                                const componentStyles = component.styles || {};

                                // Determine which modul to use for formula reference
                                const referenceModulName =
                                    nestedModulName || mainModulName;
                                const referenceModulStartRow =
                                    modulStartRows[referenceModulName];

                                columns.forEach((col, colIndex) => {
                                    const value =
                                        componentData[col] !== undefined
                                            ? componentData[col]
                                            : "";
                                    const style =
                                        componentStyles[col] !== undefined
                                            ? componentStyles[col]
                                            : componentStyle;

                                    if (
                                        typeof value === "string" &&
                                        value.startsWith("=")
                                    ) {
                                        data[currentRow][colIndex] = {
                                            f: adjustFormula(
                                                value,
                                                referenceModulStartRow,
                                                group.isFilled || false
                                            ),
                                            v: "",
                                            s: style,
                                        };
                                    } else {
                                        data[currentRow][colIndex] = {
                                            v: value,
                                            s: style,
                                        };
                                    }
                                });
                                currentRow++;
                            });
                        }

                        // Add empty row between component groups if needed
                        if (componentGroupIndex < group.component.length - 1) {
                            data[currentRow] = {};
                            currentRow++;
                        }
                    }
                );
            }

            // Add space between module groups if not last
            if (modulIndex < groupedComponents.array.length - 1) {
                data[currentRow] = {};
                currentRow++;
            }
        });
    }

    // Execute calculations for both modul and component formulas
    formula.executeCalculation();

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
            return `${col}${parseInt(rowNum)}`;
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

    const nonArrayFields = [
        "date",
        "recap_number",
        "no_contract",
        "nip",
        "product_name",
        "project_name",
        "estimator",
        "recap_coordinator",
    ];

    // Add non-array fields section title
    data[rowIndex] = {
        1: {
            v: "Project Information",
            s: categoryStyle,
        },
    };
    rowIndex++;

    // Add non-array fields
    nonArrayFields.forEach((field) => {
        if (projectData[field] !== null && projectData[field] !== undefined) {
            data[rowIndex] = {
                1: { v: formatCategoryName(field), s: dataStyle },
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
                },
                3: {
                    v: projectData[field] !== null ? projectData[field] : "",
                    s: dataStyle,
                },
                4: { v: "", s: dataStyle },
                5: { v: "", s: dataStyle },
                6: { v: "", s: dataStyle },
            };
            rowIndex++;
        }
    });

    // Add empty row after non-array fields
    data[rowIndex] = {};
    rowIndex++;

    // Then process array-type specification fields
    const arrayFields = Object.keys(projectData).filter(
        (key) =>
            Array.isArray(projectData[key]) && !nonArrayFields.includes(key)
    );

    // Loop melalui semua kategori spesifikasi (array type)
    arrayFields.forEach((category) => {
        const items = projectData[category];

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
    prepareBreakdownSheetData();
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
            defaultColumnWidth: 40,
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

console.log("Workbook created:", workbook);
const worksheet = workbook.getActiveSheet();

function applyFilteredDataValidations() {
    const definedNamed = JSON.parse(definedNames);

    const filteredDefNames = definedNamed.filter(
        (defName) => defName.name === "prt" || defName.name === "menu"
    );

    // Mapping data
    const defNameToColumn = {
        menu: 1, // Kolom index 1 untuk menu
        prt: 6, // Kolom index 6 untuk prt
    };

    filteredDefNames.forEach((defName) => {
        const targetRange = worksheet.getRange(defName.formulaOrRefString);
        const columnIndex = defNameToColumn[defName.name];

        if (!columnIndex) return;

        try {
            // Ambil nilai dari range referensi
            const values = targetRange.getValues().flat().filter(Boolean);

            // Terapkan dropdown ke kolom yang sesuai
            applyDropdownToColumn(
                columnIndex,
                values.map((value) => ({ value })),
                true
            );

            console.log(
                `Data validation applied for ${defName.name} to column ${columnIndex}`
            );
        } catch (error) {
            console.error(`Error applying ${defName.name} validation:`, error);
        }
    });
}

function applyDropdownToColumn(columnIndex, options, clearInvalid = true) {
    if (columnIndex <= 0 || !options?.length) return;

    try {
        const dropdownRule = univerAPI
            .newDataValidation()
            .requireValueInList(options.map((opt) => opt.value || opt))
            .setOptions({
                renderMode: univerAPI.Enum.DataValidationRenderMode.TEXT,
                allowInvalid: false,
                showDropDown: true,
                showErrorMessage: true,
                errorMessage: `Nilai harus ada dalam daftar yang ditentukan`,
                errorTitle: "Nilai Tidak Valid",
            })
            .build();

        const range = worksheet.getRange(
            1,
            columnIndex,
            worksheet.getMaxRows(),
            1
        );

        if (clearInvalid) {
            const currentValues = range.getValues();

            currentValues.forEach((row, i) => {
                const cellValue = row[0];
                if (
                    cellValue &&
                    !options.some((opt) => (opt.value || opt) === cellValue)
                ) {
                    worksheet.getRange(i + 1, columnIndex);
                }
            });
        }

        range.setDataValidation(dropdownRule);
    } catch (error) {
        console.error(
            `Error applying dropdown to column ${columnIndex}:`,
            error
        );
    }
}

// fungsi untuk validasi data
applyFilteredDataValidations();

const breakdownSheet = workbook.getSheets("sheet1")[0];
console.log("breakdownSheet", breakdownSheet);
if (breakdownSheet) {
    // worksheet.setColumnWidth(1, 200);
    breakdownSheet.setColumnWidth(5, 200);
    breakdownSheet.setColumnWidth(6, 200);
    breakdownSheet.setColumnWidth(7, 150);
    breakdownSheet.setRowHeight(0, 80);

    // Conditional formatting
    const range = breakdownSheet.getRange("R3:Z1000");
    const rule = breakdownSheet
        .newConditionalFormattingRule()
        .whenNumberEqualTo(11)
        .setRanges([range.getRange()])
        .setItalic(true)
        .setBackground("red")
        .setFontColor("green")
        .build();
    breakdownSheet.addConditionalFormattingRule(rule);
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
}

const validationSheet = workbook.getSheets()[2];
if (validationSheet) {
    validationSheet.setColumnWidth(2, 300);
    validationSheet.setRowHeight(0, 80);
    const definedNamed = JSON.parse(definedNames);
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

    const cellStyles = range.getCellStyles();
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

    cellStyles.forEach((row, rowIndex) => {
        row.forEach((cell, colIndex) => {
            if (
                cell?._style &&
                cellData[rowIndex] &&
                cellData[rowIndex][colIndex]
            ) {
                // Tambahkan property 's' untuk style jika style ada
                cellData[rowIndex][colIndex].s = cell._style;
            }
        });
    });

    return result;
}

// Event handler untuk tombol update
$(document).on("click", "#key-bindings-2", function () {
    const spreadsheetData = getAllData();
    const modulBreakdown = [];
    let currentModul = null;
    let currentModulObject = {};
    let currentComponents = [];

    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== "") {
            // Simpan modul sebelumnya jika ada (dengan membersihkan baris kosong di akhir)
            if (currentModul) {
                cleanEmptyRowsAtEnd(currentComponents);
                modulBreakdown.push({
                    modul: currentModulObject,
                    components: currentComponents,
                });
            }

            // Mulai modul baru
            currentModul = row[namaModulIndex];
            currentModulObject = { nama_modul: currentModul };
            currentComponents = [];

            // Isi data modul
            columns.forEach((col, colIndex) => {
                if (row[colIndex] !== undefined && row[colIndex] !== "") {
                    currentModulObject[col] = row[colIndex];
                }
            });
            continue;
        }

        // Proses baris komponen (tambahkan semua baris, termasuk yang kosong di tengah)
        const componentData = {};
        let hasData = false;
        columns.forEach((col, colIndex) => {
            if (row[colIndex] !== undefined && row[colIndex] !== "") {
                componentData[col] = row[colIndex];
                hasData = true;
            } else {
                componentData[col] = "";
            }
        });
        currentComponents.push(componentData);
    }

    // Simpan modul terakhir (dengan membersihkan baris kosong di akhir)
    if (currentModul) {
        cleanEmptyRowsAtEnd(currentComponents);
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
                console.log("Data berhasil diupdate!");
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function (xhr) {
            let errorMsg = "Terjadi kesalahan saat mengupdate data";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            alert(errorMsg);
        },
    });
});

// Fungsi untuk menghapus baris kosong hanya di akhir array
function cleanEmptyRowsAtEnd(components) {
    let i = components.length - 1;
    while (i >= 0 && isRowEmpty(components[i])) {
        components.pop();
        i--;
    }
}

// Fungsi helper untuk mengecek apakah sebuah row kosong
function isRowEmpty(row) {
    return Object.values(row).every(
        (val) => val === "" || val === undefined || val === null
    );
}

function addModulToSpreadsheet(modulName, placementModulName = null) {
    try {
        const breakdownSheet = workbook.getSheets()[0];

        // 1. Find the selected module data from allModuls
        let selectedModulData = null;
        let selectedComponents = [];
        let subModuls = [];

        if (allModuls && allModuls.array) {
            for (const modulGroup of allModuls.array) {
                if (modulGroup.modul?.nama_modul === modulName) {
                    selectedModulData = modulGroup.modul;
                    if (
                        modulGroup.component &&
                        modulGroup.component.length > 0
                    ) {
                        // Simpan data sub-modul
                        subModuls = modulGroup.component.map((compGroup) => ({
                            nama_modul:
                                compGroup.modul?.nama_modul ||
                                selectedModulData.nama_modul,
                            components: compGroup.components || [],
                        }));

                        // Gabungkan semua komponen dari semua sub-modul
                        selectedComponents = subModuls.reduce(
                            (acc, subModul) => {
                                return [...acc, ...subModul.components];
                            },
                            []
                        );
                    }
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

        // 2. Find the target row based on placement selection
        let targetRow = 0;

        if (placementModulName) {
            // 1. Find the reference module in groupedComponents
            let referenceModul = null;
            for (const modulGroup of groupedComponents.array) {
                if (modulGroup.modul?.nama_modul === placementModulName) {
                    referenceModul = modulGroup;
                    break;
                }
            }

            if (!referenceModul) {
                console.log(
                    `Module "${placementModulName}" not found in groupedComponent, adding to the end`
                );
                targetRow = breakdownSheet.getMaxRows();
            } else {
                // 2. Find the reference module's position in the spreadsheet
                const maxRows = breakdownSheet.getMaxRows();
                let referenceStartRow = 0;
                let referenceEndRow = 0;

                for (let i = 0; i < maxRows; i++) {
                    const cellData = breakdownSheet
                        .getRange(i, namaModulIndex, 1, 1)
                        .getCellDatas()[0][0];

                    if (cellData?.v === referenceModul.modul.nama_modul) {
                        referenceStartRow = i;

                        // Calculate total rows used by reference module
                        let totalComponentRows = 0;
                        referenceModul.component.forEach((subModul) => {
                            totalComponentRows += subModul.components.length;
                            if (
                                subModul.nama_modul !==
                                referenceModul.modul.nama_modul
                            ) {
                                totalComponentRows += 1; // For sub-module header
                            }
                        });

                        referenceEndRow =
                            referenceStartRow + totalComponentRows;

                        // 3. Calculate rows needed for new module (using subModuls which we calculated earlier)
                        let newModulRows = 1;
                        if (subModuls && subModuls.length > 0) {
                            subModuls.forEach((subModul) => {
                                newModulRows += subModul.components.length;
                                if (
                                    subModul.nama_modul !==
                                    selectedModulData.nama_modul
                                ) {
                                    newModulRows += 1; // For sub-module header
                                }
                            });
                        }

                        // 4. Insert required rows (1 spacing row + rows for new module)
                        breakdownSheet.insertRows(
                            referenceEndRow + 1,
                            newModulRows + 1
                        );
                        targetRow = referenceEndRow + 1; // Start after spacing row

                        console.log(
                            `Found module "${placementModulName}" from row ${referenceStartRow} to ${referenceEndRow}`
                        );
                        console.log(
                            `Inserting new module "${modulName}" at row ${targetRow} with ${newModulRows} rows and 1 spacing row`
                        );
                        break;
                    }
                }

                if (referenceStartRow === 0) {
                    console.log(
                        `Module "${placementModulName}" found in groupedComponent but not in sheet, adding to the end`
                    );
                    targetRow = breakdownSheet.getMaxRows();
                }
            }
        } else {
            // Default case when no placementModulName is specified
            const maxRows = breakdownSheet.getMaxRows();
            for (let i = maxRows - 1; i >= 0; i--) {
                let hasData = false;
                for (let col = 0; col < columns.length; col++) {
                    const cellData = breakdownSheet
                        .getRange(i, col, 1, 1)
                        .getCellDatas()[0][0];
                    if (
                        cellData?.v !== undefined &&
                        String(cellData.v).trim() !== ""
                    ) {
                        hasData = true;
                        break;
                    }
                }
                if (hasData) {
                    targetRow = i + 2; // Add after last row with data plus one empty row
                    break;
                }
            }
            if (targetRow === 0) targetRow = 1;
        }

        // 3. Calculate positions
        const newModulRow = targetRow;
        let lastComponentRow = newModulRow;

        // Hitung total rows yang dibutuhkan
        let totalComponentRows = 0;
        subModuls.forEach((subModul, index) => {
            totalComponentRows += subModul.components.length;
            // Tambahkan 1 untuk header sub-modul jika namanya berbeda dengan modul utama
            if (subModul.nama_modul !== selectedModulData.nama_modul) {
                totalComponentRows += 1;
            }
            // Tambahkan 1 untuk spasi pemisah kecuali untuk sub-modul terakhir
            if (index < subModuls.length - 1) {
                totalComponentRows += 1;
            }
        });

        lastComponentRow = newModulRow + totalComponentRows;

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

        // Sub-modul header style
        const subModulStyle = {
            bg: { rgb: "#e6f3ff" },
            bl: 1,
            bd: { t: { s: 1 }, b: { s: 1 }, l: { s: 1 }, r: { s: 1 } },
            fs: 11,
        };

        // 7. Add main module row
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
        let currentRow = newModulRow + 1;

        for (let i = 0; i < subModuls.length; i++) {
            const subModul = subModuls[i];

            // Add sub-modul header row hanya jika namanya berbeda dengan modul utama
            if (subModul.nama_modul !== selectedModulData.nama_modul) {
                breakdownSheet.getRange(currentRow, namaModulIndex).setValue([
                    [
                        {
                            v: subModul.nama_modul,
                            s: modulStyle,
                        },
                    ],
                ]);

                columns.forEach((col, colIndex) => {
                    breakdownSheet.getRange(currentRow, colIndex).setValue([
                        [
                            {
                                s: modulStyle,
                            },
                        ],
                    ]);
                });

                currentRow++;
            }

            // Add components for this sub-modul
            for (const component of subModul.components) {
                const mappedData = mapDataToColumns(component);

                columns.forEach((col, colIndex) => {
                    if (mappedData[colIndex] !== undefined) {
                        const value = mappedData[colIndex];

                        if (
                            typeof value === "string" &&
                            value.startsWith("=")
                        ) {
                            // Handle formula cells with adjustment
                            const adjustedFormula = adjustFormula(
                                value,
                                newModulRow,
                                false
                            );
                            breakdownSheet
                                .getRange(currentRow, colIndex)
                                .setValue({
                                    f: adjustedFormula,
                                    v: "",
                                });
                        } else {
                            // Handle regular values
                            breakdownSheet
                                .getRange(currentRow, colIndex)
                                .setValue(value);
                        }
                    }
                });

                currentRow++;
            }

            // Tambahkan spasi pemisah kecuali untuk sub-modul terakhir
            if (i < subModuls.length - 1) {
                currentRow++;
            }
        }

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

        // 9. Add components with formula
        selectedComponents.forEach((component, compIndex) => {
            const componentRow = newPartRow + compIndex;
            const mappedData = mapDataToColumns(component);

            columns.forEach((col, colIndex) => {
                if (mappedData[colIndex] !== undefined) {
                    const value = mappedData[colIndex];

                    if (typeof value === "string" && value.startsWith("=")) {
                        // Handle formula cells t
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

$(document).on(
    "click",
    "#modul-modal button[type='button'].bg-blue-700",
    function (e) {
        e.preventDefault();
        e.stopPropagation();

        const selectElement = $("#modulSelect");
        const placementSelect = $("#modul-placement");

        console.log("Memproses modul:", selectElement);
        const selectedModul = selectElement.val();
        const placementModul = placementSelect.val();

        if (!selectedModul) {
            alert("Silakan pilih modul terlebih dahulu");
            return;
        }

        console.log(
            "Memproses modul:",
            selectedModul,
            "placement:",
            placementModul
        );

        // Tambahkan ke spreadsheet
        if (addModulToSpreadsheet(selectedModul, placementModul)) {
            const modal = FlowbiteInstances.getInstance("Modal", "modul-modal");
            modal.hide();

            // Reset select
            selectElement.val(null).trigger("change");
            placementSelect.val(null).trigger("change");
            alert("Modul berhasil ditambahkan!");
        } else {
            alert("Gagal menambahkan modul ke spreadsheet");
        }
    }
);

$(document).on(
    "click",
    "#part-modal button[type='button'].bg-blue-700",
    function (e) {
        e.preventDefault();
        e.stopPropagation();

        const selectElement = $("#partSelect");

        console.log("Memproses part:", selectElement);
        const selectedPart = selectElement.val();

        if (!selectedPart) {
            alert("Silakan pilih Part terlebih dahulu");
            return;
        }

        console.log("Memproses part:", selectedPart);

        // Tambahkan ke spreadsheet
        if (addPartToSpreadsheet(selectedPart)) {
            // Tutup modal menggunakan Flowbite
            const modal = FlowbiteInstances.getInstance("Modal", "part-modal");
            modal.hide();

            // Reset select
            selectElement.val(null).trigger("change");

            alert("Part berhasil ditambahkan!");
        } else {
            alert("Gagal menambahkan part ke spreadsheet");
        }
    }
);
