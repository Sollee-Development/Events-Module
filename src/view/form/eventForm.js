onReady.form.event = function () {
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
        timeFormat: 'H:mm',
        interval: 15
    });
    $("form").validate({
        rules: {
            start_date: "dateISO",
            end_date: "dateISO"
        }
    });
};
