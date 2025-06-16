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

    // Loop setiap part (perhatikan struktur data Anda)
    partData.forEach((comp) => {
        const row = {};
        const componentData = comp.data || {}; // Akses properti 'data'

        // Loop kolom sesuai header
        dataValidationCol.forEach((col, index) => {
            const fieldKey = Object.keys(dataValMap).find(
                (key) => dataValMap[key] === col
            );

            // Akses nilai dari componentData
            row[index] = {
                v: fieldKey ? componentData[fieldKey] || "" : "",
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

const nameIndex = dataValidationCol.indexOf("name");

dataValidationCol.forEach((col, index) => {
    if (index === nameIndex) {
        worksheet.setColumnWidth(index, 180);
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

$(document).on("click", "#key-bindings-1", function () {
    const spreadsheetData = getAllData();

    const processedData = [];

    // Mulai dari baris 1 (setelah header)
    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Skip baris kosong
        if (Object.values(row).every((val) => val === "")) continue;

        // Proses baris komponen
        const componentData = {};
        dataValidationCol.forEach((col, colIndex) => {
            if (row[colIndex] !== undefined && row[colIndex] !== "") {
                componentData[col] = row[colIndex];
            }
        });

        if (Object.keys(componentData).length > 0) {
            processedData.push({
                data: componentData,
            });
        }
    }

    const payload = {
        part_component: processedData,
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
    console.log("Spreadsheet Data:", spreadsheetData);

    const processedData = [];

    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Skip baris kosong
        if (Object.values(row).every((val) => val === "")) continue;

        // Proses baris komponen
        const componentData = {};
        dataValidationCol.forEach((col, colIndex) => {
            if (row[colIndex] !== undefined && row[colIndex] !== "") {
                componentData[col] = row[colIndex];
            }
        });

        if (Object.keys(componentData).length > 0) {
            processedData.push({
                data: componentData,
            });
        }
    }

    const payload = {
        part_component: processedData,
        columns: dataValidationCol,
        recordId: recordId,
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
