<?php

/**
 * Unit test bootstrap
 *
 * @author Michiel Rook <michiel@touchdownconsulting.nl>
 * @package Gems
 */

date_default_timezone_set('Europe/Amsterdam');

// Set up autoload.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';
}
