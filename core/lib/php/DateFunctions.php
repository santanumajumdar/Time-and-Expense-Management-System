<?php

/* * *******************************************************************************
 * TEMS is a Time and Expense Management program developed by
 * Initechs, LLC. Copyright (C) 2009 - 2013 Initechs LLC.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY INITECHS, INITECHS DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact Initechs headquarters at 1841 Piedmont Road, Suite 301,
 * Marietta, GA, USA. or at email address contact@initechs.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display od the "Initechs" logo.
 * If the display of the logo is not reasonably feasible for technical reasons,
 * the Appropriate Legal Notices must display the words "Powered by Initechs".

 * ****************************************************************************** */


/*
  ## converts a given date format to another date format returns date if the checked date given is valid; otherwise returns NULL
  ## $s_date the date in e.g. dd/mm/yyyy
  ## $s_from, $s_to date formats from to i.e. convertdate('13/04/2009','eng','iso','-'); output: 2009-04-13
  ## date formats available
  ## 'eng' = dd/mm/yyyy
  ## 'usa' = mm/dd/yyyy
  ## 'iso' = yyyy/mm/dd
  ## $s_return_delimiter returned delimiter e.g. '-' would return dd-mm-yyyy
  ## Example: echo convertdate('13/04/2009','eng','iso','-');

 */

function convertdate($date, $from_format, $to_format, $s_return_delimiter = '-') {
    // date_default_timezone_set('America/New_York');
    $date = trim($date);
    if (strlen($date) < 6)
        return NULL;

    $s_return_date = '';
    $from_format = strtolower($from_format);
    $to_format = strtolower($to_format);
    $date = str_replace(array('\'', '-', '.', ',', ' '), '/', $date);
    $a_date = explode('/', $date);

    switch ($from_format) {
        case 'dmy': # dd/mm/yyyy
            $day = $a_date[0];
            $month = $a_date[1];
            $year = $a_date[2];
            break;
        case 'mdy':  # mm/dd/yyyy
            $month = $a_date[0];
            $day = $a_date[1];
            $year = $a_date[2];
            break;
        case 'ymd': # yyyy/mm/dd
            $year = $a_date[0];
            $month = $a_date[1];
            $day = $a_date[2];
            break;
        default: # error message
            user_error('function convertdate(string $s_date, string $s_from, string $s_to, string $s_return_delimiter) $s_from not a valid type of \'dmy\', \'mdy\' or \'ymd\'');
            return NULL;
    }

    # substitution fixes of valid alternative human input e.g. 1/12/08
    if (strlen($day) == 1) {
        $day = '0' . $day;
    } # day -trailing zero missing
    if (strlen($month) == 1) {
        $month = '0' . $month;
    } # month -trailing zero missing
    if (strlen($year) == 3) {
        $year = substr(date('Y'), 0, strlen(date('Y')) - 3) . $year;
    } # year -millennium missing
    if (strlen($year) == 2) {
        $year = substr(date('Y'), 0, strlen(date('Y')) - 2) . $year;
    } # year -century missing
    if (strlen($year) == 1) {
        $year = substr(date('Y'), 0, strlen(date('Y')) - 1) . $year;
    } # year -decade missing

    switch ($to_format) {
        case 'dmy': # dd/mm/yyyy
            $s_return_date = $day . $s_return_delimiter . $month . $s_return_delimiter . $year;
            break;
        case 'mdy':  # mm/dd/yyyy
            $s_return_date = $month . $s_return_delimiter . $day . $s_return_delimiter . $year;
            break;
        case "ymd": # yyyy/mm/dd
            $s_return_date = $year . $s_return_delimiter . $month . $s_return_delimiter . $day;
            break;
        default: # error message
            user_error('function convertdate(string $s_date, string $s_from, string $s_to, string $s_return_delimiter) $s_to not a valid type of \'eng\', \'usa\' or \'iso\'');
            return NULL;
    }

    # if it's an invalid calendar date e.g. 40/02/2009 or rt/we/garbage
    if (!is_numeric($month) || !is_numeric($day) || !is_numeric($year)
            || !checkdate($month, $day, $year))
        return NULL;

    return $s_return_date;
}

function cvtDateIso2Dsp($date, $dateFormat) {
    //	date_default_timezone_set('America/New_York');
    if ($date == NULL)
        return NULL;

    $isoDate = substr($date, 0, 10);
    $datearr = explode('-', $isoDate);
    if (sizeof($datearr) <> 3)
        return $isoDate;

    list($y, $m, $d) = $datearr;
    if (strtolower($dateFormat) == 'mdy')
        return "$m/$d/$y";
    elseif (strtolower($dateFormat) == 'dmy')
        return "$d/$m/$y";
    elseif (strtolower($dateFormat) == 'ymd')
        return "$y/$m/$d";
}

function cvtDateDsp2Iso($date, $dateFormat) {
    //	date_default_timezone_set('America/New_York');
    if ($date == NULL)
        return NULL;

    $datearr = explode('/', $date);
    if (sizeof($datearr) <> 3)
        return '*error';

    if (strtolower($dateFormat) == 'mdy') {
        list($m, $d, $y) = $datearr;
    } elseif (strtolower($dateFormat) == 'dmy') {
        list($d, $m, $y) = $datearr;
    } elseif (strtolower($dateFormat) == 'ymd') {
        list($y, $m, $d) = $datearr;
    }
    if (strlen(trim($y)) == 2)
        $y = '20' . trim($y);
    return "$y-$m-$d";
}

