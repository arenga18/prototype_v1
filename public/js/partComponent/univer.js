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

function prepareValidationSheetData() {
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

    console.log("Part Data : ", partData);

    // Loop setiap part
    partData.forEach((comp) => {
        const row = {};
        const componentData = comp.data || {};
        const componentStyles = comp.styles || {};

        // Loop kolom sesuai header
        dataValidationCol.forEach((col, index) => {
            const fieldKey = Object.keys(dataValMap).find(
                (key) => dataValMap[key] === col
            );

            // Akses nilai dari componentData
            const cellValue = fieldKey ? componentData[fieldKey] : undefined;
            const cellStyle = fieldKey ? componentStyles[fieldKey] || {} : {};

            row[index] = {
                v: fieldKey
                    ? cellValue !== undefined && cellValue !== null
                        ? cellValue
                        : ""
                    : "",
                s: cellStyle,
            };
        });

        data[rowIndex] = row;
        rowIndex++;
    });

    return {
        data,
        mergeCells: [],
    };
}

function mapDataToColumns(comp) {
    let componentRow = {};

    // Akses data melalui properti 'data' karena struktur Anda
    const componentData = comp.data || {};

    componentRow[componentIndex] =
        componentData.val || componentData.name || "";

    Object.entries(fieldMapping).forEach(([sourceField, targetColumn]) => {
        const colIndex = columns.indexOf(targetColumn);
        if (
            colIndex >= 0 &&
            componentData[sourceField] !== undefined &&
            componentData[sourceField] !== null
        ) {
            componentRow[colIndex] = componentData[sourceField];
        }
    });

    columns.forEach((col, index) => {
        if (
            index !== componentIndex &&
            index !== namaModulIndex &&
            componentData[col] !== undefined &&
            componentData[col] !== null
        ) {
            componentRow[index] = componentData[col];
        }
    });

    return componentRow;
}

const { data: validationData, mergeCells: validationMerge } =
    prepareValidationSheetData();

const workbook = univerAPI.createWorkbook({
    name: "Components Sheet",
    sheetCount: 1,
    sheets: {
        sheet1: {
            id: "sheet1",
            name: "Data Validation",
            tabColor: "#FF0000",
            zoomRatio: 0.8,
            hidden: BooleanNumber.FALSE,
            freeze: {
                xSplit: 4,
                ySplit: 1,
                startRow: 1,
                startColumn: 4,
            },
            rowCount: Math.max(10, Object.keys(validationData).length),
            columnCount: dataValidationCol.length,
            defaultColumnWidth: 60,
            defaultRowHeight: 30,
            mergeData: validationMerge,
            cellData: validationData,
            rowData: [],
            columnData: dataValidationCol.map((col) => ({ name: col })),
            rowHeader: { width: 40, height: 40 },
            columnHeader: { height: 30 },
        },
    },
});

