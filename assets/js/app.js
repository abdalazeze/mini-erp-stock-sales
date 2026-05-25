// Global CSRF header for all AJAX POSTs
$(function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var header = $('meta[name="csrf-header"]').attr('content') || 'X-CSRF-Token';

    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type)) {
                xhr.setRequestHeader(header, token);
            }
        }
    });
});