$(document).ready(function () {
    // Base URL
    const baseUrl = window.location.origin;
    let currentNip = nip; // Store the current NIP

    // Function to update cabinet code based on selected options
    function updateCabinetCode() {
        // Get all codes from the selected options
        const unitCode =
            $("#description_unit option:selected").data("code") || "";
        const boxCode =
            $("#box_carcase_shape option:selected").data("code") || "";
        const finCode = $("#finishing option:selected").data("code") || "";
        const layerPosCode =
            $("#layer_position option:selected").data("code") || "";
        const boxContentCode =
            $("#box_carcase_content option:selected").data("code") || "";
        const closeSysCode =
            $("#closing_system option:selected").data("code") || "";
        const numClosuresCode =
            $("#number_of_closures option:selected").data("code") || "";
        const typeCloseCode =
            $("#type_of_closure option:selected").data("code") || "";
        const handleCode = $("#handle option:selected").data("code") || "";
        const accCode = $("#acc option:selected").data("code") || "";
        const lampCode = $("#lamp option:selected").data("code") || "";
        const plinthCode = $("#plinth option:selected").data("code") || "";

        // Construct the cabinet code
        let cabinetCode = [
            unitCode,
            "-",
            boxCode,
            finCode,
            layerPosCode,
            boxContentCode,
            "-",
            closeSysCode,
            numClosuresCode,
            typeCloseCode,
            handleCode,
            "-",
            accCode,
            lampCode,
            plinthCode,
        ].join("");

        // Update the display
        $("#cabinetCodeDisplay").val(cabinetCode);
        return cabinetCode;
    }

    // Function to handle form submission
    function updateModulData() {
        // Get all form values
        const formData = {
            modul: $("#codeCabinetSelect").val(),
            code_cabinet: $("#cabinetCodeDisplay").val(),
            description_unit: $("#description_unit").val(),
            box_carcase_shape: $("#box_carcase_shape").val(),
            finishing: $("#finishing").val(),
            layer_position: $("#layer_position").val(),
            box_carcase_content: $("#box_carcase_content").val(),
            closing_system: $("#closing_system").val(),
            number_of_closures: $("#number_of_closures").val(),
            type_of_closure: $("#type_of_closure").val(),
            handle: $("#handle").val(),
            acc: $("#acc").val(),
            lamp: $("#lamp").val(),
            plinth: $("#plinth").val(),
            nip: currentNip,
        };

        console.log("Form Data:", formData); // Debugging log

        // Validate NIP exists
        if (!currentNip) {
            alert("NIP tidak ditemukan!");
            return;
        }

        // Send update request
        $.ajax({
            url: `${baseUrl}/update-modul`,
            method: "PUT",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            contentType: "application/json", // Set content type
            data: JSON.stringify(formData), // Convert to JSON string
            success: function (response) {
                if (response.success) {
                    alert("Data modul berhasil diperbarui!");
                } else {
                    alert(
                        "Gagal memperbarui data: " +
                            (response.message || "Terjadi kesalahan")
                    );
                }
            },
            error: function (xhr, status, error) {
                // Add more detailed error logging
                console.log("Error details:", xhr.responseJSON);
                alert(
                    "Terjadi kesalahan saat memperbarui data: " +
                        (xhr.responseJSON?.message || error)
                );
            },
        });
    }

    // Load select data function
    function loadSelectData(selectElement) {
        const model = $(selectElement).data("model");
        const fieldId = $(selectElement).attr("id");

        $(selectElement).html('<option value="">Memuat data...</option>');

        $.ajax({
            url: `${baseUrl}/model-data/${model.toLowerCase()}`,
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response?.data) {
                    const $select = $(selectElement);
                    $select
                        .empty()
                        .append('<option value="">-- Pilih --</option>');

                    if (response.data.length === 0) {
                        $select.append(
                            '<option value="" disabled>Data kosong</option>'
                        );
                        return;
                    }

                    $.each(response.data, function (index, item) {
                        $select.append(
                            $("<option>", {
                                value: item.name,
                                text: item.name || `Item ${item.id}`,
                                "data-code": item.code || "",
                            })
                        );
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error(`Error loading ${model}:`, error);
                $(selectElement).html(
                    '<option value="">Gagal memuat data</option>'
                );
            },
        });
    }

    // Set select value function
    function setSelectValue(selector, value) {
        if (!value) return;
        const $select = $(selector);
        const valueStr = value.toString().trim();
        const $option = $select.find(`option[value="${valueStr}"]`).length
            ? $select.find(`option[value="${valueStr}"]`)
            : $select.find(`option:contains("${valueStr}")`).first();

        if ($option.length) {
            $select.val($option.val()).trigger("change");
        }
    }

    // Initialize all select elements
    $(".model-select")
        .each(function () {
            loadSelectData(this);
        })
        .on("change", updateCabinetCode);

    // Handle cabinet select change
    $("#codeCabinetSelect").on("change", function () {
        const selectedCode = $(this).val();
        if (!selectedCode) {
            $("#cabinetCodeDisplay").val("");
            return;
        }

        // Get NIP from somewhere (adjust according to your implementation)
        currentNip = nip;

        $.ajax({
            url: `${baseUrl}/modul-by-cabinet`,
            type: "GET",
            data: { code_cabinet: selectedCode, nip: currentNip },
            success: function (response) {
                if (response?.status === "success" && response.data?.length) {
                    const data = response.data[0];
                    $("#cabinetCodeDisplay").val(data.code_cabinet || "");

                    // Set all select values
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

                    updateCabinetCode();
                }
            },
        });
    });

    // Handle update button click
    $("[data-modal-hide='kodifikasi-modal']")
        .prev()
        .on("click", function (e) {
            e.preventDefault();
            updateModulData();
        });
});
