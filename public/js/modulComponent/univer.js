const namaModulIndex = columns.indexOf("nama_modul");
const componentIndex = columns.indexOf("component");
const typeIndex = columns.indexOf("type");

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
const formula = univerAPI.getFormula();

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

    if (groupedComponents?.array) {
        // Process each module group directly without uniqueGroups
        groupedComponents.array.forEach((group, modulIndex) => {
            const modulData = group.modul || {};
            const modulName = modulData.nama_modul || "";

            // Skip if no modul name
            if (!modulName) return;

            // Store starting row for formula adjustment
            modulStartRows[modulName] = currentRow + 1;

            // Create modul row with style
            data[currentRow] = {};
            columns.forEach((col, colIndex) => {
                if (modulData[col] !== undefined) {
                    data[currentRow][colIndex] = {
                        v: modulData[col],
                        s: modulStyle,
                    };
                } else if (colIndex === namaModulIndex) {
                    data[currentRow][colIndex] = {
                        v: modulName,
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

            // Process components array
            if (Array.isArray(group.component)) {
                group.component.forEach(
                    (componentGroup, componentGroupIndex) => {
                        // Handle nested module names in component groups
                        const nestedModulName =
                            componentGroup.modul?.nama_modul || modulName;

                        // Add nested module header if different from parent
                        if (nestedModulName !== modulName) {
                            data[currentRow] = {};
                            columns.forEach((col, colIndex) => {
                                if (colIndex === namaModulIndex) {
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

                        // Handle nested components structure
                        const components = componentGroup.components || [];

                        if (Array.isArray(components)) {
                            components.forEach((comp) => {
                                data[currentRow] = {};

                                // Map component data to columns
                                const rowData = mapDataToColumns(comp);

                                Object.keys(rowData).forEach((col) => {
                                    const colValue = rowData[col];
                                    data[currentRow][col] =
                                        typeof colValue === "string" &&
                                        colValue.startsWith("=")
                                            ? {
                                                  f: adjustFormula(
                                                      colValue,
                                                      modulStartRows[
                                                          nestedModulName
                                                      ] ||
                                                          modulStartRows[
                                                              modulName
                                                          ],
                                                      group.isFilled || false
                                                  ),
                                                  v: "",
                                              }
                                            : { v: colValue };
                                });

                                currentRow++;
                            });
                        }
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

    // Execute calculations after a short delay
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

const worksheet = workbook.getActiveSheet();
const componentSheet = workbook.getSheets("sheet1")[0];
if (componentSheet) {
    componentSheet.setRowHeight(0, 80);
}

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
            const row = startDataRow + index; // Mulai dari row 2

            // Isi semua kolom yang sesuai
            for (const [key, value] of Object.entries(component)) {
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

// Event handler untuk tombol simpan
$(document).on("click", "#key-bindings-1", function () {
    // 1. Ambil data spreadsheet dan nilai dari form
    const spreadsheetData = getAllData();
    let selectedModul = $("#modulSelect").val();
    const referenceModul = $("#modulReference").val();

    if (!selectedModul) {
        for (let i = 1; i < spreadsheetData.length; i++) {
            const row = spreadsheetData[i];
            if (row[namaModulIndex] && row[namaModulIndex] !== "") {
                selectedModul = row[namaModulIndex];
                break;
            }
        }

        // Jika masih kosong, beri pesan error
        if (!selectedModul) {
            alert("Tidak dapat menemukan modul dalam data!");
            return;
        }
    }

    // 3. Rekonstruksi data untuk dikirim ke server
    const modulBreakdown = [];
    let currentModul = null;
    let currentModulObject = {};
    let currentComponents = [];

    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== "") {
            // Simpan modul sebelumnya jika ada
            if (currentModul) {
                // Hapus baris kosong di akhir components sebelum menyimpan
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

            // Isi data modul dari baris ini
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

    // Simpan modul terakhir (dengan membersihkan baris kosong di akhir saja)
    if (currentModul) {
        cleanEmptyRowsAtEnd(currentComponents);

        modulBreakdown.push({
            modul: currentModulObject,
            components: currentComponents,
        });
    }

    // 4. Siapkan payload untuk dikirim ke server
    const payload = {
        modul: selectedModul,
        reference_modul: referenceModul,
        components: modulBreakdown,
        columns: columns,
        recordId: recordId,
    };

    console.log("Payload untuk simpan:", payload);

    // 5. Kirim data ke server
    $.ajax({
        url: "/save-spreadsheet",
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "Content-Type": "application/json",
        },
        data: JSON.stringify(payload),
        success: function (response) {
            if (response.status === "success") {
                alert("Data berhasil disimpan!");
            } else {
                alert("Gagal menyimpan data: " + response.message);
            }
        },
        error: function (xhr, status, error) {
            let errorMsg = "Terjadi kesalahan saat menyimpan";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            alert(errorMsg);
        },
    });
});

// Event handler untuk tombol update
$(document).on("click", "#key-bindings-2", function () {
    const spreadsheetData = getAllData();
    console.log("Spreadsheet : ", spreadsheetData);
    const selectedModul = $("#modulSelect").val();
    const referenceModul = $("#modulReference").val();

    if (!selectedModul) {
        alert("Pilih modul terlebih dahulu!");
        return;
    }

    // Format data untuk modul_breakdown sesuai struktur referensi
    const modulBreakdown = [];
    let currentModul = null;
    let currentModulObject = {};
    let currentComponents = [];

    for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== "") {
            // Simpan modul sebelumnya jika ada
            if (currentModul) {
                // Hanya hapus baris kosong di akhir array components
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

            // Isi data modul dari baris ini
            columns.forEach((col, colIndex) => {
                if (row[colIndex] !== undefined && row[colIndex] !== "") {
                    currentModulObject[col] = row[colIndex];
                }
            });
            continue;
        }

        // Proses baris komponen (tambahkan semua baris, termasuk yang kosong di tengah)
        const componentData = {};
        columns.forEach((col, colIndex) => {
            if (row[colIndex] !== undefined) {
                componentData[col] = row[colIndex] || "";
            }
        });
        currentComponents.push(componentData);
    }

    // Simpan modul terakhir (dengan membersihkan baris kosong di akhir saja)
    if (currentModul) {
        cleanEmptyRowsAtEnd(currentComponents);

        modulBreakdown.push({
            modul: currentModulObject,
            components: currentComponents,
        });
    }

    // Buat payload
    const payload = {
        modul: selectedModul,
        reference_modul: referenceModul,
        components: modulBreakdown,
        columns: columns,
        recordId: recordId,
    };

    console.log("Payload untuk update:", payload);

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
                    // Perbaikan di sini: komponen sebenarnya ada di component[0].components
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
