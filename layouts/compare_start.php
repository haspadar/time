<?php
use WhatTime\WhatTime;

require_once '../config.php';

$title = 'Title';
$description = 'description';

require_once 'header.inc'; ?>

<tr>
        <td class="content" align="center">
            <table class="tbl_content">
                <tbody>
                <tr>
                    <td>
                        <h1 align="left">Time Converter: What is the difference in time</h1>
                        <table class="tbl_conv">
                            <tbody>
                            <tr>
                                <td>Between:</td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="text" placeholder="Input city or place..." class="location compare compare-first">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="tbl_conv">
                            <tbody>
                            <tr>
                                <td>And:</td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="text" placeholder="Input city or place..." class="location compare compare-second">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p align="left">&nbsp;</p>
                </tr>
                </tbody>
            </table>
        </td>
    <tr>

<?php require_once 'footer.inc';
