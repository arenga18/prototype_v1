document.addEventListener("DOMContentLoaded", function () {
    const printButton = document.getElementById("print-report");
    const reportTypeSelect = document.getElementById("report_type");

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

        cellDatas.forEach((row, rowIndex) => {
            const rowData = {};
            row.forEach((cell, colIndex) => {
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

    printButton.addEventListener("click", function () {
        const spreadsheetData = getAllData();
        const modulBreakdown = processModulBreakdown(spreadsheetData);

        // Kirim data ke server sebelum membuka laporan
        sendDataToReport(
            modulBreakdown,
            reportTypeSelect.value,
            projectInformation
        );
    });

    function processModulBreakdown(spreadsheetData) {
        const modulBreakdown = [];
        let currentModul = null;
        let currentModulObject = {};
        let currentComponents = [];

        for (let i = 1; i < spreadsheetData.length; i++) {
            const row = spreadsheetData[i];

            if (Object.values(row).every((val) => val === "")) continue;

            if (row[namaModulIndex] && row[namaModulIndex] !== "") {
                if (currentModul) {
                    modulBreakdown.push({
                        modul: currentModulObject,
                        components: currentComponents,
                    });
                }

                currentModul = row[namaModulIndex];
                currentModulObject = { nama_modul: currentModul };
                currentComponents = [];

                columns.forEach((col, colIndex) => {
                    if (row[colIndex] !== undefined && row[colIndex] !== "") {
                        currentModulObject[col] = row[colIndex];
                    }
                });
                continue;
            }

            const componentData = {};
            columns.forEach((col, colIndex) => {
                if (row[colIndex] !== undefined && row[colIndex] !== "") {
                    componentData[col] = row[colIndex];
                }
            });

            if (Object.keys(componentData).length > 0) {
                currentComponents.push(componentData);
            }
        }

        if (currentModul) {
            modulBreakdown.push({
                modul: currentModulObject,
                components: currentComponents,
            });
        }
        console.log("Modul Breakdown : ", modulBreakdown);

        return modulBreakdown;
    }

    async function sendDataToReport(data, reportType, projectInformation) {
        try {
            const storeDataUrl = "/reports/store-data";
            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            ).content;

            console.log("data : ", data);

            // Validate data before sending
            if (!data || !Array.isArray(data)) {
                throw new Error("Invalid data format");
            }

            if (!reportType) {
                throw new Error("Report type is required");
            }

            const response = await fetch(storeDataUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    data: data,
                    report_type: reportType,
                    projectInformation: projectInformation,
                }),
            });

            const responseData = await response.json();

            if (!response.ok) {
                // Handle validation errors
                if (response.status === 422 && responseData.errors) {
                    const errors = Object.values(responseData.errors).join(
                        "\n"
                    );
                    throw new Error(`Validation error: ${errors}`);
                }
                throw new Error(
                    responseData.message ||
                        `HTTP error! status: ${response.status}`
                );
            }

            alert("sukses");
            // Open report in new tab
            window.open(`/reports/${reportType}`, "_blank");

            // Close modal
            const modal = document.getElementById("reports-modal");
            if (modal) modal.classList.add("hidden");
        } catch (error) {
            console.error("Error sending data:", error);
            alert(`Gagal mengirim data ke laporan: ${error.message}`);
        }
    }
});
