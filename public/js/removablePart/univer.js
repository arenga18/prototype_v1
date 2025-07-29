const namaModulIndex = columns.indexOf("nama_modul");
const componentIndex = columns.indexOf("component");
const typeIndex = columns.indexOf("type");

console.log("Part Data : ", partData);

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

function prepareUniverData() {
    let data = {};

    // Add column headers in the first row
    data[0] = {};
    columns.forEach((col, index) => {
        data[0][index] = {
            v: col,
            s: {
                bl: 1, // bold
                ht: 2, // horizontal text alignment
                vt: 2, // Vertical text alignment
                fs: 11, // Font size
            },
        };
    });

    // Start data from row 1 (after header)
    let currentRow = 1;

    // Process partData
    if (partData) {
        // Iterate through each key in partData
        Object.entries(partData).forEach(([partName, componentsJson]) => {
            try {
                // Parse the JSON string to array
                const components = JSON.parse(componentsJson || "[]");

                // Process each component
                components.forEach((compObj) => {
                    if (compObj && typeof compObj === "object") {
                        const componentWithModul = { ...compObj };

                        // Map all data using the helper function
                        const mappedData = mapDataToColumns(componentWithModul);

                        console.log("MappedData : ", mappedData);

                        // Convert to Univer cell format
                        data[currentRow] = {};
                        Object.entries(mappedData).forEach(
                            ([colIndex, value]) => {
                                data[currentRow][colIndex] = { v: value };
                            }
                        );

                        currentRow++;
                    }
                });
            } catch (err) {
                console.error(
                    `Error parsing components for part ${partName}:`,
                    err
                );
            }
        });
    }

    // If no data was added, add an empty row
    if (currentRow === 1) {
        data[currentRow] = {};
        currentRow++;
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
            rowCount: Math.max(0, Object.keys(cellData).length),
            columnCount: columns.length,
            defaultColumnWidth: 30,
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

// Dapatkan instance worksheet
const worksheet = workbook.getActiveSheet();

const validationSheet = workbook.getSheets()[1];
if (validationSheet) {
    validationSheet.setColumnWidth(2, 300);
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

function applyFilteredDataValidations() {
    const definedNamed = JSON.parse(definedNames);

    // Filter hanya definedNames dengan nama 'prt' atau 'menu'
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
                renderMode: univerAPI.Enum.DataValidationRenderMode.ARROW,
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

columns.forEach((col, index) => {
    if (index === namaModulIndex || index === componentIndex) {
        worksheet.setColumnWidth(index, 200);
    } else if (col === "proses_khusus") {
        worksheet.setColumnWidth(index, 140);
    } else {
        worksheet.setColumnWidth(index, 40);
    }
});

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

    const cellDatas = range.getCellDatas();
    console.log(cellDatas);
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

    return {
        cellData: cellData,
    };
}

// Event handler untuk tombol simpan
$(document).on("click", "#key-bindings-1", function () {
    const spreadsheetData = getAllData();
    console.log("Data spreadsheet:", spreadsheetData);

    // 1. Ambil partName dari row 1 (index 1)
    const row1 = spreadsheetData[1] || {};
    const partName = row1[componentIndex] || "default_part";

    // 2. Proses data row 1 sesuai format referensi
    const processedData = [];
    const componentData = {};

    columns.forEach((col, colIndex) => {
        if (row1[colIndex] !== undefined && row1[colIndex] !== "") {
            componentData[col] = row1[colIndex];
        }
    });

    if (Object.keys(componentData).length > 0) {
        processedData.push({
            modul: partName,
            data: componentData,
        });
    }

    // 3. Siapkan payload hybrid
    const payload = {
        part: partName,
        component: processedData,
    };

    console.log("Payload final:", payload);

    // 4. Kirim ke server
    $.ajax({
        url: "/save-removable-part",
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        contentType: "application/json",
        data: JSON.stringify(payload),
        success: function (data) {
            const msg =
                data.status === "success"
                    ? `Data berhasil disimpan! (${processedData.length} komponen)`
                    : "Gagal menyimpan: " + data.message;
            // alert(msg);
        },
        error: function (xhr) {
            alert("Error: " + (xhr.responseJSON?.message || xhr.statusText));
        },
    });
});

// Event handler untuk tombol update
$(document).on("click", "#key-bindings-2", function () {
    try {
        const spreadsheetData = getAllData();
        const cellData = spreadsheetData.cellData || [];

        // Validate we have data to process
        if (!Array.isArray(cellData)) {
            throw new Error("Invalid cell data format");
        }

        // Get part name from first row
        const firstRow = cellData[1] || {};
        const partName = firstRow[componentIndex]?.v || "default_part";

        // Process data rows
        const processedData = [];

        for (let i = 1; i < cellData.length; i++) {
            const row = cellData[i];
            if (!row || typeof row !== "object") continue;

            const componentData = {};
            const componentStyles = {};
            let hasValidData = false;

            columns.forEach((col, colIndex) => {
                const cell = row[colIndex];
                if (!cell) return;

                // Process cell value (skip empty values)
                if (typeof cell === "object") {
                    // Handle formula (always include if present)
                    if (cell.f !== undefined) {
                        componentData[col] = cell.f;
                        hasValidData = true;
                    }
                    // Handle value (only if not empty)
                    else if (
                        cell.v !== undefined &&
                        cell.v !== "" &&
                        cell.v !== null
                    ) {
                        componentData[col] = cell.v;
                        hasValidData = true;
                    }
                    // Handle style
                    if (cell.s) {
                        componentStyles[col] = cell.s;
                    }
                }
                // Handle non-object values (only if not empty)
                else if (cell !== "" && cell !== undefined && cell !== null) {
                    componentData[col] = cell;
                    hasValidData = true;
                }
            });

            // Only add to processedData if has valid data or styles
            if (hasValidData || Object.keys(componentStyles).length > 0) {
                processedData.push({
                    data: componentData,
                    ...(Object.keys(componentStyles).length > 0 && {
                        styles: componentStyles,
                    }),
                });
            }
        }

        // Validate we have at least some data
        if (processedData.length === 0) {
            throw new Error("No valid data found to process");
        }

        const payload = {
            part: partName,
            component: processedData,
            recordId: recordId,
        };

        console.log("Processed Data Payload:", payload);

        $.ajax({
            url: "/update-removable-part",
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                "Content-Type": "application/json",
            },
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.status === "success") {
                    Livewire.emit("refresh");
                    showToast("Data updated successfully", "success");
                } else {
                    showToast(
                        "Error: " + (response.message || "Unknown error"),
                        "error"
                    );
                }
            },
            error: function (xhr) {
                const errorMsg =
                    xhr.responseJSON?.message || "Terjadi kesalahan";
                showToast(errorMsg, "error");
                console.error("API Error:", xhr.responseJSON || xhr.statusText);
            },
        });
    } catch (error) {
        console.error("Processing Error:", error);
        showToast("Error processing data: " + error.message, "error");
    }
});

// Helper function for displaying notifications
function showToast(message, type = "info") {
    // Implement your preferred notification system here
    alert(`${type.toUpperCase()}: ${message}`);
}