function cvtTime2Dsp($date) {
    if ($date == NULL)
        return NULL;

    $time = substr($date, 11, 8);
    $timearr = explode(':', $time);
    if (sizeof($timearr) <> 3)
        return $time;
    list($h, $m, $s) = $timearr;
    if ($h > 12) {
        $h -= 12;
        $ampm = 'PM';
    }
    else
        $ampm = 'AM';

    return " at $h:$m:$s $ampm";
}

function isValidDate($date, $dateFormat) {
    $dateFormat = strtolower($dateFormat);

    if ($dateFormat == 'ymd')
        $datearr = explode('-', $date);
    else
        $datearr = explode('/', $date);

    if (count($datearr) != 3)
        return FALSE;

    if ($dateFormat == 'ymd')
        list($y, $m, $d) = $datearr;
    elseif ($dateFormat == 'dmy')
        list($d, $m, $y) = $datearr;
    elseif ($dateFormat == 'mdy')
        list($m, $d, $y) = $datearr;
    else
        return FALSE;

    if ((!is_numeric($m))
            or (!is_numeric($d))
            or (!is_numeric($y)))
        return FALSE;

    //	date_default_timezone_set('America/New_York');

    /* checkdate - check whether the date is valid.
     * strtotime - Parse about any English textual datetime description into a Unix timestamp.
     * Thus, it restricts any input before 1901 and after 2038,
     * i.e., it invalidate outrange dates like 01-01-2500.
     * preg_match - match the pattern */

    if (!(checkdate($m, $d, $y) && strtotime("$y-$m-$d")
            && preg_match('#\b\d{2}[/-]\d{2}[/-]\d{4}\b#', "$d-$m-$y")))
        return FALSE;

    return TRUE;
}

function getWeekEndDate($wedate='', $weekendday='6') {
    if ($wedate == '')
        $wedate = date("Ymd");
    $pd = date_parse($wedate);
    $ed = mktime(0, 0, 0, $pd['month'], $pd['day'], $pd['year']);
    $diff = ($weekendday - date("w", $ed));
    If ($diff >= 0) {
        $pd['day'] += ( $weekendday - date("w", $ed));
    } else {
        $pd['day'] += ( $weekendday - date("w", $ed)) + 7;
    }
    $ed = mktime(0, 0, 0, $pd['month'], $pd['day'], $pd['year']);
    return date("Y-m-d", $ed);
}

function validWeekendDate($wedate, $weekendday) {
    $dateFormat = getUserDateFormat();
    $wedate = convertdate($wedate, $dateFormat, 'ymd');

    return (validWeekendDateIso($wedate, $weekendday));
}

function validWeekendDateIso($wedate, $weekendday) {
    $pd = date_parse($wedate);
    $ed = mktime(0, 0, 0, $pd['month'], $pd['day'], $pd['year']);
    $w = date("w", $ed);
    if ($w <> $weekendday)
        return FALSE;

    return TRUE;
}

function Date2Day($wedate) {
    $pd = date_parse($wedate);
    $ed = mktime(0, 0, 0, $pd['month'], $pd['day'], $pd['year']);
    $day = date("l", $ed);
    return $day;
}

function tesAddDate($date, $days=0) {
    if (strtolower($date) == 'today')
        $date = date("Ymd");
    $pd = date_parse($date);
    $return_date = mktime(0, 0, 0, $pd['month'], $pd['day'] + $days, $pd['year']);
    $return_date = date('Y-m-d', $return_date);
    return $return_date;
}

function getBiMonthlyDate($wedate = '', $CurrentPrevNext) {
    if ($wedate == '')
        $wedate = date("Ymd");
    $pd = date_parse($wedate);

    switch ($CurrentPrevNext) {
        case 'Prev':
            If ($pd['day'] < 15)
                $ed = date("Y-m-d", mktime(0, 0, 0, $pd['month'] - 1, 15, $pd['year']));
            else
                $ed = date("Y-m-t", mktime(0, 0, 0, $pd['month'] - 1, $pd['day'], $pd['year']));

        case 'Next':
            If ($pd['day'] < 15)
                $ed = date("Y-m-d", mktime(0, 0, 0, $pd['month'] + 1, 15, $pd['year']));
            else
                $ed = date("Y-m-t", mktime(0, 0, 0, $pd['month'] + 1, $pd['day'], $pd['year']));

        default:
            If ($pd['day'] < 15)
                $ed = date("Y-m-d", mktime(0, 0, 0, $pd['month'], 15, $pd['year']));
            else
                $ed = date("Y-m-t", mktime(0, 0, 0, $pd['month'], $pd['day'], $pd['year']));
    }

    return $ed;
}

function getMonthEnd($wedate= '', $CurrentPrevNext) {
    if ($wedate == '')
        $wedate = date("Ymd");
    $pd = date_parse($wedate);
    //Get  month end date
    switch ($CurrentPrevNext) {
        case 'Prev':
            $ed = date("Y-m-t", mktime(0, 0, 0, $pd['month'] - 1, $pd['day'], $pd['year']));
        case 'Next':
            $ed = date("Y-m-t", mktime(0, 0, 0, $pd['month'] + 1, $pd['day'], $pd['year']));
        default:
            $ed = date("Y-m-t", mktime(0, 0, 0, $pd['month'], $pd['day'], $pd['year']));
    }

    return $ed;
}

?>