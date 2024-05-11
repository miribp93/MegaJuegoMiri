$(document).ready(function() {
    $('#botonpista').click(function(event) {
        event.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: 'juego.php',
            dataType: 'json',
            data: {
                botonpista: true
            },
            success: function(response) {
                $('#pista').text(response.letra);
                
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
});





