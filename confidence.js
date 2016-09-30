// TODO Move from here
$('.noscript').hide();

$('[id*="confidence_" ]').change(function() {
    var instance = $(this).data('instance');
    var level = $(this).val();
    var myurl = M.cfg.wwwroot+'/mod/confidence/data.php?level='+level+'&instance='+instance;
    $('.confidence_message_'+instance).fadeIn(100);
    $.ajax({
        url: myurl,
        //data: "confidence"+confidence,
        //data: "name="+$(this).val()+'&value='+idval+'&fromajax=1&sesskey='+M.cfg.sesskey,
        type: 'POST',
        success: function (success) {
            $('.confidence_message_'+instance).html('<p>Confidence level Changed!!!</p>')
        },
        error: function(response, status, xhr) {
            msg = 'Sorry, there was an error: ';
            $('.confidence_message_'+instance).html( msg + xhr.statusText );
        }
    });
    $('.confidence_message_'+instance).fadeOut(5000);
});
