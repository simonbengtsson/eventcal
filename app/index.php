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
    <link href="http://eventcal.flown.io/appicon.png" rel="icon" sizes="192x192"/>
    <link href="style.css" rel="stylesheet" type="text/css">
    <title>Facebook Event Calendar</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Add Facebook events using ical. Filters out events you are not attending to or has not responded to.">
</head>
<body>

<div class="container">

    <h1><img src="appicon.png">Facebook Event Calendar</h1>

    <p class="sub-header">Subscribe to a subset of your Facebook events using ical</p>

    <p class="main-desc">
        Facebook provides <a href="https://www.facebook.com/help/152652248136178/">a way to export</a> and sync all your
        events to a third party calendar application such as Google Calendar. However they include events you have not yet
        responded to which can fill up your calendar pretty quickly if you get invited to a lot of events. <br><br>

        This service acts a filter between facebook and your calendar and you can choose to filter out events you don't want.
        If you want to filter events in some other
        way or have some other feature request etc, let me know on <a href="https://google.com/+simonbengtsson">Google+</a> or
        <a href="https://twitter.com/someatoms">Twitter</a>.
    </p>

    <div class="options">
        <label style="display: block; margin-bottom: 10px;">Include the following events</label>
        <label class="switcher">
            <input type="checkbox" checked="checked" value="<?php echo STATUS_GOING ?>"/>
            <span class="switcher__indicator"></span> Going
        </label><br/>
        <label class="switcher">
            <input type="checkbox"  checked="checked"  value="<?php echo STATUS_MAYBE ?>"/>
            <span class="switcher__indicator"></span> Maybe and Intrested
        </label><br/>
        <label class="switcher">
            <input type="checkbox"  value="<?php echo STATUS_UNDECIDED ?>"/>
            <span class="switcher__indicator"></span> Not responded to
        </label><br/>
    </div>

    <form id="calendar-form">
        <label for="fb-calendar">Facebook calendar</label><br>
        <input id="fb-calendar">

        <p class="help-block">
            To get your facebook calendar link follow <a href="https://www.facebook.com/help/152652248136178">these</a>
            instructions. Your calendar link should look something like this
            <em>webcal://www.facebook.com/ical/u.php?uid=1645531053&key=AQDdB7kP2hjd14uh</em>
        </p>
    </form>

    <label for="filtered-calendar">Filtered calendar</label><br>
    <input id="filtered-calendar" readonly>

    <p class="help-block">Subscribe to this calendar in Google Calendar or a similar application</p>

    <p class="open-source-desc">
        If you have a feature request or have found a bug you can post an issue on
        <a href="http://github.com/someatoms/eventcal">github</a>.
        <iframe class="github-badge"
                src="https://ghbtns.com/github-btn.html?user=someatoms&repo=eventcal&type=star&count=false"
                frameborder="0"
                scrolling="0"></iframe>
    </p>

</div>

<script src="https://code.jquery.com/jquery-3.1.0.min.js"></script>
<script>
    +function () {
        var $form = $("#calendar-form");
        var $filteredCal = $('#filtered-calendar');
        var $switches = $('.options .switcher input');

        $switches.change(function() {
            update();
        });

        $form.find('input').on('input', function () {
            update();

        });

        function update() {
            var cal = $("#fb-calendar").val().trim();
            if (cal) {
                var base = 'webcal://' + window.location.host + '/?calendar=';
                var url = base + encodeURIComponent(cal);


                var $checked = $('.options .switcher input:checked');
                var status = $checked.map(function() {
                    return this.value;
                }).toArray();
                url += '&status=' + status.join(',');

                $filteredCal.val(url);
            } else {
                $filteredCal.val("");
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
