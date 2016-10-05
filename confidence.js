// TODO Move from here
$('.noscript').hide();

$('[id*="confidence_" ]').change(function() {
    var instance = $(this).data('instance');
    var level = $(this).val();
    var def = $("#def"+instance).val();
    console.log(def);
    var myurl = M.cfg.wwwroot+'/mod/confidence/confidence.php?level='+level+'&instance='+instance;
    $('.confidence_message_'+instance).fadeIn(100);
    $.ajax({
        url: myurl,
        //data: "confidence"+confidence,
        //data: "name="+$(this).val()+'&value='+idval+'&fromajax=1&sesskey='+M.cfg.sesskey,
        type: 'POST',
        error: function(xhr, status, error) {
            msg = xhr.responseText;
            $('.confidence_message_'+instance).html(msg);
        },
        success: function (data) {
            if (data == 'avail') {
                $('#confidence_'+instance).val(def);
                msg = "Record already available";
            } else {
                $('.confidence_value_'+instance).html(level);
                msg = "Confidence level changed";
            }
            $('.confidence_message_'+instance).html(msg);
        },
    })
    $('.confidence_message_'+instance).fadeOut(2000);
});
