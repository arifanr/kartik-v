<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-helpers
 * @version 1.3.0
 */

namespace kartik\helpers;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Collection of useful helper functions for Yii Applications
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 *
 */
class Enum extends \yii\helpers\Inflector
{
    /* time intervals in seconds */
    public static $intervals = [
        'year' => 31556926,
        'month' => 2629744,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];

    /**
     * Check if a variable is empty or not set.
     *
     * @param reference $var variable to perform the check
     *
     * @return boolean
     */
    public static function isEmpty(&$var)
    {
        return is_array($var) ? empty($var) : (!isset($var) || (strlen($var) == 0));
    }

    /**
     * Check if a value exists in the array. This method is faster
     * in performance than the built in PHP in_array method.
     *
     * @param string $needle the value to search
     * @param array  $haystack the array to scan
     *
     * @return boolean
     */
    public static function inArray($needle, $haystack)
    {
        $flippedHaystack = array_flip($haystack);
        return isset($flippedHaystack[$needle]);
    }

    /**
     * Properize a string for possessive punctuation.
     * e.g.
     *     properize("Chris"); //returns Chris'
     *     properize("David"); //returns David's
     *
     * @param string $string input string
     */
    public static function properize($string)
    {
        $string = preg_replace('/\s+(.*?)\s+/', '*\1*', $string);
        return $string . '\'' . ($string[strlen($string) - 1] != 's' ? 's' : '');
    }

    /**
     * Get time elapsed (Facebook Style)
     *
     * Example Output(s):
     *     10 hours ago
     *
     * @param string  $fromTime start date time
     * @param boolean $human if true returns an approximate human friendly output
     * If set to false will attempt an exact conversion of time intervals.
     * @param string  $toTime end date time (defaults to current system time)
     * @param string  $append the string to append for the converted elapsed time. Defaults to ' ago'.
     *
     * @return string
     */
    public static function timeElapsed($fromTime = null, $human = true, $toTime = null, $append = null)
    {
        static::initI18N();
        $elapsed = '';
        if ($fromTime != null) {
            $fromTime = strtotime($fromTime);
            $toTime = ($toTime == null) ? time() : (int)$toTime;
            $diff = $toTime - $fromTime;
            $intervals = static::$intervals;

            if (empty($append)) {
                $append = ' ' . Yii::t('kvenum', 'ago');
            }
            if ($human) {
                // now we just find the difference
                if ($diff <= 0) {
                    $elapsed = Yii::t('kvenum', 'a moment ago');
                } elseif ($diff < 60) {
                    $elapsed = Yii::t('kvenum', '{n, plural, one{one second} other{# seconds}}',
                            ['n' => $diff]) . $append;
                } elseif ($diff >= 60 && $diff < $intervals['hour']) {
                    $diff = floor($diff / $intervals['minute']);
                    $elapsed = Yii::t('kvenum', '{n, plural, one{one minute} other{# minutes}}',
                            ['n' => $diff]) . $append;
                } elseif ($diff >= $intervals['hour'] && $diff < $intervals['day']) {
                    $diff = floor($diff / $intervals['hour']);
                    $elapsed = Yii::t('kvenum', '{n, plural, one{one hour} other{# hours}}', ['n' => $diff]) . $append;
                } elseif ($diff >= $intervals['day'] && $diff < $intervals['week']) {
                    $diff = floor($diff / $intervals['day']);
                    $elapsed = Yii::t('kvenum', '{n, plural, one{one day} other{# days}}', ['n' => $diff]) . $append;
                } elseif ($diff >= $intervals['week'] && $diff < $intervals['month']) {
                    $diff = floor($diff / $intervals['week']);
                    $elapsed = Yii::t('kvenum', '{n, plural, one{one week} other{# weeks}}', ['n' => $diff]) . $append;
                } elseif ($diff >= $intervals['month'] && $diff < $intervals['year']) {
                    $diff = floor($diff / $intervals['month']);
                    $elapsed = Yii::t('kvenum', '{n, plural, one{one month} other{# months}}',
                            ['n' => $diff]) . $append;
                } elseif ($diff >= $intervals['year']) {
                    $diff = floor($diff / $intervals['year']);
                    $elapsed = Yii::t('kvenum', '{n, plural, one{one year} other{# years}}', ['n' => $diff]) . $append;
                }
            } else {
                $elapsed = static::time2String($diff, $intervals) . $append;
            }
        }
        return $elapsed;
    }

