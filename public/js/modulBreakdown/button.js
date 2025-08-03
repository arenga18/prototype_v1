// Event handler untuk tombol update
$(document).on("click", "#key-bindings-2", function () {
    const spreadsheetData = getAllData();
    let selectedModul = $("#modulSelect").val();
    const cellData = spreadsheetData.cellData;

    console.log("cell data : ", cellData);

    // Helper function to get cell value (prioritizes formula over value)
    const getCellValue = (cell) => {
        if (cell?.f !== undefined && cell.f !== "") return cell.f;
        if (cell?.v !== undefined && cell.v !== "") return cell.v;
        return "";
    };

    // Helper function to check if a cell has content
    const hasContent = (cell) => {
        return (
            (cell?.f !== undefined && cell.f !== "") ||
            (cell?.v !== undefined && cell.v !== "")
        );
    };

    // Auto-detect module if not selected
    if (!selectedModul) {
        for (let i = 1; i < cellData.length; i++) {
            const row = cellData[i];
            if (row[namaModulIndex] && hasContent(row[namaModulIndex])) {
                selectedModul = getCellValue(row[namaModulIndex]);
                break;
            }
        }

        if (!selectedModul) {
            alert("Tidak dapat menemukan modul dalam data!");
            return;
        }
    }

    // Process spreadsheet data into module breakdown structure
    const modulBreakdown = [];
    let currentModul = null;
    let currentModulObject = {};
    let currentComponents = [];

    for (let i = 1; i < cellData.length; i++) {
        const row = cellData[i];
        const rowStyles = {};

        // Process styles for the row
        Object.keys(row).forEach((colIndex) => {
            if (row[colIndex]?.s) {
                rowStyles[colIndex] = row[colIndex].s;
            }
        });

        // Check if this is a module row
        if (row[namaModulIndex] && hasContent(row[namaModulIndex])) {
            // Save previous module if exists
            if (currentModul) {
                cleanEmptyRowsAtEnd(currentComponents);
                modulBreakdown.push({
                    modul: currentModulObject,
                    components: currentComponents,
                });
            }

            // Start new module
            currentModul = getCellValue(row[namaModulIndex]);
            currentModulObject = { nama_modul: currentModul };
            currentComponents = [];

            // Add module data from this row
            columns.forEach((col, colIndex) => {
                if (row[colIndex] !== undefined) {
                    const value = getCellValue(row[colIndex]);
                    if (value !== "") {
                        currentModulObject[col] = value;
                    }
                }
            });
            continue;
        }

        // Process component row
        const componentData = {};
        const componentStyles = {};

        columns.forEach((col, colIndex) => {
            const cell = row[colIndex];
            if (cell) {
                // Get value (formula or value)
                const value = getCellValue(cell);
                if (value !== "") {
                    componentData[col] = value;
                }

                // Get style if exists
                if (cell.s) {
                    componentStyles[col] = cell.s;
                }
            }
        });

        // Add component if has data or style
        if (
            Object.keys(componentData).length > 0 ||
            Object.keys(componentStyles).length > 0
        ) {
            currentComponents.push({
                data: componentData,
                styles: componentStyles,
            });
        }
    }

    // Add the last module
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
                alert("Data berhasil diupdate!");
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
            // Hanya sesuaikan referensi $G3
            return formula.replace(/(\$G)(\d+)/g, (match, col, rowNum) => {
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
        const adjustFormula = (formula, modulStartRow, isFilled) => {
            // Hanya sesuaikan referensi $G3
            return formula.replace(/(\$G)(\d+)/g, (match, col, rowNum) => {
                const newRow = isFilled
                    ? parseInt(rowNum)
                    : modulStartRow + parseInt(rowNum) - 2;
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
