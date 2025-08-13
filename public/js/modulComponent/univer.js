const namaModulIndex = columns.indexOf("nama_modul");
const componentIndex = columns.indexOf("component");
const typeIndex = columns.indexOf("type");

console.log("grouped Components : ", groupedComponents);

// Inisialisasi Univer
const CalculationMode = {
    FORCED: 0,
    WHEN_EMPTY: 1,
    NO_CALCULATION: 2,
};
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

// Create Univer instance with formula configuration
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
        UniverSheetsCorePreset({
            formula: {
                initialFormulaComputing: CalculationMode.FORCED, // Force calculation on initialization
            },
        }),
        UniverSheetsDataValidationPreset(),
        UniverSheetsFindReplacePreset(),
        UniverSheetsFilterPreset(),
        UniverSheetsConditionalFormattingPreset(),
    ],
});

// Get the formula facade
const formula = univerAPI.getFormula();

// Force calculation
formula.setInitialFormulaComputing(CalculationMode.FORCED);
formula.executeCalculation();

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

// Siapkan data untuk Univer
function prepareUniverData() {
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
        return formula.replace(
            /(^|[^A-Za-z_])(\$?[A-Z]+\$?)(\d+)(?![A-Za-z0-9_])/g,
            (match, prefix, colPart, rowNum) => {
                const newRow = isFilled
                    ? parseInt(rowNum)
                    : modulStartRow + parseInt(rowNum) - 2;

                // Pisahkan bagian kolom dan $ baris jika ada
                const hasRowDollar = colPart.endsWith("$");
                const col = hasRowDollar ? colPart.slice(0, -1) : colPart;

                return `${prefix}${col}${hasRowDollar ? "$" : ""}${newRow}`;
            }
        );
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

            if (!group.component || group.component.length === 0) {
                // Store starting row for formula adjustment
                modulStartRows[mainModulName] = currentRow + 1;

                // Create main modul row with style
                data[currentRow] = {};
                columns.forEach((col, colIndex) => {
                    // Hanya tampilkan mainModulName jika component kosong dan ini kolom nama_modul
                    if (
                        (!group.component || group.component.length === 0) &&
                        colIndex === namaModulIndex
                    ) {
                        data[currentRow][colIndex] = {
                            v: mainModulName,
                            s: modulStyle,
                        };
                    }
                    // Jika ada data di mainModulData untuk kolom ini
                    else if (mainModulData[col] !== undefined) {
                        data[currentRow][colIndex] = {
                            v: mainModulData[col],
                            s: modulStyle,
                        };
                    }
                    // Kolom lainnya (termasuk nama_modul ketika ada component)
                    else {
                        data[currentRow][colIndex] = {
                            v: "",
                            s: modulStyle,
                        };
                    }
                });
                currentRow++;
            }

            // Process all component groups hanya jika ada component
            if (Array.isArray(group.component) && group.component.length > 0) {
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
                            const referenceModulName = nestedModulName;
                            const referenceModulStartRow =
                                modulStartRows[referenceModulName] ||
                                currentRow;

                            columns.forEach((col, colIndex) => {
                                const value =
                                    nestedModulData[col] !== undefined
                                        ? nestedModulData[col]
                                        : "";
                                const style = modulStyle;

                                if (colIndex === namaModulIndex) {
                                    // Handle nama_modul column specially
                                    data[currentRow][colIndex] = {
                                        v: nestedModulName,
                                        s: style,
                                    };
                                } else if (
                                    typeof value === "string" &&
                                    value.startsWith("=")
                                ) {
                                    // Handle formula cells
                                    data[currentRow][colIndex] = {
                                        f: adjustFormula(
                                            value,
                                            referenceModulStartRow,
                                            true
                                        ),
                                        v: "",
                                        s: style,
                                        t: 1,
                                    };
                                } else if (
                                    value !== undefined &&
                                    value !== ""
                                ) {
                                    // Handle regular values
                                    data[currentRow][colIndex] = {
                                        v: value,
                                        s: style,
                                    };
                                } else {
                                    // Empty cells
                                    data[currentRow][colIndex] = {
                                        v: "",
                                        s: style,
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
                                    modulStartRows[referenceModulName] ||
                                    currentRow;

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
                                                true
                                            ),
                                            v: "",
                                            s: style,
                                            t: 1,
                                        };
                                    } else {
                                        data[currentRow][colIndex] = {
                                            v: value,
                                            s: style,
                                            t: 1,
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
    const adjustFormula = (formula, modulStartRow, isFilled) => {
        // Hanya sesuaikan referensi $G3
        return formula.replace(/(\$G)(\d+)/g, (match, col, rowNum) => {
            const newRow = isFilled
                ? parseInt(rowNum)
                : modulStartRow + parseInt(rowNum) - 2;
            return `${col}${newRow}`;
        });
    };

    // Loop setiap part langsung (bukan per modul)
    partComponentsData.forEach((comp) => {
        const row = {};
        const componentData = comp.data || {};
        const componentStyles = comp.styles || {};

        // Loop kolom sesuai header
        dataValidationCol.forEach((col, index) => {
            const fieldKey = Object.keys(dataValMap).find(
                (key) => dataValMap[key] === col
            );

            const value = fieldKey
                ? componentData[fieldKey] !== null &&
                  componentData[fieldKey] !== undefined
                    ? componentData[fieldKey]
                    : ""
                : "";

            // Handle formula cells
            if (typeof value === "string" && value.startsWith("=")) {
                row[index] = {
                    f: adjustFormula(value),
                    v: "",
                    s: fieldKey ? componentStyles[fieldKey] || "" : "",
                };
            } else {
                row[index] = {
                    v: value,
                    s: fieldKey ? componentStyles[fieldKey] || "" : "",
                };
            }
        });

        data[rowIndex] = row;
        rowIndex++;
    });

    return {
        data,
        mergeCells: [],
    };
}

// Fungsi untuk membuat definisi kolom
function createColumnDefinitions() {
    return columns.map((col) => {
        return {
            name: col,
        };
    });
}

// Siapkan data untuk Univer
const { data: cellData, mergeCells } = prepareUniverData();
const columnDefs = createColumnDefinitions();
const { data: validationData, mergeCells: validationMerge } =
    prepareValidationSheetData();

// Buat workbook
const workbook = univerAPI.createWorkbook({
    name: "Components Sheet",
    sheetCount: 2,
    sheets: {
        sheet1: {
            id: "sheet1",
            name: "Components",
            tabColor: "#FF0000",
            hidden: BooleanNumber.FALSE,
            freeze: {
                xSplit: 7,
                ySplit: 1,
                startRow: 1,
                startColumn: 7,
            },
            zoomRatio: 0.8,
            rowCount: Math.max(2, Object.keys(cellData).length),
            columnCount: columns.length,
            defaultColumnWidth: 60,
            defaultRowHeight: 25,
            mergeData: mergeCells,
            cellData: cellData,
            rowData: [],
            columnData: columnDefs,
            rowHeader: {
                width: 40,
            },
            columnHeader: {
                height: 20,
            },
        },
        sheet2: {
            id: "sheet2",
            name: "Data Validation",
            tabColor: "#2563EB",
            zoomRatio: 0.8,
            hidden: BooleanNumber.FALSE,
            freeze: {
                xSplit: 1,
                ySplit: 1,
                startRow: 1,
                startColumn: 0,
            },
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
const componentSheet = workbook.getSheets("sheet1")[0];
if (componentSheet) {
    componentSheet.setColumnWidth(5, 130);
    componentSheet.setColumnWidth(6, 200);
    componentSheet.setColumnWidth(7, 150);
    componentSheet.setRowHeight(0, 80);
}

const validationSheet = workbook.getSheets()[1];
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
}

function applyFilteredDataValidations() {
    const definedNamed = JSON.parse(definedNames);

    // Filter hanya definedNames dengan nama 'prt' atau 'menu'
    const filteredDefNames = definedNamed.filter(
        (defName) =>
            defName.name === "menu" ||
            defName.name === "Prt" ||
            defName.name === "pk"
    );

    console.log("FilteredDefinedNames : ", filteredDefNames);

    // Mapping data
    const defNameToColumn = {
        menu: 1, // Kolom index 1 untuk menu
        Prt: 6, // Kolom index 6 untuk prt
        pk: 7, // kolom index 7 untuk pk
    };

    filteredDefNames.forEach((defName) => {
        const targetRange = worksheet.getRange(defName.formulaOrRefString);
        const columnIndex = defNameToColumn[defName.name];

        console.log("column Index : ", columnIndex);

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
                renderMode: univerAPI.Enum.DataValidationRenderMode.ARROW,
                allowInvalid: false,
                showDropDown: true,
                showErrorMessage: true,
                errorMessage: `Nilai harus ada dalam daftar yang ditentukan`,
                errorTitle: "Nilai Tidak Valid",
            })
            .build();

        const range = worksheet.getRange(1, columnIndex, 1000, 1);

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
        console.log(`Error applying dropdown to column ${columnIndex}:`, error);
    }
}

// fungsi untuk validasi data
applyFilteredDataValidations();

// Fungsi untuk mendapatkan semua data dari worksheet
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

    const cellData = [];

    cellDatas.forEach((row, rowIndex) => {
        const rowData = {};
        row.forEach((cell, colIndex) => {
            const cellObj = {};

            // Save formula if exists
            if (formulas[rowIndex]?.[colIndex]) {
                cellObj.f = formulas[rowIndex][colIndex];
            }

            // Save value - preserve zeros and other falsy values except undefined/null
            if (cell?.v !== undefined && cell?.v !== null) {
                cellObj.v = cell.v; // Preserve original value including 0, false, etc.
            }

            rowData[colIndex] = cellObj;
        });
        cellData.push(rowData);
    });

    // Apply styles to cells
    cellStyles.forEach((row, rowIndex) => {
        row.forEach((cell, colIndex) => {
            if (cell?._style && cellData[rowIndex]?.[colIndex]) {
                cellData[rowIndex][colIndex].s = cell._style;
            }
        });
    });

    return {
        cellData: cellData,
    };
}

// Event handler untuk perubahan dropdown modul
$("#modulSelect").on("change", function () {
    const selectedValue = $(this).val();
    if (!selectedValue) return;

    // Dapatkan workbook aktif
    const workbook = univerAPI.getActiveWorkbook();
    const sheet = workbook.getActiveSheet();

    // Kosongkan semua data kecuali header (baris 0)
    for (let row = 1; row < sheet.getMaxRows(); row++) {
        for (let col = 0; col < sheet.getMaxColumns(); col++) {
            const range = sheet.getRange(row, col);
            if (range) {
                range.clear();
            }
        }
    }

    // Set nilai modul di baris 1 (setelah header)
    const modulRange = sheet.getRange(1, namaModulIndex);
    if (modulRange) {
        modulRange.setValue(selectedValue);
    }

    // Jika perlu menambahkan baris kosong di bawahnya
    if (sheet.getMaxRows() < 2) {
        sheet.insertRows(1, 1);
    }
    applyFilteredDataValidations();
});

$("#modulReference").on("change", async function () {
    const modulValue = $(this).val();
    if (!modulValue) return;

    try {
        // 1. Ambil data dari server
        const response = await $.ajax({
            url: "/get-modul-data",
            method: "GET",
            data: { modul: modulValue },
        });

        if (!response.success || !response.components) {
            console.log("Tidak ada data untuk modul:", modulValue);
            return;
        }

        // 2. Parse data komponen
        let allModuls;
        try {
            allModuls =
                typeof response.components === "string"
                    ? JSON.parse(response.components)
                    : response.components;

            if (!Array.isArray(allModuls)) {
                throw new Error("Format data modul tidak valid");
            }
        } catch (err) {
            console.error("Gagal memproses data modul:", err);
            return;
        }

        // 3. Cari modul yang sesuai
        const selectedModul = allModuls.find(
            (modul) => modul.modul && modul.modul.nama_modul === modulValue
        );

        if (!selectedModul || !Array.isArray(selectedModul.components)) {
            console.log("Modul tidak ditemukan atau tidak memiliki komponen");
            return;
        }

        const componentsData = selectedModul.components;
        console.log("Komponen yang akan ditampilkan:", componentsData);

        // 4. Siapkan worksheet
        const workbook = univerAPI.getActiveWorkbook();
        const sheet = workbook.getActiveSheet();
        const lastColumn = sheet.getMaxColumns();

        // 5. Hitung baris yang dibutuhkan (dimulai dari row 2)
        const neededRows = componentsData.length;
        const startDataRow = 2; // Data dimulai dari row 2
        const totalNeededRows = startDataRow + neededRows;

        // 6. Bersihkan data lama dari row 2 ke bawah
        const lastRow = sheet.getMaxRows();
        if (lastRow >= startDataRow) {
            sheet
                .getRange(
                    startDataRow,
                    1,
                    lastRow - startDataRow + 1,
                    lastColumn
                )
                .clear();
        }

        // 7. Sesuaikan jumlah baris
        const currentRows = sheet.getMaxRows();

        if (currentRows < totalNeededRows) {
            // Tambah baris jika kurang
            const rowsToAdd = totalNeededRows - currentRows;
            sheet.insertRows(currentRows, rowsToAdd);
        } else if (currentRows > totalNeededRows) {
            // Hapus baris ekstra jika terlalu banyak
            const rowsToDelete = currentRows - totalNeededRows;
            sheet.deleteRows(totalNeededRows + 1, rowsToDelete);
        }

        // 8. Isi data ke sheet mulai dari row 2
        const updateOperations = [];

        componentsData.forEach((component, index) => {
            console.log("component : ", component);
            const row = startDataRow + index;

            // Isi semua kolom yang sesuai
            for (const [key, value] of Object.entries(component.data)) {
                const colIndex = columns.indexOf(key);
                if (colIndex >= 0 && value !== undefined && value !== null) {
                    updateOperations.push(
                        sheet.getRange(row, colIndex).setValue(value)
                    );
                }
            }
        });

        // 9. Eksekusi semua operasi update
        await Promise.all(updateOperations);

        // 10. Terapkan validasi data
        await applyFilteredDataValidations();

        console.log(
            `Data untuk modul ${modulValue} berhasil dimuat (${componentsData.length} komponen)`
        );
    } catch (error) {
        console.error("Error dalam memproses perubahan modul:", error);
    }
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
                    if (
                        modulGroup.component &&
                        modulGroup.component.length > 0
                    ) {
                        selectedComponents =
                            modulGroup.component[0].components || [];
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
            // Hanya proses referensi sel (A1, $A1, A$1, $A$1) tapi skip defined names
            return formula.replace(
                /(^|[^A-Za-z_])(\$?[A-Z]+\$?)(\d+)(?![A-Za-z0-9_])/g,
                (match, prefix, colPart, rowNum) => {
                    const newRow = isFilled
                        ? parseInt(rowNum)
                        : modulStartRow + parseInt(rowNum) - 1;

                    // Pisahkan bagian kolom dan $ baris jika ada
                    const hasRowDollar = colPart.endsWith("$");
                    const col = hasRowDollar ? colPart.slice(0, -1) : colPart;

                    return `${prefix}${col}${hasRowDollar ? "$" : ""}${newRow}`;
                }
            );
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
        console.log("Selected Component : ", selectedComponents);
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
        const adjustFormula = (formula, modulStartRow, isFilled) => {
            // Hanya proses referensi sel (A1, $A1, A$1, $A$1) tapi skip defined names
            return formula.replace(
                /(^|[^A-Za-z_])(\$?[A-Z]+\$?)(\d+)(?![A-Za-z0-9_])/g,
                (match, prefix, colPart, rowNum) => {
                    const newRow = isFilled
                        ? parseInt(rowNum)
                        : modulStartRow + parseInt(rowNum) - 1;

                    // Pisahkan bagian kolom dan $ baris jika ada
                    const hasRowDollar = colPart.endsWith("$");
                    const col = hasRowDollar ? colPart.slice(0, -1) : colPart;

                    return `${prefix}${col}${hasRowDollar ? "$" : ""}${newRow}`;
                }
            );
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

        // 9. Add components with formula adjustment
        selectedComponents.forEach((component, compIndex) => {
            const componentRow = newPartRow + compIndex;
            const mappedData = mapDataToColumns(component);
            console.log("MappedData : ", mappedData);

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
        breakdownSheet.setColumnWidth(namaModulIndex, 200);
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

        const selectElement = $("#sub-modulSelect");
        console.log("Memproses modul:", selectElement);
        const selectedModul = selectElement.val();

        if (!selectedModul) {
            alert("Silakan pilih modul terlebih dahulu");
            return;
        }

        console.log("Memproses modul:", selectedModul);

        // Tambahkan ke spreadsheet
        if (addModulToSpreadsheet(selectedModul)) {
            const modal = FlowbiteInstances.getInstance("Modal", "modul-modal");
            modal.hide();

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
