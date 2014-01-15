function closeAlert(alert)
{
    $(alert).alert('close');
}

$(document).ready(function(){
    $('input[type=checkbox][name="id[]"]').on('change', function(){
        if ($('input[type=checkbox][name="id[]"]:checked').length > 0) {
            $('.send-selected').removeClass('hide');
        } else {
            $('.send-selected').addClass('hide');
        }
    });

    var timer = 6000;
    $('#push > .alert').each(function(){
        setTimeout(closeAlert, timer, this);
        timer += 1000;
    })
});