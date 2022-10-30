
const interval = setInterval(() => {
    $('.time').html(generateTime($('#timezone').val()));
    $('.date').html(generateDate($('#timezone').val()) + ' in ' + $('#description').val());
    updateLocationsTime();
}, 1000);

function updateLocationsTime() {
    $('.location-time').each(function () {
        $(this).html(generateTime($(this).data('timezone'), true))
    });
}

// Function to format 1 in 01
const zeroFill = n => {
    return ('0' + n).slice(-2);
}

function generateTime(timezone, ignoreSeconds) {
    const now = changeTimeZone(new Date(), timezone);
    // Format date as in mm/dd/aaaa hh:ii:ss
    return zeroFill(now.getHours()) + ':' + zeroFill(now.getMinutes()) + (ignoreSeconds ? '' : ':' + zeroFill(now.getSeconds()));
}

function generateDate(timezone) {
    const now = changeTimeZone(new Date(), timezone);

    return zeroFill(now.getUTCDate()) + '.' + zeroFill((now.getMonth() + 1)) + '.' + now.getFullYear();
}

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
    html: true,
    source: function(request, response) {
        $.ajax({
            url: "/locations.php",
            type: 'post',
            dataType: "json",
            data: {
                search: request.term
            },
            html: true,
            success: function(data) {
                // response(data);
                response($.map(data, function (item)
                {
                    return {
                        label: item.label + '<span class="location-time" data-timezone="' + item.timezone + '"></span>',
                        value: item.value,
                        url: item.url
                    }
                }));
                updateLocationsTime();
            }
        });
    },
    select: function (event, ui) {
        $('.location').val(ui.item.label.split('<span')[0]); // display the selected text
        document.location = '/' + ui.item.url;
        // $('#selectedpost_id').val(ui.item.value); // save selected id to input
        return false;
    }
});