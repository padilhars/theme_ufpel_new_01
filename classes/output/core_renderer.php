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
 * Core renderer for theme_ufpel.
 *
 * @package    theme_ufpel
 * @copyright  2025 Universidade Federal de Pelotas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_ufpel\output;

use moodle_url;
use context_course;
use completion_info;
use stdClass;

/**
 * Renderers to align Moodle's HTML with theme_ufpel.
 *
 * @copyright  2025 Universidade Federal de Pelotas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Renders the custom course header with background image.
     *
     * @return string HTML to output.
     */
    public function course_header(): string {
        global $COURSE, $PAGE, $USER;

        // Only render custom header in course context.
        if ($PAGE->context->contextlevel !== CONTEXT_COURSE || $COURSE->id === SITEID) {
            return parent::course_header();
        }

        // Get course context.
        $context = context_course::instance($COURSE->id);
        
        // Parse course name into components.
        $courseinfo = $this->parse_course_name($COURSE->fullname);
        
        // Build the custom header data.
        $data = [
            'courseimage' => $this->get_course_image_url($COURSE)->out(false),
            'courseid' => $COURSE->id,
            'coursestatus' => $this->get_course_status($COURSE),
            'semester' => $courseinfo['semester'],
            'coursecode' => $courseinfo['code'],
            'courseclass' => $courseinfo['class'],
            'coursetitle' => $courseinfo['title'],
            'teachers' => $this->get_course_teachers($COURSE->id, $context),
            'studentcount' => $this->get_student_count($COURSE->id, $context),
            'hasprogress' => $this->is_student($context, $USER->id),
        ];
        
        // Add progress data if user is a student.
        if ($data['hasprogress']) {
            $data['progress'] = $this->get_course_progress($COURSE, $USER->id);
        }

        return $this->render_from_template('theme_ufpel/course_header', $data);
    }

    /**
     * Parse course name into structured components.
     *
     * Expected format: "SEMESTER - CODE - CLASS - TITLE"
     * Example: "2025/2 - 11260030 - M2 - LABORATÓRIO DE EDUCAÇÃO MATEMÁTICA II"
     *
     * @param string $fullname Full course name.
     * @return array Associative array with semester, code, class, and title.
     */
    protected function parse_course_name(string $fullname): array {
        // Default values.
        $result = [
            'semester' => '',
            'code' => '',
            'class' => '',
            'title' => $fullname, // Fallback to full name if parsing fails.
        ];

        // Split by hyphen and trim each part.
        $parts = array_map('trim', explode('-', $fullname));

        // Check if we have at least 4 parts (semester - code - class - title).
        if (count($parts) >= 4) {
            $result['semester'] = $parts[0];
            $result['code'] = $parts[1];
            $result['class'] = $parts[2];
            // Join remaining parts in case title contains hyphens.
            $result['title'] = implode(' - ', array_slice($parts, 3));
        } else if (count($parts) === 3) {
            // If only 3 parts, assume no class identifier.
            $result['semester'] = $parts[0];
            $result['code'] = $parts[1];
            $result['title'] = $parts[2];
        } else if (count($parts) === 2) {
            // If only 2 parts, assume semester and title.
            $result['semester'] = $parts[0];
            $result['title'] = $parts[1];
        }

        return $result;
    }

    /**
     * Determine course status based on current date and course dates.
     *
     * @param stdClass $course Course object.
     * @return array Status information with status, label, badgeclass, and tooltip.
     */
    protected function get_course_status(stdClass $course): array {
        $now = time();
        
        // Check if course has started.
        $hasStarted = empty($course->startdate) || $course->startdate <= $now;
        
        // Check if course has ended.
        $hasEnded = !empty($course->enddate) && $course->enddate < $now;

        // Build tooltip with dates.
        $tooltip = $this->build_course_dates_tooltip($course->startdate, $course->enddate);

        if ($hasEnded) {
            return [
                'status' => 'finished',
                'label' => get_string('coursestatus_finished', 'theme_ufpel'),
                'badgeclass' => 'badge-danger',
                'tooltip' => $tooltip,
            ];
        } else if ($hasStarted) {
            return [
                'status' => 'ongoing',
                'label' => get_string('coursestatus_ongoing', 'theme_ufpel'),
                'badgeclass' => 'badge-success',
                'tooltip' => $tooltip,
            ];
        } else {
            return [
                'status' => 'upcoming',
                'label' => get_string('coursestatus_upcoming', 'theme_ufpel'),
                'badgeclass' => 'badge-warning',
                'tooltip' => $tooltip,
            ];
        }
    }

    /**
     * Build tooltip text with course start and end dates.
     *
     * @param int $startdate Course start date timestamp.
     * @param int $enddate Course end date timestamp.
     * @return string Formatted tooltip text.
     */
    protected function build_course_dates_tooltip(int $startdate, int $enddate): string {
        $parts = [];

        if (!empty($startdate)) {
            $parts[] = get_string('coursestart', 'theme_ufpel') . ': ' . 
                      userdate($startdate, get_string('strftimedateshort', 'core_langconfig'));
        }

        if (!empty($enddate)) {
            $parts[] = get_string('courseend', 'theme_ufpel') . ': ' . 
                      userdate($enddate, get_string('strftimedateshort', 'core_langconfig'));
        }

        return !empty($parts) ? implode(' - ', $parts) : '';
    }

    /**
     * Get the course image URL or a default placeholder.
     *
     * @param stdClass $course Course object.
     * @return moodle_url Course image URL.
     */
    protected function get_course_image_url(stdClass $course): moodle_url {
        // Get course context.
        $coursecontext = context_course::instance($course->id);
        
        // Get course image from file storage.
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $coursecontext->id,
            'course',
            'overviewfiles',
            0,
            'filename',
            false
        );

        // Find first valid image file.
        foreach ($files as $file) {
            if ($file->is_valid_image()) {
                return moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    null,
                    $file->get_filepath(),
                    $file->get_filename()
                );
            }
        }

        // Return default placeholder image.
        return new moodle_url('/theme/ufpel/pix/default_course_image.jpg');
    }

    /**
     * Get course teachers with their profile information.
     *
     * @param int $courseid Course ID.
     * @param context_course $context Course context.
     * @return array Array of teacher data.
     */
    protected function get_course_teachers(int $courseid, context_course $context): array {
        global $DB;

        $teachers = [];
        
        // Get enrolled users with teacher roles.
        $teacheroles = ['editingteacher', 'teacher'];
        
        foreach ($teacheroles as $roleshortname) {
            $role = $DB->get_record('role', ['shortname' => $roleshortname]);
            if (!$role) {
                continue;
            }
            
            $enrolledteachers = get_role_users(
                $role->id,
                $context,
                false,
                'u.id, u.firstname, u.lastname, u.email, u.picture, u.imagealt, 
                 u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename',
                'u.lastname ASC, u.firstname ASC',
                true,
                '',
                '',
                3 // Limit to 3 teachers.
            );
            
            foreach ($enrolledteachers as $teacher) {
                if (!isset($teachers[$teacher->id])) {
                    $teachers[$teacher->id] = [
                        'id' => $teacher->id,
                        'fullname' => fullname($teacher),
                        'profileurl' => (new moodle_url('/user/profile.php', ['id' => $teacher->id]))->out(false),
                        'avatar' => $this->user_picture($teacher, ['size' => 35, 'link' => false]),
                    ];
                }
            }
            
            // Stop if we already have teachers.
            if (!empty($teachers)) {
                break;
            }
        }

        return array_values($teachers);
    }

    /**
     * Get the count of enrolled students in the course.
     *
     * @param int $courseid Course ID.
     * @param context_course $context Course context.
     * @return int Number of students.
     */
    protected function get_student_count(int $courseid, context_course $context): int {
        global $DB;

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        if (!$studentrole) {
            return 0;
        }

        $students = get_role_users(
            $studentrole->id,
            $context,
            false,
            'u.id',
            'u.id',
            true
        );

        return count($students);
    }

    /**
     * Format course date for display.
     *
     * @param int $timestamp Unix timestamp.
     * @return string Formatted date or empty string.
     */
    protected function format_course_date(int $timestamp): string {
        if (empty($timestamp)) {
            return '';
        }

        return userdate($timestamp, get_string('strftimedateshort', 'core_langconfig'));
    }

    /**
     * Check if user is a student in the course.
     *
     * @param context_course $context Course context.
     * @param int $userid User ID.
     * @return bool True if user is a student.
     */
    protected function is_student(context_course $context, int $userid): bool {
        global $DB;

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        if (!$studentrole) {
            return false;
        }

        return user_has_role_assignment($userid, $studentrole->id, $context->id);
    }

    /**
     * Get course progress for a student.
     *
     * @param stdClass $course Course object.
     * @param int $userid User ID.
     * @return array Progress data with enabled, completed, total, and percentage.
     */
    protected function get_course_progress(stdClass $course, int $userid): array {
        $completion = new completion_info($course);

        if (!$completion->is_enabled()) {
            return [
                'enabled' => false,
                'completed' => 0,
                'total' => 0,
                'percentage' => 0,
            ];
        }

        // Get all activities in the course.
        $modinfo = get_fast_modinfo($course, $userid);
        
        $total = 0;
        $completed = 0;

        foreach ($modinfo->get_cms() as $cm) {
            // Skip if not tracked.
            if ($cm->completion === COMPLETION_TRACKING_NONE) {
                continue;
            }

            $total++;

            $cmcompletion = $completion->get_data($cm, true, $userid);
            if (
                $cmcompletion->completionstate === COMPLETION_COMPLETE ||
                $cmcompletion->completionstate === COMPLETION_COMPLETE_PASS
            ) {
                $completed++;
            }
        }

        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'enabled' => true,
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    /**
     * Override full_header to include custom course header.
     *
     * @return string HTML to output.
     */
    public function full_header(): string {
        global $COURSE, $PAGE;

        $html = parent::full_header();

        // Add custom course header for course pages.
        if ($PAGE->context->contextlevel === CONTEXT_COURSE && $COURSE->id !== SITEID) {
            $html = $this->course_header() . $html;
        }

        return $html;
    }
}