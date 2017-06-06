$(document).ready(function() {
    $('.url-select').click(function(e) {
        e.preventDefault();
        $('#form_site').val($(this).attr('data-url'));
        Materialize.updateTextFields();
    });
});