    /**
     * Initialize translations
     */
    public static function initI18N()
    {
        if (!empty(Yii::$app->i18n->translations['kvenum'])) {
            return;
        }
        Yii::setAlias("@kvenum", __DIR__);
        Yii::$app->i18n->translations['kvenum'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => "@kvenum/messages",
            'forceTranslation' => true
        ];
    }

    /**
     * Get elapsed time converted to string
     *
     * Example Output:
     *    1 year 5 months 3 days ago
     *
     * @param integer $timeline elapsed number of seconds
     * @param array   $intervals configuration of time intervals in seconds
     *
     * @return string
     */
    protected static function time2String($timeline, $intervals)
    {
        $output = '';
        foreach ($intervals AS $name => $seconds) {
            $num = floor($timeline / $seconds);
            $timeline -= ($num * $seconds);
            if ($num > 0) {
                $output .= $num . ' ' . $name . (($num > 1) ? 's' : '') . ' ';
            }
        }
        return trim($output);
    }

    /**
     * Get time remaining (Facebook Style)
     *
     * Example Output(s):
     *     10 hours to go
     *
     * @param string  $futureTime future date time
     * @param boolean $human if true returns an approximate human friendly output
     * If set to false will attempt an exact conversion of time intervals.
     * @param string  $currentTime current date time (defaults to current system time)
     * @param string  $append the string to append for the converted elapsed time
     * (default: 'until the deadline')
     *
     * @return string
     */
    public static function timeRemaining(
        $futureTime = null,
        $human = true,
        $currentTime = null,
        $append = ' until the deadline'
    ) {
        $remaining = '';
        if ($futureTime != null) {
            $futureTime = strtotime($futureTime);
            $currentTime = ($currentTime == null) ? time() : (int)$currentTime;
            $diff = $futureTime - $currentTime;
            $intervals = static::$intervals;

            if ($human) {
                // now we just find the difference
                if ($diff <= 0) {
                    $remaining = 'a moment to go';
                } elseif ($diff < 60) {
                    $remaining = $diff == 1 ? $diff . ' second to go' : $diff . ' seconds' . $append;
                } elseif ($diff >= 60 && $diff < $intervals['hour']) {
                    $diff = floor($diff / $intervals['minute']);
                    $remaining = $diff == 1 ? $diff . ' minute to go' : $diff . ' minutes' . $append;
                } elseif ($diff >= $intervals['hour'] && $diff < $intervals['day']) {
                    $diff = floor($diff / $intervals['hour']);
                    $remaining = $diff == 1 ? $diff . ' hour to go' : $diff . ' hours' . $append;
                } elseif ($diff >= $intervals['day'] && $diff < $intervals['week']) {
                    $diff = floor($diff / $intervals['day']);
                    $remaining = $diff == 1 ? $diff . ' day to go' : $diff . ' days' . $append;
                } elseif ($diff >= $intervals['week'] && $diff < $intervals['month']) {
                    $diff = floor($diff / $intervals['week']);
                    $remaining = $diff == 1 ? $diff . ' week to go' : $diff . ' weeks to go';
                } elseif ($diff >= $intervals['month'] && $diff < $intervals['year']) {
                    $diff = floor($diff / $intervals['month']);
                    $remaining = $diff == 1 ? $diff . ' month to go' : $diff . ' months' . $append;
                } elseif ($diff >= $intervals['year']) {
                    $diff = floor($diff / $intervals['year']);
                    $remaining = $diff == 1 ? $diff . ' year to go' : $diff . ' years' . $append;
                }
            } else {
                $remaining = static::time2String($diff, $intervals) . $append;
            }
        }
        return $remaining;
    }

    /**
     * Format and convert "bytes" to its
     * optimal higher metric unit
     *
     * @param double  $bytes number of bytes
     * @param integer $precision the number of decimal places to round off
     *
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Number to words conversion. Returns the number
     * converted as an anglicized string.
     *
     * @param double $num the source number
     *
     * @return string
     */
    public static function numToWords($num)
    {
        $num = (int)$num; // make sure it's an integer

        if ($num < 0) {
            return Yii::t('kvenum', 'minus') . ' ' . static::convertTri(-$num, 0);
        }

        if ($num == 0) {
            return Yii::t('kvenum', 'zero');
        }

        return static::convertTri($num, 0);
    }

