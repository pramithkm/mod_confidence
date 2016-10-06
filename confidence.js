// TODO Move from here
$('.noscript').hide();

$('[id*="confidence_" ]').change(function() {
    var instance = $(this).data('instance');
    var level = $(this).val();
    var myurl = M.cfg.wwwroot+'/mod/confidence/confidence.php?level='+level+'&instance='+instance;
    $.ajax({
        url: myurl,
        type: 'POST',
        error: function(xhr, status, error) {
            msg = xhr.responseText;
            $('.confidence_message_'+instance).html(msg);
        },
        success: function (data) {
            if (data == 'avail') {
                $('.confidence_message_'+instance).addClass('alert alert-danger');
                msg = "Record already available";
            } else {
                $('.confidence_value_'+instance).html(level);
                $('.confidence_message_'+instance).addClass('alert alert-success');
                msg = "Confidence level changed";
            }
            $('.confidence_message_'+instance).html(msg);
        },
    })
});
