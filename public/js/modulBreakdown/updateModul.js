$(document).ready(function () {
    // Base URL
    const baseUrl = window.location.origin;

    // Fungsi untuk load data
    function loadSelectData(selectElement) {
        const model = $(selectElement).data("model");
        const fieldId = $(selectElement).attr("id");

        // Tampilkan loading
        $(selectElement).html('<option value="">Memuat data...</option>');

        $.ajax({
            url: `${baseUrl}/model-data/${model.toLowerCase()}`,
            type: "GET",
            dataType: "json",
            success: function (response) {
                console.log(`Response for ${model}:`, response); // Debug

                if (response && response.data) {
                    populateSelect(selectElement, response.data);
                } else {
                    showError(fieldId, "Data tidak valid dari server");
                }
            },
            error: function (xhr, status, error) {
                console.log(
                    `Error loading ${model}:`,
                    xhr.responseJSON || error
                );
                showError(
                    fieldId,
                    `Gagal memuat data: ${xhr.responseJSON?.message || error}`
                );
                $(selectElement).html(
                    '<option value="">Gagal memuat data</option>'
                );
            },
        });
    }

    // Fungsi untuk mengisi select
    function populateSelect(selectElement, data) {
        const $select = $(selectElement);
        $select.empty().append('<option value="">-- Pilih --</option>');

        if (data.length === 0) {
            $select.append('<option value="" disabled>Data kosong</option>');
            return;
        }

        $.each(data, function (index, item) {
            $select.append(
                $("<option>", {
                    value: item.id,
                    text: item.name || `Item ${item.id}`,
                })
            );
        });
    }

    // Fungsi tampilkan error
    function showError(fieldId, message) {
        $(`#${fieldId}_error`).text(message).show();
    }

    // Load data untuk semua select
    $(".model-select").each(function () {
        loadSelectData(this);
    });

    // Handle form submit
    $("#dynamicForm").submit(function (e) {
        e.preventDefault();
        alert("Form submitted!");
    });

    $("#codeCabinetSelect").on("change", function () {
        const selectedCode = $(this).val();

        if (!selectedCode) {
            $("#cabinetCodeDisplay").val("");
            return;
        }

        $("#cabinetCodeDisplay").val(selectedCode);

        // Ambil data modul berdasarkan kode kabinet
        $.ajax({
            url: `${baseUrl}/modul-by-cabinet`,
            type: "GET",
            data: {
                code_cabinet: selectedCode,
            },
            success: function (response) {
                if (response.status === "success") {
                    const data = response.data[0];

                    // Pastikan semua select sudah ter-load
                    const selectsLoaded = $(".model-select")
                        .toArray()
                        .every((select) => {
                            return $(select).find("option").length > 1; // Lebih dari 1 karena option pertama adalah "Loading..."
                        });

                    if (!selectsLoaded) {
                        console.warn(
                            "Beberapa select options belum selesai loading"
                        );
                        return;
                    }

                    // Set nilai masing-masing form field
                    $("#cabinetCodeDisplay").val(data.code_cabinet || "");

                    // Untuk select options, gunakan .val() dengan value yang sesuai
                    setSelectValue("#description_unit", data.description_unit);
                    setSelectValue(
                        "#box_carcase_shape",
                        data.box_carcase_shape
                    );
                    setSelectValue("#finishing", data.finishing);
                    setSelectValue("#layer_position", data.layer_position);
                    setSelectValue(
                        "#box_carcase_content",
                        data.box_carcase_content
                    );
                    setSelectValue("#closing_system", data.closing_system);
                    setSelectValue(
                        "#number_of_closures",
                        data.number_of_closures
                    );
                    setSelectValue("#type_of_closure", data.type_of_closure);
                    setSelectValue("#handle", data.handle);
                    setSelectValue("#acc", data.acc);
                    setSelectValue("#lamp", data.lamp);
                    setSelectValue("#plinth", data.plinth);

                    console.log("Form fields updated from modul data:", data);
                } else {
                    console.warn("Modul tidak ditemukan");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching modul by cabinet:", error);
            },
        });
    });

    function setSelectValue(selector, value) {
        if (!value) return;

        const $select = $(selector);
        value = value.toString().trim(); // Normalisasi value

        // Cari option yang value atau text-nya match
        const $matchedOption = $select
            .find(`option[value="${value}"], option:contains("${value}")`)
            .first();

        if ($matchedOption.length) {
            const matchedValue = $matchedOption.val();
            $select.val(matchedValue).trigger("change");
        } else {
            console.warn(`Value '${value}' tidak ditemukan di ${selector}`, {
                availableOptions: $select
                    .find("option")
                    .map((i, o) => $(o).val())
                    .get(),
            });
        }
    }
});