    /**
     * Recursive function used in number to words conversion.
     * Converts three digits per pass.
     *
     * @param double $num the source number
     * @param double $tri the three digits converted per pass.
     *
     * @return string
     */
    protected static function convertTri($num, $tri)
    {
        // chunk the number, ...rxyy
        $r = (int)($num / 1000);
        $x = ($num / 100) % 10;
        $y = $num % 100;

        // init the output string
        $str = "";
        $ones = static::ones();
        $tens = static::tens();
        $triplets = static::triplets();

        // do hundreds
        if ($x > 0) {
            $str = $ones[$x] . ' ' . Yii::t('kvenum', 'hundred');
        }

        // do ones and tens
        if ($y < 20) {
            $str .= $ones[$y];
        } else {
            $str .= $tens[(int)($y / 10)] . $ones[$y % 10];
        }

        // add triplet modifier only if there
        // is some output to be modified...
        if ($str != "") {
            $str .= $triplets[$tri];
        }

        // continue recursing?
        if ($r > 0) {
            return static::convertTri($r, $tri + 1) . $str;
        } else {
            return $str;
        }
    }

    /**
     * Generate list of ones
     *
     * @return array
     */
    public static function ones()
    {
        static::initI18N();
        return [
            '',
            ' ' . Yii::t('kvenum', 'one'),
            ' ' . Yii::t('kvenum', 'two'),
            ' ' . Yii::t('kvenum', 'three'),
            ' ' . Yii::t('kvenum', 'four'),
            ' ' . Yii::t('kvenum', 'five'),
            ' ' . Yii::t('kvenum', 'six'),
            ' ' . Yii::t('kvenum', 'seven'),
            ' ' . Yii::t('kvenum', 'eight'),
            ' ' . Yii::t('kvenum', 'nine'),
            ' ' . Yii::t('kvenum', 'ten'),
            ' ' . Yii::t('kvenum', 'eleven'),
            ' ' . Yii::t('kvenum', 'twelve'),
            ' ' . Yii::t('kvenum', 'thirteen'),
            ' ' . Yii::t('kvenum', 'fourteen'),
            ' ' . Yii::t('kvenum', 'fifteen'),
            ' ' . Yii::t('kvenum', 'sixteen'),
            ' ' . Yii::t('kvenum', 'seventeen'),
            ' ' . Yii::t('kvenum', 'eighteen'),
            ' ' . Yii::t('kvenum', 'nineteen')
        ];
    }

    /**
     * Generate list of tens
     *
     * @return array
     */
    public static function tens()
    {
        static::initI18N();
        return [
            '',
            '',
            ' ' . Yii::t('kvenum', 'twenty'),
            ' ' . Yii::t('kvenum', 'thirty'),
            ' ' . Yii::t('kvenum', 'forty'),
            ' ' . Yii::t('kvenum', 'fifty'),
            ' ' . Yii::t('kvenum', 'sixty'),
            ' ' . Yii::t('kvenum', 'seventy'),
            ' ' . Yii::t('kvenum', 'eighty'),
            ' ' . Yii::t('kvenum', 'ninety')
        ];
    }


    /**
     * Generate list of months
     *
     * @return array
     */
    public static function months()
    {
        static::initI18N();
        return [
            1 => Yii::t('kvenum', 'January'),
            Yii::t('kvenum', 'February'),
            Yii::t('kvenum', 'March'),
            Yii::t('kvenum', 'April'),
            Yii::t('kvenum', 'May'),
            Yii::t('kvenum', 'June'),
            Yii::t('kvenum', 'July'),
            Yii::t('kvenum', 'August'),
            Yii::t('kvenum', 'September'),
            Yii::t('kvenum', 'October'),
            Yii::t('kvenum', 'November'),
            Yii::t('kvenum', 'December'),
        ];
    }

    /**
     * Generate list of days
     *
     * @return array
     */
    public static function days()
    {
        static::initI18N();
        return [
            1 => Yii::t('kvenum', 'Sunday'),
            Yii::t('kvenum', 'Monday'),
            Yii::t('kvenum', 'Tuesday'),
            Yii::t('kvenum', 'Wednesday'),
            Yii::t('kvenum', 'Thursday'),
            Yii::t('kvenum', 'Friday'),
            Yii::t('kvenum', 'Saturday')
        ];
    }

