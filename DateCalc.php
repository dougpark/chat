<?php

class DateCalc
{

    public function __construct()
    {
    }

    // Sample  date formats
    // 
    // 5/18/20                  - macOS Messages - contact list
    // 5/18/20, 4:09 AM         - macOS Messages - message details
    //
    // macOS Mail   
    // 8:49 AM                  - list (today)
    // Yesterday                - list (yesterday)
    // 5/18/20                  - list (earlier than yesterday)
    // 8:40 AM                  - mail detail (today)
    // Yesterday at 3:23 PM     - mail detail (yesterday)
    // May 3, 2020 at 1:20 AM   - mail detail (earlier than yesterday)
    //
    // Gmail webmail
    // May 18                   - mail list
    // Sun, May 3, 1:27 AM      - mail detail (earlir than 13 days ago)
    // Tue, May 19, 10:54 AM (22 hours ago) - mail detail
    // Mon, May 18, 5:13 PM (13 days ago) - mail detail
    //
    // Slack macOS
    // Yesterday
    // Today
    // Monday, May 18th
    // 6:57 PM
    //
    // DayOne
    // Mon, May 18, 2020, 10:03 AM CDT


    // get data on one user = userid
    public function convert($dateIn)
    {
        date_default_timezone_set('America/Chicago');

        // chat msg date
        $msg = DateTime::createFromFormat("Y-m-d H:i:s", $dateIn);

        // calc days between dates at midnight
        $todayMidnight = Date_create()->setTime(0, 0, 0);
        $msgMidnight = DateTime::createFromFormat("Y-m-d H:i:s", $dateIn)->setTime(0, 0, 0);
        $diff = $todayMidnight->diff($msgMidnight)->format("%d");

        // determine when to print Today and Yesterday
        if ($diff == 0) {
            $out = "Today, " . $msg->format('h:i A');
        } else {
            if ($diff == 1) {
                $out = "Yesterday, " . $msg->format('h:i A');
            } else {
                $out = $msg->format('D, M d h:i A') . " ($diff days ago)";
            }
        }

        return  $out;
    }
}
