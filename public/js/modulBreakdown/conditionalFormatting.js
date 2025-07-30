function conditionalFormattingKodeBahan() {
    const columns = [16, 17, 22, 23, 24, 25];
    const rowCount = 1000;

    // Buat array Range objects langsung
    const rangeObjects = columns.map((col) =>
        breakdownSheet.getRange(1, col, rowCount, 1).getRange()
    );
    const formatRules = [
        { value: 4, background: "#ed857e", fontColor: "black" },
        { value: 2, background: "#57ff7b", fontColor: "black" },
        { value: 22, background: "#d1db40", fontColor: "black" },
        { value: 11, background: "gray", fontColor: "white" },
        { value: 9, background: "#bdb833", fontColor: "white" },
        { value: 0, background: "#b2e0eb", fontColor: "black" },
        { value: 7, background: "#4642b8", fontColor: "white" },
        { value: 5, background: "#f8fa89", fontColor: "black" },
        { value: 3, background: "red", fontColor: "white" },
        { value: 1, background: "#41c42d", fontColor: "white" },
    ];

    formatRules.forEach((rule) => {
        const formattingRule = breakdownSheet
            .newConditionalFormattingRule()
            .whenNumberEqualTo(rule.value)
            .setRanges(rangeObjects)
            .setBackground(rule.background)
            .setFontColor(rule.fontColor)
            .setBold(true);

        breakdownSheet.addConditionalFormattingRule(formattingRule.build());
    });
}

function conditionalFormattingTbahan() {
    const columns = [15, 11];
    const rowCount = 1000;

    // Buat array Range objects langsung
    const rangeObjects = columns.map((col) =>
        breakdownSheet.getRange(1, col, rowCount, 1).getRange()
    );
    const formatRules = [
        { start: 0, end: 11, background: "#yellow", fontColor: "black" },
        { start: 12, end: 18, background: "#b2e0eb", fontColor: "black" },
        { start: 18.1, end: 19.9, background: "#52a354", fontColor: "black" },
    ];

    formatRules.forEach((rule) => {
        const formattingRule = breakdownSheet
            .newConditionalFormattingRule()
            .whenNumberBetween(rule.start, rule.end)
            .setRanges(rangeObjects)
            .setBackground(rule.background)
            .setFontColor(rule.fontColor)
            .setBold(true);

        breakdownSheet.addConditionalFormattingRule(formattingRule.build());
    });

    const formattingRule = breakdownSheet
        .newConditionalFormattingRule()
        .whenNumberGreaterThanOrEqualTo(20)
        .setRanges(rangeObjects)
        .setBackground("#399ef7")
        .setFontColor("black")
        .setBold(true);

    breakdownSheet.addConditionalFormattingRule(formattingRule.build());
}

function conditionalFormatting_P_L() {
    const columns = [8, 9];
    const rowCount = 1000;

    // Buat array Range objects langsung
    const rangeObjects = columns.map((col) =>
        breakdownSheet.getRange(1, col, rowCount, 1).getRange()
    );
    const formatRules = [
        { value: 2430, background: "#ed857e", fontColor: "black" },
        { value: 1210, background: "#ed857e", fontColor: "black" },
    ];

    formatRules.forEach((rule) => {
        const formattingRule = breakdownSheet
            .newConditionalFormattingRule()
            .whenNumberGreaterThan(rule.value)
            .setRanges(rangeObjects)
            .setBackground(rule.background)
            .setFontColor(rule.fontColor)
            .setBold(true);

        breakdownSheet.addConditionalFormattingRule(formattingRule.build());
    });
}

const prosesKhusus = breakdownSheet.getRange(1, 7, 1000, 1);

const formattingRule = breakdownSheet
    .newConditionalFormattingRule()
    .whenCellNotEmpty()
    .setRanges([prosesKhusus.getRange()])
    .setBackground("#e3b22b")
    .setFontColor("black")
    .setBold(true);

breakdownSheet.addConditionalFormattingRule(formattingRule.build());

conditionalFormattingKodeBahan();
conditionalFormattingTbahan();
conditionalFormatting_P_L();
