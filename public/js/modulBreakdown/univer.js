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
                                            group.isFilled || false
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

            // Periksa jika fieldKey ada dan componentData[fieldKey] tidak null atau undefined
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

console.log("component Data : ", componentData);

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
            defaultColumnWidth: 60,
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

function applyFilteredDataValidations() {
    const definedNamed = JSON.parse(definedNames);

    // Filter hanya definedNames dengan nama 'prt' atau 'menu'
    const filteredDefNames = definedNamed.filter(
        (defName) => defName.name === "menu" || defName.name === "Prt"
    );

    console.log("FilteredDefinedNames : ", filteredDefNames);

    // Mapping data
    const defNameToColumn = {
        menu: 1, // Kolom index 1 untuk menu
        Prt: 6, // Kolom index 6 untuk prt
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

const breakdownSheet = workbook.getSheets("sheet1")[0];
if (breakdownSheet) {
    // worksheet.setColumnWidth(1, 200);
    breakdownSheet.setColumnWidth(5, 130);
    breakdownSheet.setColumnWidth(6, 200);
    breakdownSheet.setColumnWidth(7, 150);
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

    const cellData = [];

    cellDatas.forEach((row, rowIndex) => {
        const rowData = {};
        row.forEach((cell, colIndex) => {
            const cellObj = {};

            // Jika ada formula, simpan formula aslinya
            if (formulas[rowIndex] && formulas[rowIndex][colIndex]) {
                cellObj.f = formulas[rowIndex][colIndex];
            }

            // Simpan nilai (jika ada)
            if (cell?.v !== undefined) {
                cellObj.v = cell.v || "";
            }

            rowData[colIndex] = cellObj;
        });
        cellData.push(rowData);
    });

    cellStyles.forEach((row, rowIndex) => {
        row.forEach((cell, colIndex) => {
            if (
                cell?._style &&
                cellData[rowIndex] &&
                cellData[rowIndex][colIndex]
            ) {
                cellData[rowIndex][colIndex].s = cell._style;
            }
        });
    });

    return {
        cellData: cellData,
    };
}
