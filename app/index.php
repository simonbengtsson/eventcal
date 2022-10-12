<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

function dd($var)
{
    var_dump($var);
    die();
}

function array_get($arr, $key, $default = null)
{
    return isset($arr[$key]) ? $arr[$key] : $default;
}

const STATUS_MAYBE = 'TENTATIVE'; // This is also the status for "interested"
const STATUS_GOING = 'ACCEPTED';
const STATUS_UNDECIDED = 'NEEDS-ACTION';

// Routing for php's built in web server
$ext = array_get(pathinfo($_SERVER['REQUEST_URI']), 'extension');
if (in_array($ext, ['png', 'jpg', 'jpeg', 'css', 'js', 'gif', 'html'])) {
    return false;
}

require_once('logger.php');
require_once('app.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://eventcal.flown.io/appicon.png" rel="icon" sizes="192x192"/>
    <link href="style.css" rel="stylesheet" type="text/css">
    <title>Facebook Event Calendar</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Add Facebook events using ical. Filters out events you are not attending to or has not responded to.">
</head>
<body>

<div class="container">

    <h1><img src="appicon.png">Facebook Event Calendar</h1>

    <p class="sub-header">Filter Facebook events before exporting them to your calendar (ical format)</p>

    <p class="main-desc">
        Facebook provides <a href="https://www.facebook.com/help/152652248136178/">a way to export</a> and sync all your
        events to a third party calendar application such as Google Calendar, Apple Calendar or Outlook. However they
        include events not yet responded to. This service can declutter your calendar by filtering out those events.
    </p>

    <h3><span class="progress-step first">1</span> Get the URL of your upcoming Facebook events</h3>
    Currently (May 2021) it can be done this way, but facebook might do minor redesigns in the future. Facebook provides a <a href="https://www.facebook.com/help/152652248136178">guide</a> for exporting one event to a calendar which might be a good start if you are having issues with the below steps.
    <ol>
        <li>On the desktop go to your <a href="https://www.facebook.com/events/calendar">facebook event list</a>.</li>
        <li>Right click the "Add to Calendar" button</li>
        <li>Click "Copy Link Address" and paste it below</li>
    </ol>
    <input id="fb-calendar" placeholder="Paste the Facebook calendar URL here">
    <p>The URL should look similar to this: <em>https://www.facebook.com/events/ical/upcoming/?uid=6246345&key=6SAh6345G5</em></p>

    <h3><span class="progress-step second">2</span> Choose the events you want</h3>
    <div class="options">
        <label class="switcher">
            <input type="checkbox" checked="checked" value="<?php echo STATUS_GOING ?>"/>
            <span class="switcher__indicator"></span> Going
        </label><br/>
        <label class="switcher">
            <input type="checkbox"  checked="checked"  value="<?php echo STATUS_MAYBE ?>"/>
            <span class="switcher__indicator"></span> Maybe and Interested
        </label><br/>
        <label class="switcher">
            <input type="checkbox"  value="<?php echo STATUS_UNDECIDED ?>"/>
            <span class="switcher__indicator"></span> Not responded to
        </label><br/>
    </div>

    <h3><span class="progress-step third">3</span> Subscribe to the filtered calendar</h3>
    <p>Note that it might take up to <a href="https://support.google.com/calendar/answer/37100?hl=en&ref_topic=1672445">12 hours</a> for changes to show in your Google Calendar or other calendar clients</p>
    <button id="nativecal"><img src="appicon.png">System Calendar</button>
    <button id="gcal"><img src="gcal.png">Google Calendar</button>
    <p>Or you can manually subscribe to the calendar with the following URL:</p>
    <input id="filtered-calendar" readonly placeholder="Filtered calendar will appear here">

    <p class="help-block"></p>

    <p class="open-source-desc">
        If you have any questions, let me know on <a href="https://twitter.com/simongbengtsson">Twitter</a> or
        <a href="https://github.com/simonbengtsson/eventcal">Github</a>
        <br>
        <br>
        <iframe src="https://ghbtns.com/github-btn.html?user=simonbengtsson&repo=eventcal&type=star&count=true&size=large" frameborder="0" scrolling="0" width="160px" height="30px"></iframe>
    </p>

</div>

<script src="https://code.jquery.com/jquery-3.1.0.min.js"></script>
<script>
    +function () {
        var $fbCal = $("#fb-calendar");
        var $gcal = $("#gcal");
        var $nativecal = $("#nativecal");
        var $filteredCal = $('#filtered-calendar');
        var $switches = $('.options .switcher input');

        $gcal.click(function() {
            if (!validateURL()) return;
            var url = getFilteredCalendar();
            var gurl = "https://www.google.com/calendar/render?cid=" + encodeURIComponent(url);
            window.open(gurl);
        });

        $nativecal.click(function() {
            if (!validateURL()) return;
            var url = getFilteredCalendar();
            window.open(url, '_self');
        });

        $switches.change(update);
        $fbCal.on('input', update);

        function update() {
            $fbCal.css('border-color', '#000');
            var url = getFbCalendar();
            if (url && !validateURL()) {
                $fbCal.css('border-color', '#e74c3c');
            } else if (url) {
                var filtered = getFilteredCalendar();
                $filteredCal.val(filtered);

                var verifyUrl = getFilteredCalendar(true)
                $.get(verifyUrl).then(function (res) {
                    var eventCount = (res.match(/BEGIN:VEVENT/g) || []).length;
                    console.log('Event count', eventCount)
                    window.calendar = res
                }).catch(function (error) {
                    console.log('error', error)
                })
            }
        }

        function validateURL() {
            var url = getFbCalendar();
            var urlregex = /^(https?|webcal):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/;
            return urlregex.test(url);
        }

        function getFbCalendar() {
            return $("#fb-calendar").val().trim();
        }

        function getFilteredCalendar(verifyUrl) {
            var cal = getFbCalendar();
            if (cal) {
                const protocol = verifyUrl ? window.location.protocol : 'webcal:'
                var base = protocol + '//' + window.location.host + window.location.pathname + '?base64=true&calendar=';
                var url = base + window.btoa(cal);

                var $checked = $('.options .switcher input:checked');
                var status = $checked.map(function() {
                    return this.value;
                }).toArray();
                url += '&status=' + status.join(',');
                url += verifyUrl ? '&verify=true' : ''
                return url;
            } else {
                return "";
            }
        }

        $filteredCal.click(function () {
            $(this).select();
        });
    }();
</script>
<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-34855982-11', 'auto');
    ga('send', 'pageview');

</script>

</body>
</html>
