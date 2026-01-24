<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Theme UFPel lib functions.
 *
 * @package    theme_ufpel
 * @copyright  2025 Universidade Federal de Pelotas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the main SCSS content for the theme.
 *
 * @param theme_config $theme The theme config object.
 * @return string The SCSS content.
 */
function theme_ufpel_get_main_scss_content($theme): string {
    global $CFG;

    // Load default preset from theme directory.
    $scss = file_get_contents($CFG->dirroot . '/theme/ufpel/scss/preset/default.scss');

    return $scss;
}

/**
 * Get pre-SCSS variables.
 *
 * This is executed before the main SCSS compilation.
 *
 * @param theme_config $theme The theme config object.
 * @return string The pre-SCSS variables.
 */
function theme_ufpel_get_pre_scss($theme): string {
    // No pre-SCSS variables needed for now.
    return '';
}

/**
 * Get extra SCSS to append.
 *
 * This is executed after the main SCSS compilation.
 *
 * @param theme_config $theme The theme config object.
 * @return string The extra SCSS.
 */
function theme_ufpel_get_extra_scss($theme): string {
    // No extra SCSS needed for now.
    return '';
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course Course object.
 * @param stdClass $cm Course module object.
 * @param context $context Context object.
 * @param string $filearea File area identifier.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether to force download.
 * @param array $options Additional options affecting the file serving.
 * @return bool False if file not found, does not return if successful.
 */
function theme_ufpel_pluginfile(
    $course,
    $cm,
    $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options = []
): bool {
    // Serve static files from pix directory.
    if ($filearea === 'pix') {
        send_file_not_found();
    }

    // If we reach here, file was not found.
    send_file_not_found();
}