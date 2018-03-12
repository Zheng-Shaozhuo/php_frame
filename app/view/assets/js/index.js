$(function() {
    $('.sort').change(function (){
        $.ajax({
            url: 'msort.html',
            type: 'post',
            async: true,
            data: {
                bid: $(this).attr('data-id'),
                sort: $(this).val()
            },
            dataType: 'json',
            timeout: 3000,
            success: function(data) {

            },
            error: function(data) {

            },
            complete: function() {

            }
        });
    });
});
