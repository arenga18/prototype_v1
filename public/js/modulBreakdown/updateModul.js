$(document).on(
    "click",
    "button[data-modal-target='kodifikasi-modal']",
    function (e) {
        e.preventDefault();
        e.stopPropagation();
        const baseUrl = window.location.origin;
        let currentNip = nip;

        // Get the modal elements
        const $loadingSpinner = $("#modal-loading-spinner");
        const $formContent = $("#modal-form-content");

        function showLoading() {
            $loadingSpinner.removeClass("hidden");
            $formContent.addClass("hidden");
        }

        function hideLoading() {
            $loadingSpinner.addClass("hidden");
            $formContent.removeClass("hidden");
        }

        showLoading();

        // Array to hold all promises for API calls
        const apiCalls = [];

        // Function to update cabinet code based on selected options
        function updateCabinetCode() {
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

            $("#cabinetCodeDisplay").val(cabinetCode);
            return cabinetCode;
        }

        // Function to handle form submission
        function updateModulData() {
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
                recordId: recordId,
            };

            console.log("Form Data:", formData);

            if (!currentNip) {
                alert("NIP tidak ditemukan!");
                return;
            }

            $.ajax({
                url: `${baseUrl}/update-modul`,
                method: "PUT",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                contentType: "application/json",
                data: JSON.stringify(formData),
                success: function (response) {
                    if (response.success) {
                        alert("Data modul berhasil diperbarui!");
                        console.log(
                            "modul_references:",
                            response.modul_references
                        );
                        $.ajax({
                            url: `${baseUrl}/get-modul`,
                            type: "GET",
                            data: {
                                modul_reference: response.modul_references,
                                recordId: recordId,
                            },
                            success: function (groupedResponse) {
                                if (groupedResponse.success) {
                                    console.log(
                                        "Updated grouped components:",
                                        groupedResponse
                                    );

                                    groupedComponents =
                                        groupedResponse.groupedComponents;

                                    // Prepare the new sheet data
                                    const sheetData =
                                        prepareBreakdownSheetData();

                                    console.log("sheetData : ", sheetData);

                                    console.log(
                                        "groupedComponents : ",
                                        groupedComponents
                                    );

                                    if (breakdownSheet && sheetData) {
                                        breakdownSheet._worksheet._snapshot.cellData =
                                            sheetData.data;

                                        breakdownSheet._worksheet._cellData._matrix =
                                            sheetData.data;

                                        breakdownSheet._worksheet._rowManager._config.cellData =
                                            sheetData.data;

                                        breakdownSheet.refreshCanvas();
                                    }
                                } else {
                                    console.error(
                                        "Error in response:",
                                        groupedResponse.message
                                    );
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error(
                                    "Error fetching grouped components:",
                                    error
                                );
                                console.log(
                                    "Full error response:",
                                    xhr.responseJSON
                                );
                            },
                        });
                    } else {
                        alert(
                            "Gagal memperbarui data: " +
                                (response.message || "Terjadi kesalahan")
                        );
                    }
                },
                error: function (xhr, status, error) {
                    console.log("Error details:", xhr.responseJSON);
                    alert(
                        "Terjadi kesalahan saat memperbarui data: " +
                            (xhr.responseJSON?.message || error)
                    );
                },
            });
        }

        // Load select data function, now returns a Promise
        function loadSelectData(selectElement) {
            const model = $(selectElement).data("model");

            console.log("MODEL : ", model);
            const fieldId = $(selectElement).attr("id");

            $(selectElement).html('<option value="">Memuat data...</option>');

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: `${baseUrl}/model-data/${model.toLowerCase()}`,
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        if (response?.data) {
                            const $select = $(selectElement);
                            $select
                                .empty()
                                .append(
                                    '<option value="">-- Pilih --</option>'
                                );

                            if (response.data.length === 0) {
                                $select.append(
                                    '<option value="" disabled>Data kosong</option>'
                                );
                                resolve();
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
                            resolve(); // Resolve the promise on success
                        } else {
                            reject("Invalid data format"); // Reject if data is not as expected
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(`Error loading ${model}:`, error);
                        $(selectElement).html(
                            '<option value="">Gagal memuat data</option>'
                        );
                        reject(error); // Reject the promise on error
                    },
                });
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

        // Collect all promises from loading select data
        $(".model-select").each(function () {
            apiCalls.push(loadSelectData(this));
        });

        // Handle cabinet select change
        $("#codeCabinetSelect").on("change", function () {
            showLoading();
            const selectedCode = $(this).val();
            if (!selectedCode) {
                $("#cabinetCodeDisplay").val("");
                hideLoading();
                return;
            }

            currentNip = nip;

            $.ajax({
                url: `${baseUrl}/modul-by-cabinet`,
                type: "GET",
                data: { code_cabinet: selectedCode, nip: currentNip },
                success: function (response) {
                    if (
                        response?.status === "success" &&
                        response.data?.length
                    ) {
                        const data = response.data[0];
                        $("#cabinetCodeDisplay").val(data.code_cabinet || "");

                        setSelectValue(
                            "#description_unit",
                            data.description_unit
                        );
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
                        setSelectValue(
                            "#type_of_closure",
                            data.type_of_closure
                        );
                        setSelectValue("#handle", data.handle);
                        setSelectValue("#acc", data.acc);
                        setSelectValue("#lamp", data.lamp);
                        setSelectValue("#plinth", data.plinth);

                        updateCabinetCode();
                    }
                    hideLoading(); // Hide loading after data is loaded and set
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching modul data:", error);
                    alert("Gagal memuat data modul.");
                    hideLoading(); // Hide loading on error
                },
            });
        });

        // Once all initial select data promises are resolved
        Promise.all(apiCalls)
            .then(() => {
                console.log("All select data loaded.");
                hideLoading();
                if ($("#codeCabinetSelect").val()) {
                    $("#codeCabinetSelect").trigger("change");
                }
            })
            .catch((error) => {
                console.error("One or more API calls failed:", error);
                hideLoading(); // Hide loading on any error
                alert("Gagal memuat beberapa data. Silakan coba lagi.");
            });

        // Attach event listener for updating cabinet code after initial load
        $(".model-select").on("change", updateCabinetCode);

        // Handle update button click
        $("[data-modal-hide='kodifikasi-modal']")
            .prev()
            .on("click", function (e) {
                e.preventDefault();
                updateModulData();
            });
    }
);
