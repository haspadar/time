<html xmlns="http://www.w3.org/1999/html" lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/css/jquery-ui.css">
    <link rel="stylesheet" href="/css/whattime.css?t=<?=time()?>">
    <link rel="icon" type="image/png" href="/img/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ubuntu">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.2/dist/leaflet.css" integrity="sha256-sA+zWATbFveLLNqWO2gtiw3HL/lh1giY/Inf1BJ0z14=" crossorigin=""/>
    <link rel="canonical" href="https://<?=$_SERVER['HTTP_HOST']?><?=$_SERVER['REQUEST_URI']?>">
    <title><?=$title?></title>
    <meta name="description" content="<?=$description?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php if (isset($time)) :?>
    <input type="hidden" id="timezone" value="<?=$time->getTimezone()?>">
    <input type="hidden" id="description" value="<?=$time->getTitle()?>">
<?php endif;?>

<table class="base">
    <tbody>
    <tr class="header">
        <td class="tdheader fifty" align="center"><table class="tbl_header">
                <tbody>
                <tr>
                    <td class="td_logo"><a href="/"><img src="/img/logo.png" alt="Time in your city" class="mylogo"/></a></td>
                    <td>
                        <input type="text" class="location" value="<?=isset($time) ? $time->getTitle() : ''?>" placeholder="Search for city or place...">
                    </td>
                </tr>
                </tbody>
            </table></td>
    </tr>