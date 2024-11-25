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

            $('#select_variable').keyup(function(){
                let term = $(this).val();
                $.ajax({
                    method: "POST",
                    url: <?=json_encode($module->getUrl('hub-user-management/getUserInfoAutocomplete_AJAX.php'))?>,
                    dataType: "json",
                    data: {
                        term: term
                    }
                }).done(function(response) {
                    $("#user-list").show();
                    var lists = '';
                    $.each(response, function(key, user) {
                        lists += "<div class='autocomplete-items' onclick='addUserName(\"" + user.value + "\")'><a onclick='addUserName(\"" + user.value + "\")'>" +  user.label + "</a></div>";
                    });
                    $("#user-list").html(lists);
                });
            });

            $('.autocomplete-input').keyup(function(){
                let term = $(this).val();
                let pid = "<?=$pid?>";
                let type = $('input[name="data_type"]:checked').attr('id');
                $(".autocomplete-start").hide();
                $.ajax({
                    method: "POST",
                    url: <?=json_encode($module->getUrl('getAutocompleteData.php'))?>,
                    dataType: "json",
                    data: {
                        term: term,
                        type: type,
                        pid: pid
                    }
                }).done(function(response) {
                    $(".autocomplete-search").show();
                    var lists = '';
                    $.each(response, function(key, data) {
                        lists += "<div style='display: block'>";
                        if(type == "variable"){
                            lists += "<a tabindex='0' role='button' class='info-toggle' data-html='true' data-container='body' data-toggle='tooltip' data-trigger='hover' data-placement='right' style='outline: none;' title='"+data.info+"'><i class='fas fa-info-circle fa-fw' style='color:#0d6efd' aria-hidden='true'></i></a> ";
                        }
                        lists += "<a onclick='addDataToInput(\"" + data.value + "\")'>"+data.label+"</a></div>";
                    });
                    $(".autocomplete-search").html(lists);
                });
            });

            $(document).mouseup(function(e)
            {
                var container = $(".autocomplete-items");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0)
                {
                    container.hide();
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
                }
            });
        }
    </script>
</head>
<body>
    <div class="title" style="padding-top:15px">
        Select the type of data you want to rename.
        <div style="padding-top:15px;padding-bottom: 15px;">
            <input type="radio" name="data_type" id="variable" onclick="showData(this.id)" checked> Variable
            <input type="radio" name="data_type" id="instrument" style="margin-left: 25px" onclick="showData(this.id)"> Instrument
        </div>
        <div>
            <div class="autocomplete-wrap">
                <input type="text" autocomplete="input" class="x-form-text x-form-field autocomplete-input" id="input-variable" style="width: 250px;">
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
</body>
</html>

