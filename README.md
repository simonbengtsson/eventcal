<a href='https://ko-fi.com/A535IR4' target='_blank'>
<img height='36' style='border:0px;height:36px;' src='https://az743702.vo.msecnd.net/cdn/kofi4.png?v=f' border='0' alt='Buy Me a Coffee at ko-fi.com' />
</a> 

# Facebook Event Filter
Filters and sanitizes your Facebook events into ical format. 

Facebook provides a way to export and sync all your events to a third party calendar application such as Google Calendar, Apple Calendar or Outlook. However they include events not yet responded to. This service can declutter your calendar by filtering out those events.

### Deploy
To deploy this service to your own server, simply upload everything in the `app` folder. With `rsync` this would like something like this:

`rsync -a --chown=www-data:www-data app/ username@example.com:/var/www/eventcal`

### Develop
The easiest way to start development is by using php's built in web server.

`php -S localhost:8080 -t app`

### Contributions
Questions, feature requests, pull requests and bug reports are more than welcome!
