const namaModulIndex = columns.indexOf("nama_modul");
const componentIndex = columns.indexOf("component");
const typeIndex = columns.indexOf("type");

console.log("componentTypes : ", componentTypes);
console.log("componentOption : ", componentOptions);

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

// Siapkan data untuk Univer
function prepareUniverData() {
    let data = {};

    // Tambahkan header kolom di baris pertama
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

    // Mulai data dari baris 1 (setelah header)
    let currentRow = 1;

    // Check if we have modul data to load
    if (modul || modulData[modul]) {
        let components = [];
        try {
            // Handle case when modulData[modul] is null/undefined
            const modulComponents = modulData[modul] || "[]";
            components = JSON.parse(modulComponents);
        } catch (err) {
            console.error("Error parsing modulData:", err);
            components = []; // Pastikan components tetap array meskipun error
        }

        // Set nama modul hanya di baris pertama data (baris 1)
        data[currentRow] = {};
        data[currentRow][namaModulIndex] = {
            v: modul,
        };
        currentRow++;

        // Pastikan components adalah array dan tidak null/undefined
        if (!Array.isArray(components)) {
            console.warn(
                `Data components untuk modul ${modul} bukan array, mengkonversi ke array kosong`
            );
            components = [];
        }

        // Hanya proses jika ada kolom component dan components tidak kosong
        if (componentIndex >= 0) {
            if (components && components.length > 0) {
                components.forEach((compObj) => {
                    // Pastikan compObj tidak null/undefined
                    if (compObj && typeof compObj === "object") {
                        data[currentRow] = {};

                        // Isi component dan field lainnya
                        if (compObj.component) {
                            data[currentRow][componentIndex] = {
                                v: compObj.component,
                            };
                        }

                        // Map other fields
                        Object.keys(compObj).forEach((key) => {
                            const colIndex = columns.indexOf(key);
                            if (colIndex >= 0) {
                                data[currentRow][colIndex] = {
                                    v: compObj[key],
                                };
                            }
                        });

                        currentRow++;
                    } else {
                        console.warn("Data component tidak valid:", compObj);
                    }
                });
            } else {
                console.log(`Modul ${modul} tidak memiliki components`);
                // Tambahkan baris kosong untuk menjaga struktur
                data[currentRow] = {};
                currentRow++;
            }
        } else {
            console.error("Kolom component tidak ditemukan.");
            // Tambahkan baris kosong untuk menjaga struktur
            data[currentRow] = {};
            currentRow++;
        }
    } else {
        console.warn("Tidak ada data component untuk modul:", modul);
        // Tambahkan baris kosong untuk menjaga struktur
        data[currentRow] = {};
        currentRow++;
    }

    // Add grouped components data if exists
    Object.entries(groupedComponents).forEach(
        ([modulName, components], modulIndex) => {
            // Set nama modul hanya di baris pertama grup
            data[currentRow] = {};
            data[currentRow][namaModulIndex] = {
                v: modulName || "",
            };
            currentRow++;

            if (Array.isArray(components)) {
                components.forEach((comp) => {
                    if (comp && typeof comp === "object") {
                        const rowData = mapDataToColumns(comp);
                        data[currentRow] = {};
                        Object.keys(rowData).forEach((col) => {
                            data[currentRow][col] = {
                                v: rowData[col],
                            };
                        });
                        currentRow++;
                    }
                });
            }

            // Tambahkan baris kosong antara modul
            if (modulIndex < Object.keys(groupedComponents).length - 1) {
                data[currentRow] = {};
                currentRow++;
            }
        }
    );

    return {
        data,
        mergeCells: [], // Return empty array for mergeCells
    };
}