    /**
     * Generate list of triplets
     *
     * @return array
     */
    public static function triplets()
    {
        static::initI18N();
        return [
            '',
            ' ' . Yii::t('kvenum', 'thousand'),
            ' ' . Yii::t('kvenum', 'million'),
            ' ' . Yii::t('kvenum', 'billion'),
            ' ' . Yii::t('kvenum', 'trillion'),
            ' ' . Yii::t('kvenum', 'quadrillion'),
            ' ' . Yii::t('kvenum', 'quintillion'),
            ' ' . Yii::t('kvenum', 'sextillion'),
            ' ' . Yii::t('kvenum', 'septillion'),
            ' ' . Yii::t('kvenum', 'octillion'),
            ' ' . Yii::t('kvenum', 'nonillion'),
        ];
    }

    /**
     * Generates a list of years
     *
     * @param integer $from the start year
     * @param integer $to the end year
     * @param boolean $keys whether to set the array keys same as the values (defaults to false)
     * @param boolean $desc whether to sort the years descending (defaults to true)
     *
     * @return array
     * @throws InvalidConfigException if $to < $from
     */
    public static function yearList($from, $to = null, $keys = false, $desc = true)
    {
        if (static::isEmpty($to)) {
            $to = intval(date("Y"));
        }
        if ($to >= $from) {
            $years = ($desc) ? range($to, $from) : range($from, $to);
            return $keys ? array_combine($years, $years) : $years;
        } else {
            throw new InvalidConfigException("The 'year to' parameter must exceed 'year from'.");
        }
    }

    /**
     * Generate a month or day array list for Gregorian calendar
     *
     * @param string  $unit whether 'day' or 'month'
     * @param boolean $abbr whether to return abbreviated day or month
     * @param boolean $start the first day or month to set. Defaults to `1`.
     * @param string  $case whether 'upper', lower', or null. If null, then
     * the initcap case will be used.
     *
     * @return array list of days or months
     */
    protected static function genCalList($unit = 'day', $abbr = false, $start = 1, $case = null)
    {
        $source = $unit == 'month' ? static::months() : static::days();
        $total = count($source);
        if ($start < 1 || $start > $total) {
            throw new InvalidConfigException("The start '{$unit}' must be between 1 and {$total}.");
        }
        $converted = [];
        foreach ($source as $key => $value) {
            $data = $abbr ? substr($value, 0, 3) : $value;
            if ($case == 'upper') {
                $data = strtoupper($data);
            } elseif ($case == 'lower') {
                $data = strtolower($data);
            }
            if ($start == 1) {
                $i = $key;
            } else {
                $i = $key - $start + 1;
                if ($i < 1) {
                    $i += $total;
                }
            }
            $converted[$i] = $data;
        }
        return (ksort($converted) ? $converted : $source);
    }

    /**
     * Generate a month array list for Gregorian calendar
     *
     * @param boolean $abbr whether to return abbreviated month
     * @param boolean $start the first month to set. Defaults to `1` for `January`.
     * @param string  $case whether 'upper', lower', or null. If null, then
     * the initcap case will be used.
     *
     * @return array list of months
     */
    public static function monthList($abbr = false, $start = 1, $case = null)
    {
        return static::genCalList('month', $abbr, $start, $case);
    }

    /**
     * Generate a day array list for Gregorian calendar
     *
     * @param boolean $abbr whether to return abbreviated day
     * @param boolean $start the first day to set. Defaults to `1` for `Sunday`.
     * @param string  $case whether 'upper', lower', or null. If null, then
     * the initcap case will be used.
     *
     * @return array list of days
     */
    public static function dayList($abbr = false, $start = 1, $case = null)
    {
        return static::genCalList('day', $abbr, $start, $case);
    }

