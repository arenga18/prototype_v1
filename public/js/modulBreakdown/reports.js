$(document).ready(function () {
    const printButton = $("#print-report");
    const reportTypeSelect = $("#report_type");
    const pathSegments = window.location.pathname.split("/");
    const projectId = pathSegments[3];

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
        const result = [];

        $.each(cellDatas, function (rowIndex, row) {
            const rowData = {};
            $.each(row, function (colIndex, cell) {
                if (cell?.v !== undefined) {
                    rowData[colIndex] = cell.v || "";
                } else {
                    rowData[colIndex] = "";
                }
            });
            result.push(rowData);
        });

        return result;
    }

    printButton.on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const spreadsheetData = getAllData();
        console.log("Spreadsheet : ", spreadsheetData);
        const modulBreakdown = processModulBreakdown(spreadsheetData);

        // Send data to server
        sendDataToReport(
            modulBreakdown,
            reportTypeSelect.val(),
            projectInformation,
            projectId
        );
    });

    function processModulBreakdown(spreadsheetData) {
        const modulBreakdown = [];
        let currentModul = null;
        let currentModulObject = {};
        let currentComponents = [];

        $.each(spreadsheetData, function (i, row) {
            if (i === 0) return true; // skip header row

            if (Object.values(row).every((val) => val === "")) return true;

            if (row[namaModulIndex] && row[namaModulIndex] !== "") {
                if (currentModul) {
                    // Push the module as the first component
                    currentComponents.unshift(currentModulObject);
                    modulBreakdown.push({
                        components: currentComponents,
                    });
                }

                currentModul = row[namaModulIndex];
                currentModulObject = {
                    nama_modul: currentModul,
                };
                currentComponents = [];

                $.each(columns, function (colIndex, col) {
                    if (row[colIndex] !== undefined && row[colIndex] !== "") {
                        currentModulObject[col] = row[colIndex];
                    }
                });
                return true;
            }

            const componentData = {};
            $.each(columns, function (colIndex, col) {
                if (row[colIndex] !== undefined && row[colIndex] !== "") {
                    componentData[col] = row[colIndex];
                }
            });

            if (Object.keys(componentData).length > 0) {
                currentComponents.push(componentData);
            }
        });

        if (currentModul) {
            // Push the module as the first component
            currentComponents.unshift(currentModulObject);
            modulBreakdown.push({
                components: currentComponents,
            });
        }
        console.log("Modul Breakdown : ", modulBreakdown);

        return modulBreakdown;
    }

    async function sendDataToReport(
        data,
        reportType,
        projectInformation,
        projectId
    ) {
        try {
            const storeDataUrl = `/admin/projects/${projectId}/reports/store-data`;
            const csrfToken = $('meta[name="csrf-token"]').attr("content");

            // Validate data before sending
            if (!data || !Array.isArray(data)) {
                throw new Error("Invalid data format");
            }

            if (!reportType) {
                throw new Error("Report type is required");
            }

            const response = await $.ajax({
                url: storeDataUrl,
                type: "POST",
                contentType: "application/json",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                data: JSON.stringify({
                    data: data,
                    report_type: reportType,
                    projectInformation: projectInformation,
                }),
                dataType: "json",
            });

            alert("sukses");
            // Open report in new tab
            window.open(
                `/admin/projects/${projectId}/reports/${reportType}`,
                "_blank"
            );

            // Close modal
            const modal = FlowbiteInstances.getInstance(
                "Modal",
                "report-modal"
            );
            console.log("Modal : ", modal);
        } catch (error) {
            console.error("Error sending data:", error);
        }
    }
});
