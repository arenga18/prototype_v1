let spreadsheetInstance = null;

window.initSpreadsheet = function (data) {
    spreadsheetInstance = jspreadsheet(document.getElementById("spreadsheet"), {
        data: data,
        minDimensions: [data[0].length, data.length],
        columns: data[0].map(() => ({ type: "text" })),
    });
};

window.updateSpreadsheet = function (data) {
    if (spreadsheetInstance) {
        spreadsheetInstance.destroy();
    }
    spreadsheetInstance = jspreadsheet(document.getElementById("spreadsheet"), {
        data: data,
        minDimensions: [data[0].length, data.length],
        columns: data[0].map(() => ({ type: "text" })),
    });
};