const worksheet = workbook.getActiveSheet();
const validationSheet = workbook.getSheets()[0];
if (validationSheet) {
    validationSheet.setColumnWidth(2, 300);
    const definedNamed = JSON.parse(definedNames);
    definedNamed?.forEach((defName) => {
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

const nameIndex = dataValidationCol.indexOf("name");

dataValidationCol.forEach((col, index) => {
    if (index === nameIndex) {
        worksheet.setColumnWidth(index, 250);
    }
});

function getAllData() {
    const workbook = univerAPI.getActiveWorkbook();
    const worksheet = workbook.getActiveSheet();
    const range = worksheet.getRange(
        0,
        0,
        worksheet.getMaxRows(),
        worksheet.getMaxColumns()
    );

    // Ambil data sel, style dan formula
    const cellStyles = range.getCellStyles();
    const cellDatas = range.getCellDatas();
    const formulas = range.getFormulas();

    // Ambil defined names dari workbook
    const definedNamesServices = workbook.getDefinedNames();

    // Proses defined names
    const definedNames = [];

    if (definedNamesServices && definedNamesServices.length > 0) {
        definedNamesServices.forEach((service) => {
            if (service._definedNameParam) {
                definedNames.push({
                    name: service._definedNameParam.name || "",
                    formulaOrRefString:
                        service._definedNameParam.formulaOrRefString || "",
                    id: service._definedNameParam.id || "",
                    sheetReference: service._definedNameParam.formulaOrRefString
                        ? service._definedNameParam.formulaOrRefString
                              .split("!")[0]
                              .replace(/'/g, "")
                        : "Current Sheet",
                });
            }
        });
    }

    // Proses data sel
    const cellData = [];

    // Pertama, proses semua data sel termasuk formula dan nilai
    cellDatas.forEach((row, rowIndex) => {
        const rowData = {};
        row.forEach((cell, colIndex) => {
            const cellObj = {};

            // Jika ada formula, simpan formula aslinya
            if (formulas[rowIndex] && formulas[rowIndex][colIndex]) {
                cellObj.f = formulas[rowIndex][colIndex];
            }

            // Simpan nilai (jika ada), termasuk nilai 0
            if (cell?.v !== undefined && cell?.v !== null) {
                cellObj.v = cell.v; // Simpan nilai apa adanya, termasuk 0
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
        definedNames: definedNames,
    };
}

$(document).on("click", "#key-bindings-1", function () {
    const spreadsheetData = getAllData();
    const cellData = spreadsheetData.cellData;
    const processedData = [];

    // Mulai dari baris 1 (setelah header)
    for (let i = 1; i < cellData.length; i++) {
        const row = cellData[i];
        const componentData = {};
        const componentStyles = {};

        dataValidationCol.forEach((col, colIndex) => {
            const cell = row[colIndex];

            // Handle value
            if (cell && (cell.v !== undefined || cell.f !== undefined)) {
                componentData[col] = cell.f ? cell.f : cell.v;
            } else if (
                cell &&
                typeof cell === "object" &&
                cell.value !== undefined
            ) {
                componentData[col] = cell.value;
            } else if (cell !== undefined && cell !== "") {
                componentData[col] = cell;
            } else {
                componentData[col] = "";
            }

            // Handle style
            if (cell && cell.s) {
                componentStyles[col] = cell.s;
            }
        });

        if (Object.values(componentData).some((val) => val !== "")) {
            processedData.push({
                data: componentData,
                styles: componentStyles,
            });
        }
    }

    const payload = {
        part_component: processedData,
        defined_names: spreadsheetData.definedNames,
    };

    console.log("Payload untuk simpan:", payload);

    $.ajax({
        url: "/save-part",
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        contentType: "application/json",
        data: JSON.stringify(payload),
        success: function (data) {
            if (data.status === "success") {
                alert("Data berhasil disimpan!");
            } else {
                alert("Gagal menyimpan data: " + data.message);
            }
        },
        error: function (xhr, status, error) {
            alert("Error: " + error);
        },
    });
});

// Event handler untuk tombol update
$(document).on("click", "#key-bindings-2", function () {
    const spreadsheetData = getAllData();
    console.log("SPREADSHEET : ", spreadsheetData);
    const cellData = spreadsheetData.cellData;
    const processedData = [];

    for (let i = 1; i < cellData.length; i++) {
        const row = cellData[i];
        const componentData = {};
        const componentStyles = {};
        let hasData = false;

        dataValidationCol.forEach((col, colIndex) => {
            const cell = row[colIndex];
            if (!cell) return; // Skip if cell is null/undefined

            // Handle value - skip if v is empty or undefined
            if (cell.v !== undefined && cell.v !== null && cell.v !== "") {
                componentData[col] = cell.f ? cell.f : cell.v;
                hasData = true;
            } else if (cell.f !== undefined) {
                componentData[col] = cell.f;
                hasData = true;
            } else if (typeof cell === "object" && cell.value !== undefined) {
                componentData[col] = cell.value;
                hasData = true;
            } else if (
                typeof cell !== "object" &&
                cell !== undefined &&
                cell !== ""
            ) {
                componentData[col] = cell;
                hasData = true;
            }

            // Handle style - only add if s exists and is not empty
            if (cell.s && Object.keys(cell.s).length > 0) {
                componentStyles[col] = cell.s;
            }
        });

        if (hasData) {
            const dataItem = {
                data: componentData,
            };

            // Only add styles object if it's not empty
            if (Object.keys(componentStyles).length > 0) {
                dataItem.styles = componentStyles;
            }

            processedData.push(dataItem);
        }
    }

    const payload = {
        part_component: processedData,
        columns: dataValidationCol,
        recordId: recordId,
        defined_names: spreadsheetData.definedNames,
    };

    console.log("Processed Data:", payload);

    $.ajax({
        url: "/update-part",
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "Content-Type": "application/json",
        },
        data: JSON.stringify(payload),
        success: function (response) {
            if (response.status === "success") {
                // alert("data berhasil diperbarui");
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