    /**
     * Generate a date picker array list for Gregorian Calendar.
     *
     * @param integer $from the start day, defaults to 1
     * @param integer $to the end day, defaults to 31
     * @param integer $interval the date interval, defaults to 1.
     * @param integer $intervalFromZero whether to start incrementing intervals from zero if $from = 1.
     * @param integer $showLast whether to show the last date (set in $to) even if it does not match interval.
     *
     * @return array
     * @throws InvalidConfigException
     */
    public static function dateList($from = 1, $to = 31, $interval = 1, $intervalFromZero = true, $showLast = true)
    {
        if ($to < 1 || $from < 1) {
            $val = $from < 1 ? "from day '{$from}'" : "to day '{$to}'";
            throw new InvalidConfigException("Invalid value for {$val} passed. Must be greater or equal than 1");
        }
        if ($from > $to) {
            throw new InvalidConfigException("The from day '{$from}' cannot exceed to day '{$to}'.");
        }
        if ($to > 31) {
            throw new InvalidConfigException("Invalid value for to day '{$to}' passed. Must be less than or equal to 31");
        }
        if ($from > 1 || $interval == 1 || !$intervalFromZero) {
            $out = range($from, $to, $interval);
        } else {
            $out = range(0, $to, $interval);
            $out[0] = 1;
        }
        $len = count($out);
        if ($showLast && $out[$len - 1] != $to) {
            $out[$len] = $to;
        }
        return $out;
    }

    /**
     * Generate a time picker array list
     *
     * @param string  $unit the time unit ('hour', 'min', 'sec', 'ms')
     * @param integer $interval the time interval.
     * @param integer $from the time from (defaults to 23 for hour
     * @param integer $to the time to (defaults to 1).
     * @param bool    $padZero whether to pad zeros to the left of each time unit value.
     *
     * @return array
     * @throws InvalidConfigException if $unit passed is invalid
     */
    public static function timeList($unit, $interval = 1, $from = 0, $to = null, $padZero = true)
    {
        if ($unit == 'hour') {
            $maxTo = 23;
        } elseif ($unit == 'min' || $unit == 'sec') {
            $maxTo = 59;
        } elseif ($unit == 'ms') {
            $maxTo = 999;
        } else {
            throw new InvalidConfigException("Invalid time unit passed. Must be 'hour', 'min', 'sec', or 'ms'.");
        }
        if ($interval < 1) {
            throw new InvalidConfigException("Invalid time interval '{$interval}'. Must be greater than 0.");
        }
        if (empty($to)) {
            $to = $maxTo;
        }
        if ($to > $maxTo) {
            throw new InvalidConfigException("The '{$unit} to' cannot exceed {$maxTo}.");
        }
        if ($from < 0 || $from > $to) {
            throw new InvalidConfigException("The '{$unit} from' must lie between {$from} and {$to}.");
        }
        $data = range($from, $to, $interval);
        if (!$padZero) {
            return $data;
        }
        $out = [];
        $pad = strlen($maxTo . '');
        foreach ($data as $key => $value) {
            $out[$key] = str_pad($value, $pad, '0', STR_PAD_LEFT);
        }
        return $out;
    }

    /**
     * Generates a boolean list
     *
     * @param string $false the label for the false value
     * @param string $true the label for the true value
     *
     * @return array
     */
    public static function boolList($false = null, $true = null)
    {
        static::initI18N();
        return [
            false => empty($false) ? Yii::t('kvenum', 'No') : $false, // == 0
            true => empty($true) ? Yii::t('kvenum', 'Yes') : $true,  // == 1
        ];
    }