// Fungsi untuk memetakan data ke kolom
function mapDataToColumns(comp) {
    let componentRow = {};

    // Map standard fields
    componentRow[componentIndex] = comp.component || comp.name || "";

    if (typeIndex >= 0) {
        componentRow[typeIndex] = comp.code || "";
    }

    if (namaModulIndex >= 0) {
        componentRow[namaModulIndex] = comp.modul || "";
    }

    // Map all fields from the component data to their respective columns
    Object.keys(comp).forEach((key) => {
        const colIndex = columns.indexOf(key);
        if (colIndex >= 0 && comp[key] !== undefined && comp[key] !== null) {
            componentRow[colIndex] = comp[key];
        }
    });

    // Map fields according to fieldMapping
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

    return componentRow;
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

// Buat workbook
const workbook = univerAPI.createWorkbook({
    name: "Components Sheet",
    sheetCount: 1,
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
    },
});

// Dapatkan instance worksheet
const worksheet = workbook.getActiveSheet();
console.log("worksheet : ", worksheet);
console.log("workbook : ", workbook);

function handleComponentChange(rowIndex, componentName) {
    const workbook = univerAPI.getActiveWorkbook();
    const sheet = workbook.getActiveSheet();

    // Find the matching component data
    const componentData = componentOptions.find(
        (opt) => opt.value === componentName
    )?.data;

    if (!componentData) return;

    // Map the component data to columns
    const rowData = mapDataToColumns(componentData);

    // Update the row with the component data
    Object.keys(rowData).forEach((colIndex) => {
        const value = rowData[colIndex];
        if (value !== undefined && value !== null && value !== "") {
            sheet.getRange(rowIndex, parseInt(colIndex)).setValue(value);
        }
    });

    // Reapply dropdowns to maintain validation
    applyAllDropdowns();
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
            })
            .build();

        const range = worksheet.getRange(
            1,
            columnIndex,
            worksheet.getMaxRows(),
            1
        );

        if (clearInvalid) {
            const values = range.getValue() || [];
            const safeValues = Array.isArray(values) ? values : [[values]];

            safeValues.forEach((row, i) => {
                const cellValue = Array.isArray(row) ? row[0] : row;
                if (
                    cellValue &&
                    !options.some((opt) => (opt.value || opt) === cellValue)
                ) {
                    worksheet.getRange(i + 1, columnIndex).clearValue();
                }
            });
        }

        range.setDataValidation(dropdownRule);

        // Add change listener for component column
        if (columnIndex === COLUMN_DROPDOWNS.component.index) {
            range.onChange(({ row, col, value }) => {
                if (row > 0) {
                    // Skip header row
                    handleComponentChange(row + 1, value); // +1 because onChange uses 0-based index
                }
            });
        }
    } catch (error) {
        console.error(
            `Error applying dropdown to column ${columnIndex}:`,
            error
        );
    }
}

const COLUMN_DROPDOWNS = {
    type: {
        index: 1,
        options: componentTypes,
    },
    component: {
        index: 6,
        options: componentOptions,
    },
    luar: {
        index: 18,
        options: componentOptions,
    },
    dalam: {
        index: 20,
        options: componentOptions,
    },
    rel: {
        index: 36,
        options: componentOptions,
    },
    engsel: {
        index: 37,
        options: componentOptions,
    },
    v: {
        index: 38,
        options: componentOptions,
    },
    v2: {
        index: 39,
        options: componentOptions,
    },
    h: {
        index: 40,
        options: componentOptions,
    },
    nama_barang: {
        index: 41,
        options: componentOptions,
    },
};

function applyAllDropdowns() {
    Object.values(COLUMN_DROPDOWNS).forEach(({ index, options }) => {
        applyDropdownToColumn(index, options);
    });
}

applyAllDropdowns();

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
    applyAllDropdowns();
});

