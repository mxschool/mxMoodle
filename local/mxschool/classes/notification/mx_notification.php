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
 * Generic email notification for all of the emails sent by Middlesex School's Dorm and Student functions plugin.
 *
 * @package    local_mxschool
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2019, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mxschool\local;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../../locallib.php');

use \local_mxschool\event\email_sent;

class notification {

    /** @var string $emailclass The class of the email as specified in the local_mxschool_notification database table.*/
    private $emailclass;
    /** @var string $subject The subject line of the email.*/
    private $subject;
    /** @var string $body The body text of the email.*/
    private $body;
    /** @var array $data The data for the email as [placeholder => value].*/
    protected $data;
    /** @var array $recipients The recipients for the email with string properties email and salutationname.*/
    protected $recipients;

    /**
     * @param string $emailclass The class of the email as specified in the local_mxschool_notification database table.
     * @throws coding_exception If the email class does not exist.
     */
    public function __construct($emailclass) {
        global $DB;
        $record = $DB->get_record('local_mxschool_notification', array('class' => $emailclass));
        if (!$record) {
            throw new \coding_exception("Invalid email class: {$emailclass}.");
        }
        $this->emailclass = $emailclass;
        $this->subject = $record->subject;
        $this->body = $record->body_html;
        $this->data = array();
        $this->recipients = array();
    }

    /**
     * Generates the subject line for the email with replacements.
     *
     * @param array $recipientdata Additional substitutions as [placeholder => value].
     * @return string The processed subject line.
     */
    final public function get_subject($recipientdata) {
        return self::replace_placeholders($this->subject, array_merge($this->data, $recipientdata));
    }

    /**
     * Generates the body text for the email with replacements.
     *
     * @param array $recipientdata Additional substitutions as [placeholder => value].
     * @return string The processed body text.
     */
    final public function get_body($recipientdata) {
        return self::replace_placeholders($this->body, array_merge($this->data, $recipientdata));
    }

    /**
     * Sends the notification emails to all of the specified recipients.
     *
     * @return bool A value of true if all emails send successfully, false otherwise.
     * @throws coding_exception If any recipient has a non-valid email or
     *                          if any recipient has no non-empty salutationname, alternatename, or firstname field.
     */
    final public function send() {
        $supportuser = \core_user::get_support_user();
        $result = true;
        foreach ($this->recipients as $recipient) {
            if (empty($recipient->email)) {
                throw new \coding_exception('Recipient has no email address.');
            }
            if (empty($recipient->salutationname) && empty($recipient->alternatename) && empty($recipient->firstname)) {
                throw new \coding_exception('Recipient has no valid option for salutation.');
            }
            $redirect = get_config('local_mxschool', 'email_redirect');
            if (!empty($redirect)) {
                $recipient->email = $redirect;
            }
            $recipientdata = array('email' => $recipient->email, 'addressee' =>
                !empty($recipient->salutationname) ? $recipient->salutationname : (
                    !empty($recipient->alternatename) ? $recipient->alternatename : $recipient->firstname
                )
            );
            $subject = $this->get_subject($recipientdata);
            $body = $this->get_body($recipientdata);
            $result &= email_to_user($recipient, $supportuser, $subject, '', $body);
        }
        email_sent::create(array('other' => array('emailclass' => $this->emailclass)))->trigger();
        return $result;
    }

    /**
     * @return array The list of strings which can serve as tags for the notification.
     */
    public function get_tags() {
        return array('email', 'addressee');
    }

    /**
     * Substitutes placeholders with values in an arbitrary string.
     *
     * @param string $string The string with placeholders.
     * @param stdClass|array $replacements The substitutions to make as [placeholder => value].
     * @return string The original string with the substitutions having been made.
     */
    final protected static function replace_placeholders($string, $replacements) {
        $replacements = (array)$replacements;
        foreach ($replacements as $placeholder => $value) {
            $string = str_replace("{{$placeholder}}", $value, $string);
        }
        return $string;
    }

}

