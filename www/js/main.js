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
    });

    var current_form = null;
    $('form.delete-task').on('submit', function(){
        if (current_form !== null) {
            return true;
        }

        current_form = this;

        if ($(this).find('input[type=submit]').hasClass('task-grouped')) {
            $('#delete-modal .modal-alert').html('<b>Attention, il s\'agit d\'une tâche groupée, l\'ensemble des tâches liées seront également supprimées</b>');
        }

        $('#delete-modal').modal('show');

        $('#delete-modal').on('hidden.bs.modal', function(e){
            current_form = null;
            $('#delete-modal .modal-alert').html('');
        });


        return false;
    });

    $('#delete-modal button.btn-danger').on('click', function(){
        current_form.submit();
    });

    $('form[method=put], form[method=delete]').on('submit', function(){
        var form = $(this);

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            dataType: 'html',
            statusCode: {
                200: function(data, textStatus, jqXHR) {
                    console.log(data);
                },
                301: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                },
                302: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                },
                404: function(jqXHR) {
                    $('html').html(jqXHR.responseText);
                },
                500: function(jqXHR) {
                    $('html').html(jqXHR.responseText);
                }
            }
        });

        return false;
    });
});