    /**
     * Convert a PHP array to HTML table
     *
     * @param array   $array the associative array to be converted
     * @param boolean $transpose whether to show keys as rows instead of columns.
     * This parameter should be used only for a single dimensional associative array.
     * If used for a multidimensional array, the sub array will be imploded as text.
     * @param boolean $recursive whether to recursively generate tables for multi-dimensional arrays
     * @param boolean $typeHint whether to show the data type as a hint
     * @param string  $null the content to display for blank cells
     * @param array   $tableOptions the HTML attributes for the table
     * @param array   $keyOptions the HTML attributes for the array key
     * @param array   $valueOptions the HTML attributes for the array value
     *
     * @return string|boolean
     */
    public static function array2table(
        $array,
        $transpose = false,
        $recursive = false,
        $typeHint = true,
        $tableOptions = ['class' => 'table table-bordered table-striped'],
        $keyOptions = [],
        $valueOptions = ['style' => 'cursor: default; border-bottom: 1px #aaa dashed;'],
        $null = '<span class="not-set">(not set)</span>'
    ) {
        // Sanity check
        if (empty($array) || !is_array($array)) {
            return false;
        }

        // Start the table
        $table = Html::beginTag('table', $tableOptions) . "\n";

        // The header
        $table .= "\t<tr>";

        if ($transpose) {
            foreach ($array as $key => $value) {
                if ($typeHint) {
                    $valueOptions['title'] = self::getType(strtoupper($value));
                }

                if (is_array($value)) {
                    $value = '<pre>' . print_r($value, true) . '</pre>';
                } else {
                    $value = Html::tag('span', $value, $valueOptions);
                }
                $table .= "\t\t<th>" . Html::tag('span', $key, $keyOptions) . "</th>" .
                    "<td>" . $value . "</td>\n\t</tr>\n";
            }
            $table .= "</table>";
            return $table;
        }

        if (!isset($array[0]) || !is_array($array[0])) {
            $array = array($array);
        }
        // Take the keys from the first row as the headings
        foreach (array_keys($array[0]) as $heading) {
            $table .= '<th>' . Html::tag('span', $heading, $keyOptions) . '</th>';
        }
        $table .= "</tr>\n";
        // The body
        foreach ($array as $row) {
            $table .= "\t<tr>";
            foreach ($row as $cell) {
                $table .= '<td>';

                // Cast objects
                if (is_object($cell)) {
                    $cell = (array)$cell;
                }

                if ($recursive === true && is_array($cell) && !empty($cell)) {
                    // Recursive mode
                    $table .= "\n" . array2table($cell, true, true) . "\n";
                } else {
                    if (!is_null($cell) && is_bool($cell)) {
                        $val = $cell ? 'true' : 'false';
                        $type = 'boolean';
                    } else {
                        $chk = (strlen($cell) > 0);
                        $type = $chk ? self::getType($cell) : 'NULL';
                        $val = $chk ? htmlspecialchars((string)$cell) : $null;
                    }
                    if ($typeHint) {
                        $valueOptions['title'] = $type;
                    }
                    $table .= Html::tag('span', $val, $valueOptions);
                }
                $table .= '</td>';
            }
            $table .= "</tr>\n";
        }
        $table .= '</table>';
        return $table;
    }

    /**
     * Parses and returns a variable type
     *
     * @param string $var the variable to be parsed
     *
     * @return string
     */
    public static function getType($var)
    {
        if (is_array($var)) {
            return 'array';
        } elseif (is_object($var)) {
            return 'object';
        } elseif (is_resource($var)) {
            return 'resource';
        } elseif (is_null($var)) {
            return 'NULL';
        } elseif (is_bool($var)) {
            return 'boolean';
        } elseif (is_float($var) || (is_numeric(str_replace(',', '', $var)) && strpos($var,
                    '.') > 0 && is_float((float)str_replace(',', '', $var)))
        ) {
            return 'float';
        } elseif (is_int($var) || (is_numeric($var) && is_int((int)$var))) {
            return 'integer';
        } elseif (is_scalar($var) && strtotime($var) !== false) {
            return 'datetime';
        } elseif (is_scalar($var)) {
            return 'string';
        }
        return 'unknown';
    }

