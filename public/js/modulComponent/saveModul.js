$(document).on("click", "#key-bindings-1, #key-bindings-2", function () {
    const isUpdate = $(this).attr("id") === "key-bindings-2";
    const { cellData } = getAllData();
    let selectedModul = $("#modulSelect").val() || detectModule(cellData);
    const referenceModul = $("#modulReference").val();

    if (!selectedModul) {
        alert("Tidak dapat menemukan modul dalam data!");
        return;
    }

    const modulBreakdown = processSpreadsheetData(cellData);
    const payload = createPayload(
        selectedModul,
        referenceModul,
        modulBreakdown
    );

    console.log(
        `Processed payload for ${isUpdate ? "update" : "save"}:`,
        payload
    );

    sendToServer(isUpdate, payload);
});

// Helper functions
function getCellValue(cell) {
    if (!cell) return "";
    if (cell.f !== undefined && String(cell.f).trim() !== "") return cell.f;
    if (cell.v !== undefined) {
        if (typeof cell.v === "string") return cell.v.trim();
        if (typeof cell.v === "number" || typeof cell.v === "boolean")
            return String(cell.v);
        return "";
    }
    return "";
}

function hasDataContent(cell) {
    if (!cell) return false;

    // Check for formula
    if (cell.f !== undefined && String(cell.f).trim() !== "") return true;

    // Check for value
    if (cell.v !== undefined) {
        if (typeof cell.v === "string") return cell.v.trim() !== "";
        if (typeof cell.v === "number") return !isNaN(cell.v);
        if (typeof cell.v === "boolean") return true;
    }

    return false;
}

function detectModule(cellData) {
    for (let i = 1; i < cellData.length; i++) {
        const row = cellData[i];
        if (row[namaModulIndex] && hasDataContent(row[namaModulIndex])) {
            return getCellValue(row[namaModulIndex]);
        }
    }
    return null;
}

function processSpreadsheetData(cellData) {
    const modulBreakdown = [];
    let currentModul = null;
    let currentModulObject = {};
    let currentComponents = [];

    cellData.slice(1).forEach((row) => {
        // Check if row is a module header
        if (row[namaModulIndex] && hasDataContent(row[namaModulIndex])) {
            if (currentModul) {
                // Remove last empty row if exists before starting new module
                if (
                    currentComponents.length > 0 &&
                    isRowEmpty(currentComponents[currentComponents.length - 1])
                ) {
                    currentComponents.pop();
                }
                addModuleIfValid(
                    currentModulObject,
                    currentComponents,
                    modulBreakdown
                );
            }

            currentModul = getCellValue(row[namaModulIndex]);
            currentModulObject = createModulObject(row);
            currentComponents = [];
            return;
        }

        // Process all rows (including empty ones within module)
        const component = processRowWithEmpty(row);
        if (component) {
            currentComponents.push(component);
        }
    });

    // Remove last empty row for the last module if exists
    if (
        currentComponents.length > 0 &&
        isRowEmpty(currentComponents[currentComponents.length - 1])
    ) {
        currentComponents.pop();
    }
    addModuleIfValid(currentModulObject, currentComponents, modulBreakdown);
    return modulBreakdown;
}

function processRowWithEmpty(row) {
    const componentData = {};
    const componentStyles = {};
    let rowHasData = false;

    columns.forEach((col, colIndex) => {
        const cell = row[colIndex];
        if (!cell) return;

        const value = getCellValue(cell);
        if (value) {
            componentData[col] = value;
            rowHasData = true;
        }

        if (cell.s) {
            componentStyles[col] = cell.s;
        }
    });

    return {
        data: componentData,
        styles: componentStyles,
        isEmpty: !rowHasData,
    };
}

function isRowEmpty(component) {
    return component.isEmpty;
}

function processDataRow(row) {
    const componentData = {};
    let rowHasContent = false;

    columns.forEach((col, colIndex) => {
        const cell = row[colIndex];
        if (!cell) return;

        const value = getCellValue(cell);
        if (value) {
            componentData[col] = value;
            rowHasContent = true;
        }
    });

    return rowHasContent ? { data: componentData } : null;
}

function createModulObject(row) {
    const modulObj = { nama_modul: getCellValue(row[namaModulIndex]) };

    columns.forEach((col, colIndex) => {
        if (row[colIndex] !== undefined) {
            const value = getCellValue(row[colIndex]);
            if (value) modulObj[col] = value;
        }
    });

    return modulObj;
}

function addModuleIfValid(modulObj, components, breakdown) {
    if (components.length > 0 || Object.keys(modulObj).length > 1) {
        breakdown.push({
            modul: modulObj,
            components: components.filter((comp) => !isRowEmpty(comp) || comp),
        });
    }
}

function createPayload(modul, referenceModul, components) {
    return {
        modul,
        reference_modul: referenceModul,
        components,
        columns,
        recordId,
    };
}

function sendToServer(isUpdate, payload) {
    $.ajax({
        url: isUpdate ? "/update-spreadsheet" : "/save-spreadsheet",
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "Content-Type": "application/json",
        },
        data: JSON.stringify(payload),
        success: handleResponse(isUpdate),
        error: handleError,
    });
}

function handleResponse(isUpdate) {
    return function (response) {
        const message =
            response.status === "success"
                ? `Data berhasil ${isUpdate ? "diupdate" : "disimpan"}!`
                : `${isUpdate ? "Update" : "Save"} failed: ${response.message}`;
        alert(message);
    };
}

function handleError(xhr) {
    const errorMsg = xhr.responseJSON?.message || "Terjadi kesalahan";
    alert(errorMsg);
}
