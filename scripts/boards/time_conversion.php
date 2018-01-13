<?php

function convert_to_user_date($date, $format = 'n/j/Y g:ia', $userTimeZone = 'Australia/Melbourne', $serverTimeZone = 'UTC')
{
    try {
        $dateTime = new DateTime ($date, new DateTimeZone($serverTimeZone));
        $dateTime->setTimezone(new DateTimeZone($userTimeZone));
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '';
    }
}

function convert_to_server_date($date, $format = 'n/j/Y g:i A', $userTimeZone = 'Australia/Melbourne', $serverTimeZone = 'UTC')
{
    try {
        $dateTime = new DateTime ($date, new DateTimeZone($userTimeZone));
        $dateTime->setTimezone(new DateTimeZone($serverTimeZone));
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '';
    }
}

//example usage
//$serverDate = $userDate = '2017-11-02 17:37:22';
//echo convert_to_user_date($serverDate, 'n/j/Y g:i A', 'Australia/Brisbane') . "\n";
//echo convert_to_server_date($userDate) . "\n";


?>