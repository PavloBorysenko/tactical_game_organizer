jQuery(document).ready(function ($) {
    // Initialize datetimepicker
    $('.timepicker').datetimepicker({
        timeOnly: true,
        timeFormat: tgoEventMeta.timeFormat,
        stepMinute: tgoEventMeta.interval,
        minTime: tgoEventMeta.minTime,
        maxTime: tgoEventMeta.maxTime,
        defaultTime: tgoEventMeta.minTime,
        controlType: 'select',
        oneLine: true,
    });

    $('.datepicker').datepicker({
        dateFormat: tgoEventMeta.dateFormat,
        minDate: 0,
        firstDay: 1,
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
    });

    // Validate date and time on form submit
    $('form#post').on('submit', function (e) {
        var date = $('#tgo_event_date').val();
        var time = $('#tgo_event_time').val();

        if (!date || !time) {
            e.preventDefault();
            alert(tgoEventMeta.i18n.selectDateTime);
            return false;
        }

        // Check if selected date is not in the past
        var selectedDate = new Date(date + 'T' + time);
        var now = new Date();

        if (selectedDate < now) {
            e.preventDefault();
            alert(tgoEventMeta.i18n.selectFutureDate);
            return false;
        }
    });
});
