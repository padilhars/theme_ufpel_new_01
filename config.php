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
 * Theme UFPel config file.
 *
 * @package    theme_ufpel
 * @copyright  2025 Universidade Federal de Pelotas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Theme name.
$THEME->name = 'ufpel';

// Theme parent - inherits all settings from Boost.
$THEME->parents = ['boost'];

// Theme sheets - empty as we use SCSS compilation.
$THEME->sheets = [];

// Editor sheets - empty, using parent's editor sheets.
$THEME->editor_sheets = [];

// SCSS function - returns compiled SCSS content.
$THEME->scss = function($theme) {
    return theme_ufpel_get_main_scss_content($theme);
};

// Pre-SCSS callback for variables (executed before main SCSS).
$THEME->prescsscallback = 'theme_ufpel_get_pre_scss';

// Extra SCSS callback (executed after main SCSS).
$THEME->extrascsscallback = 'theme_ufpel_get_extra_scss';

// Renderer factory - allows custom renderers.
$THEME->rendererfactory = 'theme_overridden_renderer_factory';

// Icon system - using Font Awesome.
$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;

// Enable course index (Moodle 4.0+).
$THEME->usescourseindex = true;

// Enable activity navigation.
$THEME->activityheaderconfig = [
    'notitle' => false,
];

// Enable editing switch in navigation.
$THEME->haseditswitch = true;

// Block positioning - use flat navigation.
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;

// CSS optimization support.
$THEME->supportscssoptimisation = false;

// Enable course AJAX.
$THEME->enablecourseajax = true;

// Require jQuery - not needed for Moodle 5.1+.
$THEME->yuicssmodules = [];

// Remove deprecated layouts - inherit all from Boost.
$THEME->layouts = [];