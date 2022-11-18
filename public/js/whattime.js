// Function to format 1 in 01
const zeroFill = n => {
    return ('0' + n).slice(-2);
}

updateLocationsTime('.location-time');
const interval = setInterval(() => {
    $('.time').html(generateTime($('#timezone').val()));
    $('.date').html(generateDate($('#timezone').val()));
    updateLocationsTime('.location-time');
    // updateTitle();
}, 1000);

// function updateTitle() {
//     let $title = $('title');
//     let title = $.trim($title.text().split(':')[0]);
//     $title.text(title + ': ' + generateTime($title.data('timezone'), true));
// }

function updateLocationsTime(selector) {
    $(selector).each(function () {
        $(this).html(generateTime($(this).data('timezone'), true))
    });
}

function generateTime(timezone, ignoreSeconds) {
    const now = changeTimeZone(new Date(), timezone);

    return zeroFill(now.getHours()) + ':' + zeroFill(now.getMinutes()) + (ignoreSeconds ? '' : ':' + zeroFill(now.getSeconds()));
}

function generateDate(timezone) {
    const now = changeTimeZone(new Date(), timezone);
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    return dayNames[now.getDay()]
        + ', '
        + zeroFill(now.getUTCDate())
        + ' '
        + monthNames[zeroFill(now.getMonth())]
        + ' '
        + now.getFullYear();
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
                response($.map(data, function (item) {
                    return {
                        label: '<div>' + item.label + '</div><div class="location-time autocomplete-time" data-timezone="' + item.timezone + '"></div>',
                        value: item.value,
                        url: item.url
                    }
                }));
                updateLocationsTime('.autocomplete-time');
            }
        });
    },
    select: function (event, ui) {
        $('.location').val(ui.item.label.split('<div')[0]); // display the selected text
        document.location = '/' + ui.item.url;

        return false;
    }
});



let $map = $('#map');
if ($map && $map.length) {
    let latitude = $map.data('latitude');
    let longitude = $map.data('longitude');
    let accuracy = $map.data('accuracy');
    let map = L.map('map').setView([latitude, longitude], accuracy);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    L.marker([latitude, longitude]).addTo(map);
}