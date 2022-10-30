
// Function to format 1 in 01
const zeroFill = n => {
    return ('0' + n).slice(-2);
}

// Creates interval
const interval = setInterval(() => {
    const now = changeTimeZone(new Date(), $('#timezone').val());
    // Format date as in mm/dd/aaaa hh:ii:ss
    const time = zeroFill(now.getHours()) + ':' + zeroFill(now.getMinutes()) + ':' + zeroFill(now.getSeconds());
    const date = zeroFill(now.getUTCDate()) + '.' + zeroFill((now.getMonth() + 1)) + '.' + now.getFullYear();

    // Display the date and time on the screen using div#date-time
    $('.time').html(time);
    $('.date').html(date + ' in ' + $('#description').val());
}, 1000);

function changeTimeZone(date, timeZone) {
    if (typeof date === 'string') {
        return new Date(
            new Date(date).toLocaleString('en-US', {
                timeZone,
            }),
        );
    }

    return new Date(
        date.toLocaleString('en-US', {
            timeZone,
        }),
    );
}

$( ".location" ).autocomplete({
    source: function(request, response) {
        $.ajax({
            url: "/locations.php",
            type: 'post',
            dataType: "json",
            data: {
                search: request.term
            },
            success: function( data ) {
                response(data);
            }
        });
    },
    select: function (event, ui) {
        $('.location').val(ui.item.label); // display the selected text
        document.location = '/' + ui.item.label;
        // $('#selectedpost_id').val(ui.item.value); // save selected id to input
        return false;
    }
});