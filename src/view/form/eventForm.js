eventForm = function () {
    $("input[name='start_date']").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        onClose: function (selectedDate) {
            $("input[name='end_date']").datepicker("option", "minDate", selectedDate);
        }
    });
    $("input[name='end_date']").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        onClose: function (selectedDate) {
            $("input[name='start_date']").datepicker("option", "maxDate", selectedDate);
        }
    });
    $(".timepicker").timepicker({
        timeFormat: 'h:mm p',
        interval: 15,
        dropdown: true,
        scrollbar: true
    });
    $("form").validate({
        rules: {
            start_date: "dateISO",
            end_date: "dateISO",
            "repeat[interval_num]" : {
                step: 1,
                digits: true
            }
        }
    });
    if (!$("[name=recurring]").is(":checked")) $('.recurringSetting').hide();
    $('[name=recurring]').change(function() {
        if ($(this).is(":checked")) $('.recurringSetting').show();
        else $('.recurringSetting').hide();
    });
};
