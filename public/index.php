<?php

use MaxMind\Db\Reader;

require_once '../config.php';

$reader = new Reader('../GeoLite2-City.mmdb');
$ip = $_SERVER['HTTP_CLIENT_IP']
    ?? ($_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']);
if (filter_var($ip, FILTER_VALIDATE_IP) && $ip != '::1') {
    $city = $reader->get($ip);
    $names = $city['city']['names'];
    $timezone = $city['location']['time_zone'];
} else {
    $timezone = 'Europe/Kiev';
}


date_default_timezone_set($timezone);
?>
<html>
    <head>
        <title>Update Time</title>
    </head>
    <body>

        <div id="date-time"></div>
        <script>
            // Function to format 1 in 01
            const zeroFill = n => {
                return ('0' + n).slice(-2);
            }

            // Creates interval
            const interval = setInterval(() => {
                const now = changeTimeZone(new Date(), "<?=$timezone?>");
                // Format date as in mm/dd/aaaa hh:ii:ss
                const dateTime = zeroFill((now.getMonth() + 1)) + '/' + zeroFill(now.getUTCDate()) + '/' + now.getFullYear() + ' ' + zeroFill(now.getHours()) + ':' + zeroFill(now.getMinutes()) + ':' + zeroFill(now.getSeconds());

                // Display the date and time on the screen using div#date-time
                document.getElementById('date-time').innerHTML = dateTime + ' in ' + "<?=$timezone?>";
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
        </script>
    </body>
</html>