// class mx_notifications {
//
//     /**
//      * Sends an email notification based on a specified class.
//      *
//      * @param string $emailclass The class of the email.
//      * @param array $params Parameters for the email.
//      * @return bool True if email send successfully, false otherwise.
//      */
//     public static function send_email($emailclass, $params = array()) {
//         global $DB;
//         $supportuser = core_user::get_support_user();
//         $deans = clone $supportuser;
//         $deans->email = get_config('local_mxschool', 'email_deans');
//         $notification = $DB->get_record('local_mxschool_notification', array('class' => $emailclass));
//         if (!$notification) {
//             return false;
//         }
//         switch($emailclass) {
//             case 'esignout_submitted':
//                 if (!isset($params['id'])) {
//                     return false;
//                 }
//                 $record = $DB->get_record_sql(
//                     "SELECT CONCAT(u.lastname, ', ', u.firstname) AS studentname, u.firstname, u.alternatename, es.type,
//                      es.passengers, CONCAT(du.lastname, ', ', du.firstname) AS driver, du.firstname AS dfirstname,
//                      du.alternatename AS dalternatename, d.destination,  d.departure_time AS departuretime,
//                      CONCAT(a.firstname, ' ', a.lastname) AS approver, es.time_modified AS timesubmitted,
//                      p.may_ride_with AS passengerpermission, p.ride_permission_details AS specificdrivers
//                      FROM {local_mxschool_esignout} es LEFT JOIN {user} u ON es.userid = u.id
//                      LEFT JOIN {local_mxschool_esignout} d ON es.driverid = d.id LEFT JOIN {user} du ON d.userid = du.id
//                      LEFT JOIN {user} a ON es.approverid = a.id LEFT JOIN {local_mxschool_permissions} p ON es.userid = p.userid
//                      WHERE es.id = ?", array($params['id'])
//                 );
//                 $record->studentname .= !empty($record->alternatename) && $record->alternatename !== $record->firstname
//                     ? " ({$record->alternatename})" : '';
//                 $record->salutation = empty($record->alternatename) ? $record->firstname : $record->alternatename;
//                 $record->driver .= !empty($record->dalternatename) && $record->dalternatename !== $record->dfirstname
//                     ? " ({$record->dalternatename})" : '';
//                 if (isset($record->passengers)) {
//                     $passengers = json_decode($record->passengers);
//                     if (!count($passengers)) { // Driver with no passengers.
//                         $record->passengers = get_string('esignout_report_nopassengers', 'local_mxschool');
//                     }
//                     $passengernames = array();
//                     foreach ($passengers as $passenger) {
//                         $passengerrecord = $DB->get_record_sql(
//                             "SELECT CONCAT(lastname, ', ', firstname) AS name, firstname, alternatename FROM {user}
//                              WHERE id = ?", array($passenger)
//                         );
//                         $passengernames[] = $passengerrecord->name.(
//                             !empty($passengerrecord->alternatename) &&
//                             $passengerrecord->alternatename !== $passengerrecord->firstname
//                             ? " ({$passengerrecord->alternatename})" : ''
//                         );
//                     }
//                     $record->passengers = implode('<br>', $passengernames);
//                 } else {
//                     $record->passengers = '';
//                 }
//                 $record->date = date('n/j/y', $record->departuretime);
//                 $record->departuretime = date('g:i A', $record->departuretime);
//                 $record->timesubmitted = date('g:i A', $record->timesubmitted);
//                 $emaildeans = false;
//                 if ($record->type === 'Driver') {
//                     $record->permissionswarning = get_config('local_mxschool', 'esignout_notification_warning_driver');
//                 } else {
//                     if ($record->type !== 'Passenger' && $record->type !== 'Parent') {
//                         $emaildeans = true;
//                     }
//                     switch($record->passengerpermission) {
//                         case 'Any Driver':
//                             $record->permissionswarning = get_config('local_mxschool', 'esignout_notification_warning_any');
//                             break;
//                         case 'Parent Permission':
//                             $record->permissionswarning = get_config('local_mxschool', 'esignout_notification_warning_parent');
//                             break;
//                         case 'Specific Drivers':
//                             $record->permissionswarning = get_config('local_mxschool', 'esignout_notification_warning_specific')
//                                                           ." {$record->specificdrivers}";
//                             $emaildeans = true;
//                             break;
//                         default:
//                             $record->permissionswarning = get_config('local_mxschool', 'esignout_notification_warning_over21');
//                             $emaildeans = true;
//                     }
//                 }
//                 $record->irregular = $emaildeans ? get_config('local_mxschool', 'esignout_notification_warning_irregular') : '';
//
//                 $subject = self::replace_placeholders($notification->subject, $record);
//                 $body = self::replace_placeholders($notification->body_html, $record);
//                 $users = $DB->get_record_sql(
//                     "SELECT es.userid AS student, es.approverid AS approver, d.hohid AS hoh
//                      FROM {local_mxschool_esignout} es LEFT JOIN {user} u ON es.userid = u.id
//                      LEFT JOIN {local_mxschool_student} s ON es.userid = s.userid
//                      LEFT JOIN {local_mxschool_dorm} d ON s.dormid = d.id
//                      WHERE es.id = ?", array($params['id'])
//                 );
//                 $emailto = array(
//                     $DB->get_record('user', array('id' => $users->student)),
//                     $DB->get_record('user', array('id' => $users->approver)),
//                     $DB->get_record('user', array('id' => $users->hoh))
//                 );
//                 if ($emaildeans) {
//                     $emailto[] = $deans;
//                 }
//                 break;
//             case 'advisor_selection_notify_unsubmitted':
//                 $subject = $notification->subject;
//                 $body = $notification->body_html;
//                 $emailto = array($deans);
//                 $list = get_student_without_advisor_form_list();
//                 foreach ($list as $userid => $name) {
//                     $record = $DB->get_record('user', array('id' => $userid));
//                     $record->replacements = array(
//                         'studentname' => "{$record->lastname}, {$record->firstname}" . (
//                             !empty($record->alternatename) && $record->alternatename !== $record->firstname
//                                 ? " ({$record->alternatename})" : ''
//                         ), 'salutation' => empty($record->alternatename) ? $record->firstname : $record->alternatename
//                     );
//                     $emailto[] = $record;
//                 }
//                 break;
//             case 'advisor_selection_notify_results':
//                 $subject = $notification->subject;
//                 $body = $notification->body_html;
//                 $emailto = array($deans);
//                 $list = get_new_student_advisor_pair_list();
//                 foreach ($list as $suserid => $auserid) {
//                     $student = $DB->get_record('user', array('id' => $suserid));
//                     $advisor = $DB->get_record('user', array('id' => $auserid));
//                     $student->replacements = $advisor->replacements = array(
//                         'studentname' => "{$student->lastname}, {$student->firstname}" . (
//                             !empty($student->alternatename) && $student->alternatename !== $student->firstname
//                                 ? " ({$student->alternatename})" : ''
//                         ), 'salutation' => empty($student->alternatename) ? $student->firstname : $student->alternatename,
//                         'advisorname' => "{$advisor->firstname} {$advisor->lastname}"
//                     );
//                     $emailto[] = $student;
//                     $emailto[] = $advisor;
//                 }
//                 break;
//             case 'rooming_notify_unsubmitted':
//                 $subject = $notification->subject;
//                 $body = $notification->body_html;
//                 $emailto = array($deans);
//                 $list = get_student_without_rooming_form_list();
//                 foreach ($list as $userid => $name) {
//                     $record = $DB->get_record('user', array('id' => $userid));
//                     $record->replacements = array(
//                         'studentname' => "{$record->lastname}, {$record->firstname}" . (
//                             !empty($record->alternatename) && $record->alternatename !== $record->firstname
//                                 ? " ({$record->alternatename})" : ''
//                         ), 'salutation' => empty($record->alternatename) ? $record->firstname : $record->alternatename
//                     );
//                     $emailto[] = $record;
//                 }
//                 break;
//             case 'vacation_travel_submitted':
//                 if (!isset($params['id'])) {
//                     return false;
//                 }
//                 $record = $DB->get_record_sql(
//                     "SELECT t.id, u.id AS student, CONCAT(u.lastname, ', ', u.firstname) AS studentname, u.firstname,
//                      u.alternatename, t.destination, t.phone_number AS phonenumber, t.time_modified AS timesubmitted,
//                      d.mx_transportation AS depmxtransportation, d.type AS deptype, ds.name AS depsite, d.details AS depdetails,
//                      d.carrier AS depcarriercompany, d.transportation_number AS depnumber, d.date_time AS depvariable,
//                      d.international AS depinternational, r.mx_transportation AS retmxtransportation, r.type AS rettype,
//                      rs.name AS retsite, r.details AS retdetails, r.carrier AS retcarriercompany,
//                      r.transportation_number AS retnumber, r.date_time AS retvariable, r.international AS retinternational
//                      FROM {local_mxschool_vt_trip} t LEFT JOIN {user} u ON t.userid = u.id
//                      LEFT JOIN {local_mxschool_vt_transport} d ON t.departureid = d.id
//                      LEFT JOIN {local_mxschool_vt_site} ds ON d.siteid = ds.id
//                      LEFT JOIN {local_mxschool_vt_transport} r ON t.returnid = r.id
//                      LEFT JOIN {local_mxschool_vt_site} rs ON r.siteid = rs.id
//                      WHERE t.id = ?", array($params['id'])
//                 );
//                 $record->studentname .= !empty($record->alternatename) && $record->alternatename !== $record->firstname
//                     ? " ({$record->alternatename})" : '';
//                 $record->salutation = empty($record->alternatename) ? $record->firstname : $record->alternatename;
//                 $record->timesubmitted = date('n/j/y g:i A', $record->timesubmitted);
//                 $record->destination = isset($record->destination) ? $record->destination : '-';
//                 $record->phonenumber = isset($record->phonenumber) ? $record->phonenumber : '-';
//                 $record->depmxtransportation = $record->depmxtransportation ? get_string('yes') : get_string('no');
//                 $record->deptype = isset($record->deptype) ? $record->deptype : '-';
//                 $record->depsite = isset($record->depsite) ? $record->depsite : '-';
//                 $record->depdetails = isset($record->depdetails) ? $record->depdetails : '-';
//                 $record->depcarriercompany = isset($record->depcarriercompany) ? $record->depcarriercompany : '-';
//                 $record->depnumber = isset($record->depnumber) ? $record->depnumber : '-';
//                 $record->depinternational = isset($record->depinternational) ? $record->depinternational : '-';
//                 $record->depdatetime = date('n/j/y g:i A', $record->depvariable);
//                 $record->retmxtransportation = $record->retmxtransportation ? get_string('yes') : get_string('no');
//                 $record->rettype = isset($record->rettype) ? $record->rettype : '-';
//                 $record->retsite = isset($record->retsite) ? $record->retsite : '-';
//                 $record->retdetails = isset($record->retdetails) ? $record->retdetails : '-';
//                 $record->retcarriercompany = isset($record->retcarriercompany) ? $record->retcarriercompany : '-';
//                 $record->retnumber = isset($record->retnumber) ? $record->retnumber : '-';
//                 $record->retinternational = isset($record->retinternational) ? $record->retinternational : '-';
//                 $record->retdatetime = date('n/j/y g:i A', $record->retvariable);
//                 $subject = self::replace_placeholders($notification->subject, $record);
//                 $body = self::replace_placeholders($notification->body_html, $record);
//                 $transportationmanager = clone $supportuser;
//                 $transportationmanager->email = get_config('local_mxschool', 'email_transportationmanager');
//                 $emailto = array($DB->get_record('user', array('id' => $record->student)), $transportationmanager);
//                 break;
//             case 'vacation_travel_notify_unsubmitted':
//                 $subject = $notification->subject;
//                 $body = $notification->body_html;
//                 $emailto = array($deans);
//                 $list = get_student_without_vacation_travel_form_list();
//                 foreach ($list as $userid => $name) {
//                     $record = $DB->get_record('user', array('id' => $userid));
//                     $record->replacements = array(
//                         'studentname' => "{$record->lastname}, {$record->firstname}" . (
//                             !empty($record->alternatename) && $record->alternatename !== $record->firstname
//                                 ? " ({$record->alternatename})" : ''
//                         ), 'salutation' => empty($record->alternatename) ? $record->firstname : $record->alternatename
//                     );
//                     $emailto[] = $record;
//                 }
//                 break;
//             case 'peer_tutor_summary':
//                 $time = new DateTime('now', core_date::get_server_timezone_object());
//                 $time->modify('-1 day');
//                 $record = $DB->get_record_sql(
//                     "SELECT COUNT(id) AS total FROM {local_peertutoring_session} WHERE time_modified >= ?",
//                    array($time->getTimestamp())
//                 );
//                 $subject = self::replace_placeholders($notification->subject, $record);
//                 $body = self::replace_placeholders($notification->body_html, $record);
//                 $peertutoradmin = clone $supportuser;
//                 $peertutoradmin->email = get_config('local_peertutoring', 'email_peertutoradmin');
//                 $emailto = array($peertutoradmin);
//                 break;
//             default:
//                 return false;
//         }
//         \local_mxschool\event\email_sent::create(array('other' => array('emailclass' => $emailclass)))->trigger();
//         return self::email_all($emailto, $subject, $body);
//     }
//
//     /**
//      * Emails a list of users.
//      *
//      * @param array $emailto The users to send the email to - a property replacemnents will substitute text for each user.
//      * @param string $subject The subject line of the email.
//      * @param string $body The body html of the email.
//      */
//     private static function email_all($emailto, $subject, $body) {
//         $supportuser = core_user::get_support_user();
//         $result = true;
//         foreach ($emailto as $recipient) {
//             if (!isset($recipient->replacements)) {
//                 $recipient->replacements = array();
//             }
//             $recipient->replacements['email'] = $recipient->email;
//             if (!empty(get_config('local_mxschool', 'email_redirect'))) {
//                 $recipient->email = get_config('local_mxschool', 'email_redirect');
//             }
//             $emailsubject = self::replace_placeholders($subject, $recipient->replacements);
//             $emailbody = self::replace_placeholders($body, $recipient->replacements);
//             $result &= email_to_user($recipient, $supportuser, $emailsubject, '', $emailbody);
//         }
//         return $result;
//     }
//
//     /**
//      * Substitutes placeholders with values in an arbitrary string.
//      *
//      * @param string $string The string with placeholders.
//      * @param stdClass|array $replacements The substitutions to make as [placeholder => value].
//      * @return string The original string with the substitutions having been made.
//      */
//     private static function replace_placeholders($string, $replacements) {
//         $replacements = (array)$replacements;
//         foreach ($replacements as $placeholder => $value) {
//             $string = str_replace("{{$placeholder}}", $value, $string);
//         }
//         return $string;
//     }
//
// }
