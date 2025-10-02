<?php
/**
 * Formats a timestamp into a more readable date and time.
 *
 * @param string $timestamp The timestamp from the database.
 * @return string The formatted date and time.
 */
function formatTimestamp($timestamp) {
    $date = new DateTime($timestamp);
    return $date->format('M d, Y, g:i:s A');
}
?>