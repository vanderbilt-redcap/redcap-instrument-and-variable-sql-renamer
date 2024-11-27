<?php
namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

$pid = $_GET['pid'];
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
    <link type='text/css' href='<?=$module->getUrl('css/styles.css')?>' rel='stylesheet' media='screen' />
    <?php echo $module->loadREDCapJS(); ?>
    <script>
        $(document).ready(function () {
            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            });

            $('.autocomplete-input').keyup(function(){
                let term = $(this).val();
                getAutocompleteData(term,"old_var");
                if(term === ""){
                    $("#new_name_input").hide();
                }
            });

            $('[name=data_type]').change(function() {
                if ($("#input-data-old").val() !== "") {
                    getAutocompleteData($("#input-data-old").val(),"old_var");
                }else{
                    $("#new_name_input").hide();
                }
            });

            $(document).mouseup(function(e)
            {
                var container = $(".autocomplete-items");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0)
                {
                    container.hide();
                }
                if($("#input-data-old").val() === ""){
                    $("#new_name_input").hide();
                }
            });

            $('input, button').on( "click", function(e) {
                $(".autocomplete-start").hide();
                // $(e.target).hasClass('autocomplete-input')
                if ($(this).attr('autocomplete') == "button" || ($(this).attr('autocomplete') == "input" && $('.autocomplete-input').val() === "")) {
                    let type = $('input[name="data_type"]:checked').attr('id');
                    $("#select-"+type).show();
                }
            });

            $('.new-name-validation-input').keyup(function(){
                let term = $(this).val();
                getAutocompleteData(term,"new_var");
            });

            $('#save_data').submit(function (event) {
                let pid = "<?=$pid?>";
                let type = $('input[name="data_type"]:checked').attr('id');
                let old_var = $('#input-data-old').val();
                let new_var = $('#input-data-new').val();
                if(type == "instrument"){
                    console.log("....change")
                    console.log(new_var.replace(/^\s+|\s+$/g,''))
                    new_var = new_var.replace(/^\s+|\s+$/g,'')
                }
                console.log("pid: "+pid)
                console.log("type: "+type)
                console.log("old_var: "+old_var)
                console.log("new_var: "+new_var)
                return false;
                //$.ajax({
                //    method: "POST",
                //    url: <?//=json_encode($module->getUrl('saveData.php'))?>//,
                //    dataType: "json",
                //    data: {
                //        pid: pid,
                //        type: type,
                //        old_var: old_var,
                //        new_var: new_var
                //    }
                //}).done(function(response) {
                //
                //});
            });
        });

        function showData(id){
            $('[id^="select-"]').hide();
            $("#select-"+id).show();
        }

        function addDataToInput(value){
            $(".autocomplete-input").each(function (index, element) {
                if ($(element).is(":visible")) {
                    $(element).val(value);
                    $(".autocomplete-items").hide();
                    $("#new_name_input").show();
                    $(".new-name-validation-input").removeClass('danger-input');
                }
            });
        }

        function getAutocompleteData(term, option){
            let pid = "<?=$pid?>";
            let type = $('input[name="data_type"]:checked').attr('id');
            let new_var_found = false;

            $(".autocomplete-start").hide();
            if(option == "old_var") {
                $("#new_name_input").hide();
            }else{
                $("#new-name-confirm-btn").prop("disabled",false);
            }
            $("#warning-new-name-exists").hide();

            $.ajax({
                method: "POST",
                url: <?=json_encode($module->getUrl('getAutocompleteData.php'))?>,
                dataType: "json",
                data: {
                    term: term,
                    type: type,
                    option: option,
                    pid: pid
                }
            }).done(function(response) {
                if(option == "old_var") {
                    $(".autocomplete-search").show();
                }
                let lists = '';
                let aux = '';
                $.each(response, function(key, data) {
                    if(option == "old_var") {
                        if (type == "variable" && aux != data.group) {
                            aux = data.group;
                            lists += "<div class='group-header'>" + data.group + "</div>";
                        }
                        lists += "<div style='display: block'>";
                        if (type == "variable") {
                            lists += "<a tabindex='0' role='button' class='info-toggle' data-html='true' data-container='body' data-toggle='tooltip' data-trigger='hover' data-placement='right' style='outline: none;' title='" + data.info + "'><i class='fas fa-info-circle fa-fw' style='color:#0d6efd' aria-hidden='true'></i></a> ";
                        }
                        lists += "<a onclick='addDataToInput(\"" + data.value + "\")'>" + data.label + "</a></div>";
                    }else if(option == "new_var"){
                        new_var_found = true;
                        $("#warning-new-name-exists").show();
                        $(".new-name-validation-input").addClass('danger-input');
                        $("#new-name-confirm-btn").prop("disabled",true);
                    }
                });
                if(option == "old_var") {
                    $("#new_name_input").show();
                    $(".autocomplete-search").html(lists);
                }
            });
            if(!new_var_found){
                $(".new-name-validation-input").removeClass('danger-input');
            }
        }
    </script>
</head>
<body>
    <div class="container" style="display:none;margin-top: 20px">
        <div class="alert alert-success col-md-12" id="success_message"></div>
    </div>
    <div class="title" style="padding-top:15px">
        <div>
            Select the type of data you want to rename.
            <div style="padding-top:15px;padding-bottom: 15px;">
                <input type="radio" name="data_type" id="variable" onclick="showData(this.id)" checked> Variable
                <input type="radio" name="data_type" id="instrument" style="margin-left: 25px" onclick="showData(this.id)"> Instrument
            </div>
            <div>
                <div class="autocomplete-wrap">
                    <input type="text" autocomplete="input" class="x-form-text x-form-field autocomplete-input" id="input-data-old" style="width: 250px;">
                    <button autocomplete="button" listopen="0" tabindex="-1" onclick="" class="autocomplete-input ui-button ui-widget ui-state-default ui-corner-right rc-autocomplete" aria-label="Click to view choices"><img class="rc-autocomplete" src="/redcap_v14.8.2/Resources/images/arrow_state_grey_expanded.png" alt="Click to view choices"></button>
                    <div id="select-variable" class="autocomplete-start autocomplete-items" style="display:none">
                            <?php echo $module->printVariableList($pid); ?>
                    </div>
                    <div id="select-instrument" class="autocomplete-start autocomplete-items" style="display:none">
                        <?php echo $module->printInstrumentList($pid); ?>
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
        <div id="warning-new-name-exists" style="display:none;">*This Variable/Instrument already exists. Please change the name for a different one.</div>
    </div>
</body>
</html>

