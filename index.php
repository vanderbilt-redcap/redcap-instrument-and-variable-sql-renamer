<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

$pid = $_GET['pid'];
if (!array_key_exists("U", $_REQUEST) && $_REQUEST['message'] != "U") {
    $_SESSION['message'] = "";
    $_SESSION['message_type'] = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Cache-control" content="public">
    <meta name="theme-color" content="#fff">
    <link type='text/css' href='<?= $module->getUrl('css/styles.css') ?>' rel='stylesheet' media='screen'/>
    <?php
    echo $module->loadREDCapJS(); ?>
    <script>
        $(document).ready(function () {
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $('.autocomplete-input').keyup(function () {
                let term = $(this).val();
                if (term === "") {
                    $("#new_name_input").hide();
                }
                getAutocompleteData(term, "old_var");
            });

            $('[name=data_type]').change(function () {
                if ($("#input-data-old").val() !== "") {
                    getAutocompleteData($("#input-data-old").val(), "old_var");
                } else {
                    $("#new_name_input").hide();
                }
            });

            $(document).mouseup(function (e) {
                var container = $(".autocomplete-items");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    container.hide();
                }
                if ($("#input-data-old").val() === "") {
                    $("#new_name_input").hide();
                }
            });

            $('#btnConfirm').on("click", function (e) {
                let old_var = $('#input-data-old').val();
                let new_var = $('#input-data-new').val();
                saveData("<?=$pid?>", "instrument", old_var, new_var);
            });

            $('input, button').on("click", function (e) {
                $(".autocomplete-start").hide();
                // $(e.target).hasClass('autocomplete-input')
                if ($(this).attr('autocomplete') == "button" || ($(this).attr('autocomplete') == "input" && $('.autocomplete-input').val() === "")) {
                    let type = $('input[name="data_type"]:checked').attr('id');
                    $("#select-" + type).show();
                }
            });

            $('.new-name-validation-input').keyup(function () {
                let term = $(this).val();
                getAutocompleteData(term, "new_var");
            });

            $('#save_data').submit(function (event) {
                let pid = "<?=$pid?>";
                let type = $('input[name="data_type"]:checked').attr('id');
                let old_var = $('#input-data-old').val();
                let new_var = $('#input-data-new').val();
                if (type == "instrument") {
                    $("#confirmationForm").dialog({
                        width: 700,
                        modal: true,
                        enableRemoteModule: true
                    }).prev(".ui-dialog-titlebar").css("background", "#fff3cd").css("color", "#856404");
                } else {
                    if (/\s/g.test(new_var)) {
                        $("#warning-new-name-white-spaces").show();
                        $(".new-name-validation-input").addClass('danger-input');
                        $("#new-name-confirm-btn").prop("disabled", true);
                    } else {
                        saveData(pid, type, old_var, new_var);
                    }
                }
                return false;
            });
        });

        function saveData(pid, type, old_var, new_var) {
            $.ajax({
                method: "POST",
                url: <?=json_encode($module->getUrl('saveData.php'))?>,
                dataType: "json",
                data: {
                    pid: pid,
                    type: type,
                    old_var: old_var,
                    new_var: new_var
                },
                error: function (xhr, status, error) {
                    $("#dialogError").html(xhr.responseText);
                    $("#dialogError").dialog({
                        modal: true,
                        width: 800
                    }).prev(".ui-dialog-titlebar").css("background", "#f8d7da").css("color", "#721c24");
                },
                success: function (result) {
                    window.location = getMessageLetterUrl(window.location.href, "U");
                }
            });
        }

        function showData(id) {
            $('[id^="select-"]').hide();
            $("#select-" + id).show();
        }

        function addDataToInput(value) {
            $(".autocomplete-input").each(function (index, element) {
                if ($(element).is(":visible")) {
                    $(element).val(value);
                    $(".autocomplete-items").hide();
                    $("#new_name_input").show();
                    $(".new-name-validation-input").removeClass('danger-input');
                }
            });
        }

        function getAutocompleteData(term, option) {
            let pid = "<?=$pid?>";
            let type = $('input[name="data_type"]:checked').attr('id');
            let new_var_found = false;
            let old_var_found = false;

            $("#success_message").hide();
            $("#warning_message").hide();
            $(".autocomplete-start").hide();
            if (option == "old_var") {
                $("#new_name_input").hide();
            } else {
                $("#new-name-confirm-btn").prop("disabled", false);
            }
            $("#warning-new-name-exists").hide();
            $("#warning-new-name-white-spaces").hide()

            $.ajax({
                method: "POST",
                url: <?=json_encode($module->getUrl('getAutocompleteData.php'))?>,
                dataType: "json",
                data: {
                    term: term,
                    type: type,
                    option: option,
                    pid: pid
                },
                error: function (xhr, status, error) {
                    $("#dialogError").html(xhr.responseText);
                    $("#dialogError").dialog({
                        modal: true,
                        width: 800
                    }).prev(".ui-dialog-titlebar").css("background", "#f8d7da").css("color", "#721c24");
                },
                success: function (response) {
                    if (option == "old_var") {
                        $(".autocomplete-search").show();
                    }
                    let lists = '';
                    let aux = '';
                    $.each(response, function (key, data) {
                        if (option == "old_var") {
                            old_var_found = true;
                            if (type == "variable" && aux != data.group) {
                                aux = data.group;
                                lists += "<div class='group-header'>" + data.group + "</div>";
                            }
                            lists += "<div style='display: block'>";
                            if (type == "variable") {
                                lists += "<a tabindex='0' role='button' class='info-toggle' data-html='true' data-container='body' data-toggle='tooltip' data-trigger='hover' data-placement='right' style='outline: none;' title='" + data.info + "'><i class='fas fa-info-circle fa-fw' style='color:#0d6efd' aria-hidden='true'></i></a> ";
                            }
                            lists += "<a onclick='addDataToInput(\"" + data.value + "\")'>" + data.label + "</a></div>";
                        } else if (option == "new_var") {
                            new_var_found = true;
                            $("#warning-new-name-exists").show();
                            $(".new-name-validation-input").addClass('danger-input');
                            $("#new-name-confirm-btn").prop("disabled", true);
                        }
                    });
                    if (option == "old_var" && old_var_found) {
                        $("#new_name_input").show();
                        $(".autocomplete-search").html(lists);
                    } else if (option == "old_var" && !old_var_found) {
                        $(".autocomplete-items").hide();
                    }
                }
            });
            if (!new_var_found) {
                $(".new-name-validation-input").removeClass('danger-input');
            }
        }

        function getMessageLetterUrl(url, letter) {
            if (url.match(/(&message=)([A-Z]{1})/)) {
                url = url.replace(/(&message=)([A-Z]{1})/, "&message=" + letter);
            } else {
                url = url + "&message=" + letter;
            }
            return url;
        }
    </script>