$("#modulReference").on("change", async function () {
    const modulValue = $(this).val();
    if (!modulValue) return;

    try {
        const response = await $.ajax({
            url: "/get-modul-data",
            method: "GET",
            data: {
                modul: modulValue,
            },
        });

        if (!response.success) {
            console.warn("Tidak ada data untuk modul:", modulValue);
            return;
        }

        let components = [];
        try {
            components = JSON.parse(response.components);
            if (!Array.isArray(components)) {
                throw new Error("Data komponen bukan array");
            }
        } catch (err) {
            console.error("Gagal parse JSON:", err);
            return;
        }

        const workbook = univerAPI.getActiveWorkbook();
        const sheet = workbook.getActiveSheet();
        const componentIndex = columns.indexOf("component");

        if (componentIndex < 0) {
            console.error("Kolom component tidak ditemukan.");
            return;
        }

        // Kosongkan sheet (kecuali header)
        sheet
            .getRange(2, 1, sheet.getMaxRows() - 1, sheet.getMaxColumns())
            .clear();

        // Gunakan for...of untuk sequential execution
        for (const [i, compObj] of components.entries()) {
            const rowIndex = i + 2;

            // Tambahkan baris jika diperlukan
            if (rowIndex >= sheet.getMaxRows()) {
                sheet.insertRows(rowIndex, 1);
            }

            // Proses semua kolom secara synchronous
            for (const key of Object.keys(compObj)) {
                const colIndex = columns.indexOf(key);
                if (colIndex >= 0) {
                    const value = compObj[key];
                    if (value != null) {
                        await sheet
                            .getRange(rowIndex, colIndex)
                            .setValue(value);
                    }
                }
            }

            // Isi component column
            const componentValue = compObj.component || compObj.name || "";
            await sheet
                .getRange(rowIndex, componentIndex)
                .setValue(componentValue);
        }

        // Terapkan dropdown setelah semua data terisi
        await applyAllDropdowns();
    } catch (error) {
        console.error("Error:", error);
    }
});

// Event handler untuk tombol simpan
$(document).on("click", "#key-bindings-1", function () {
    const spreadsheetData = getAllData();
    const selectedModul = $("#modulSelect").val();
    const referenceModul = $("#modulReference").val();

    console.log(spreadsheetData);
    if (!selectedModul) {
        // Get the module name from namaModulIndex (assuming first data row)
        const firstDataRow = spreadsheetData[1]; // row 2 (0-based index 1)
        const modulName = firstDataRow[namaModulIndex];
        console.log("Module name from namaModulIndex: " + modulName);
    }

    // Rekonstruksi data dengan format yang benar
    const processedData = [];
    let currentModul = selectedModul;

    // Mulai dari baris 1 (setelah header)
    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Skip baris kosong
        if (Object.values(row).every((val) => val === "")) continue;

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== "") {
            currentModul = row[namaModulIndex];
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
            processedData.push({
                modul: currentModul,
                data: componentData,
            });
        }
    }

    // Use the first module name from namaModulIndex if selectedModul is empty
    const finalModul = selectedModul || spreadsheetData[1][namaModulIndex];

    const payload = {
        modul: finalModul,
        reference_modul: referenceModul,
        components: processedData,
        columns: columns,
    };

    console.log("Payload untuk simpan:", payload);

    $.ajax({
        url: "/save-spreadsheet",
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
    const selectedModul = $("#modulSelect").val();
    const referenceModul = $("#modulReference").val();

    if (!selectedModul) {
        alert("Pilih modul terlebih dahulu!");
        return;
    }

    // Rekonstruksi data dengan format yang benar
    const processedData = [];
    let currentModul = selectedModul;

    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Skip baris kosong
        if (Object.values(row).every((val) => val === "")) continue;

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== "") {
            currentModul = row[namaModulIndex];
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
            processedData.push({
                modul: currentModul,
                data: componentData,
            });
        }
    }

    const payload = {
        modul: selectedModul,
        reference_modul: referenceModul,
        components: processedData,
        columns: columns,
        recordId: recordId,
    };

    console.log("Processed Data:", payload);

    $.ajax({
        url: "/update-spreadsheet",
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
