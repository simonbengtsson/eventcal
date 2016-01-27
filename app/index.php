<?php

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

// Routing for php's built in web server
$ext = array_get(pathinfo($_SERVER['REQUEST_URI']), 'extension');
if (in_array($ext, ['png', 'jpg', 'jpeg', 'css', 'js', 'gif', 'html'])) {
    return false;
}

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
          content="Subscribe to a subset of your Facebook events using ical. Filters out events you are not attending to or has not responded to.">
</head>
<body>

<div class="container">

    <h1><img src="appicon.png">Facebook Event Calendar</h1>

    <p class="sub-header">Subscribe to a subset of your Facebook events using ical</p>

    <p class="main-desc">
        Facebook provides <a href="https://www.facebook.com/help/152652248136178/">a way to export</a> and sync all your events to a third party calendar application such as Google
        Calendar. However you can't choose which events you want to sync and that's where this service steps in.<br><br>
        Right know it simply removes events you have not responded to yet as you probably won't attend them. If you want
        to filter events in some other
        way, let me know on <a href="https://google.com/+simonbengtsson">Google+</a> or
        <a href="https://twitter.com/someatoms">Twitter</a>.
    </p>

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

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script>
    +function () {
        var $form = $("#calendar-form");
        var $filteredCal = $('#filtered-calendar');

        $form.find('input').on('input', function () {
            var cal = $("#fb-calendar").val().trim();
            var domain = 'webcal://' + window.location.host + '/?calendar=';
            $filteredCal.val(domain + encodeURIComponent(cal));
        });

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