</head>
<body>
<?php
if (array_key_exists('message_type', $_SESSION) && $_SESSION['message_type'] !== "" && array_key_exists(
        'message',
        $_SESSION
    ) && $_SESSION['message'] !== "") { ?>
    <?php
    if ($_SESSION['message_type'] == "success") { ?>
        <div class="alert alert-success col-md-12" style="margin-top: 20px"
             id="success_message"><?= $_SESSION['message'] ?></div>
    <?php
    } else { ?>
        <div class="alert alert-danger col-md-12" style="margin-top: 20px"
             id="warning_message"><?= $_SESSION['message'] ?></div>
    <?php
    } ?>
<?php
} ?>
<div class="title" style="padding-top:15px">
    <div class="alert alert-info" style="margin-bottom: 25px;width: 98%;">
        <div>Module that allows a superuser to change an Instrument or Variable name preserving all information
            previously associated with it.
        </div>
        <div>Changing a Variable/Instrument name will modify all data and metadata associated to the old name,
            includding the branching logic.
        </div>
        <br>
        <div><em>*Other features such as alerts, locking, field comments, etc. will not be modified.</em></div>
        <div><em>*Only super users have permissions to use this tool.</em></div>
        <div><br><br>STEPS:</div>
        <ol>
            <li>Select which type of data you want to modufy (variable or instrument).</li>
            <li>Select the name. You can use the drop down or type and find one.</li>
            <li>A new input will appear. Add the new name.</li>
            <ul>
                <li>For Instruments add the <strong>Form Name</strong> as it would show on the Designer.</li>
                <li>If a name already exists a message will show.</li>
            </ul>
        </ol>
    </div>
    <div>
        Select the type of data you want to rename.
        <div style="padding-top:15px;padding-bottom: 15px;">
            <input type="radio" name="data_type" id="variable" onclick="showData(this.id)" checked> Variable
            <input type="radio" name="data_type" id="instrument" style="margin-left: 25px" onclick="showData(this.id)">
            Instrument
        </div>
        <div>
            <div class="autocomplete-wrap">
                <input type="text" autocomplete="input" class="x-form-text x-form-field autocomplete-input"
                       id="input-data-old" style="width: 250px;">
                <button autocomplete="button" listopen="0" tabindex="-1" onclick=""
                        class="autocomplete-input ui-button ui-widget ui-state-default ui-corner-right rc-autocomplete"
                        aria-label="Click to view choices"><img class="rc-autocomplete"
                                                                src="/redcap_v14.8.2/Resources/images/arrow_state_grey_expanded.png"
                                                                alt="Click to view choices"></button>
                <div id="select-variable" class="autocomplete-start autocomplete-items" style="display:none">
                    <?php
                    echo $module->printVariableList($pid); ?>
                </div>
                <div id="select-instrument" class="autocomplete-start autocomplete-items" style="display:none">
                    <?php
                    echo $module->printInstrumentList($pid); ?>
                </div>
                <div class="autocomplete-search autocomplete-items" style="display:none"></div>
            </div>
        </div>
    </div>
</div>
<div style="display:none; padding-top:40px" id="new_name_input">
    <label>Add the new Variable/Instrument Name:</label>
    <input type="text" id="input-data-new" class="x-form-text x-form-field new-name-validation-input">
    <form method="POST" action="" id="save_data" style="display: inline">
        <button type="submit" class="btn btn-primary btn-xs" disabled id="new-name-confirm-btn">Confirm</button>
    </form>
    <div id="warning-new-name-exists" class="warning-message" style="display:none;">*This Variable/Instrument already
        exists. Please change the name for a different one.
    </div>
    <div id="warning-new-name-white-spaces" class="warning-message" style="display:none;">*Variable names cannot have
        white spaces. Please remove any spaces.
    </div>
</div>
<div id="confirmationForm" title="WARNING!" style="display:none;">
    <form method="POST" action="" id="data_confirmation">
        <div class="modal-body">
            <p>Are you sure you want to modify the form name? This will modify all variables associated with it and move
                them to the new instrument.</p>
        </div>
        <div class="modal-footer" style="padding-top: 30px;">
            <a class="btn btn-success" id='btnConfirm' name="btnConfirm" style="color: #fff;">Continue</a>
        </div>
    </form>
</div>
<div id="dialogError" title="Something went wrong..." style="diplay:none;"></div>
</body>
</html>
