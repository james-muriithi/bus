$(document).ready(function() {
    $('#date').bootstrapMaterialDatePicker({
        time: false,
        clearButton: true,
        minDate: new Date('2019-12-27'),
        maxDate: new Date('2019-12-27'),
        currentDate: new Date('2019-12-27'),
        switchOnClick: true
    });
});