    /**
     * Gets the user's IP address
     *
     * @param boolean $filterLocal whether to filter local & LAN IP (defaults to true)
     *
     * @return string
     */
    public static function userIP($filterLocal = true)
    {
        $ipSources = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($ipSources as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                    if ($filterLocal) {
                        $checkFilter = filter_var($ip, FILTER_VALIDATE_IP,
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
                        if ($checkFilter !== false) {
                            return $ip;
                        }
                    } else {
                        return $ip;
                    }
                }
            }
        }
        return 'Unknown';
    }

    /**
     * Gets basic browser information
     *
     * @param boolean $common show common browsers only
     * @param array   $browsers the list of browsers
     * @param string  $agent user agent
     *
     * @return array the browser information
     */
    public static function getBrowser($common = false, $browsers = [], $agent = null)
    {
        if ($agent === null) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
        }
        if ($common) {
            $browsers = [
                "opera" => Yii::t("kvenum", "Opera"),
                "chrome" => Yii::t("kvenum", "Google Chrome"),
                "safari" => Yii::t("kvenum", "Safari"),
                "firefox" => Yii::t("kvenum", "Mozilla Firefox"),
                "msie" => Yii::t("kvenum", "Microsoft Internet Explorer"),
                "mobile safari" => Yii::t("kvenum", "Mobile Safari"),
            ];
        } elseif (empty($browsers)) {
            $browsers = [
                "opera" => Yii::t("kvenum", "Opera"),
                "maxthon" => Yii::t("kvenum", "Maxthon"),
                "seamonkey" => Yii::t("kvenum", "Mozilla Sea Monkey"),
                "arora" => Yii::t("kvenum", "Arora"),
                "avant" => Yii::t("kvenum", "Avant"),
                "omniweb" => Yii::t("kvenum", "Omniweb"),
                "epiphany" => Yii::t("kvenum", "Epiphany"),
                "chromium" => Yii::t("kvenum", "Chromium"),
                "galeon" => Yii::t("kvenum", "Galeon"),
                "puffin" => Yii::t("kvenum", "Puffin"),
                "fennec" => Yii::t("kvenum", "Mozilla Firefox Fennec"),
                "chrome" => Yii::t("kvenum", "Google Chrome"),
                "mobile safari" => Yii::t("kvenum", "Mobile Safari"),
                "safari" => Yii::t("kvenum", "Apple Safari"),
                "firefox" => Yii::t("kvenum", "Mozilla Firefox"),
                "iemobile" => Yii::t("kvenum", "Microsoft Internet Explorer Mobile"),
                "msie" => Yii::t("kvenum", "Microsoft Internet Explorer"),
                "konqueror" => Yii::t("kvenum", "Konqueror"),
                "amaya" => Yii::t("kvenum", "Amaya"),
                "omniweb" => Yii::t("kvenum", "Omniweb"),
                "netscape" => Yii::t("kvenum", "Netscape"),
                "mosaic" => Yii::t("kvenum", "Mosaic"),
                "netsurf" => Yii::t("kvenum", "NetSurf"),
                "netfront" => Yii::t("kvenum", "NetFront"),
                "minimo" => Yii::t("kvenum", "Minimo"),
                "blackberry" => Yii::t("kvenum", "Blackberry"),
            ];
        }
        $info = [
            'agent' => $agent,
            'code' => 'other',
            'name' => 'Other',
            'version' => "?",
            'platform' => 'Unknown'
        ];

        if (preg_match('/iphone|ipod|ipad/i', $agent)) {
            $info['platform'] = 'ios';
        } elseif (preg_match('/android/i', $agent)) {
            $info['platform'] = 'android';
        } elseif (preg_match('/symbian/i', $agent)) {
            $info['platform'] = 'symbian';
        } elseif (preg_match('/maemo/i', $agent)) {
            $info['platform'] = 'maemo';
        } elseif (preg_match('/palm/i', $agent)) {
            $info['platform'] = 'palm';
        } elseif (preg_match('/linux/i', $agent)) {
            $info['platform'] = 'linux';
        } elseif (preg_match('/mac/i', $agent)) {
            $info['platform'] = 'mac';
        } elseif (preg_match('/win/i', $agent)) {
            $info['platform'] = 'windows';
        } elseif (preg_match('/x11|bsd|sun/i', $agent)) {
            $info['platform'] = 'unix';
        }

        foreach ($browsers as $code => $name) {
            if (preg_match("/{$code}/i", $agent)) {
                $info['code'] = $code;
                $info['name'] = $name;
                $info['version'] = static::getBrowserVer($agent, $code);
                return $info;
            }
        }
        return $info;
    }

    /**
     * Returns browser version
     *
     * @param string  $agent
     * @param browser $code
     *
     * @return float
     */
    protected static function getBrowserVer($agent, $code)
    {
        $version = '?';
        $pattern = '#(?<browser>' . $code . ')[/v ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
        if ($code == 'blackberry') {
            $pattern = '#(?<browser>' . $code . ')[/v0-9 ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
        }
        if (preg_match_all($pattern, strtolower($agent), $matches)) {
            $i = count($matches['browser']) - 1;
            $ver = [$matches['browser'][$i] => $matches['version'][$i]];
            $version = empty($ver[$code]) ? '?' : $ver[$code];
        }
        return $version;
    }
}