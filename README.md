<a href='https://ko-fi.com/A535IR4' target='_blank'>
<img height='36' style='border:0px;height:36px;' src='https://az743702.vo.msecnd.net/cdn/kofi4.png?v=f' border='0' alt='Buy Me a Coffee at ko-fi.com' />
</a> 

# Facebook Event Calendar
Filters and sanitizes your Facebook events into ical format. 

Facebook provides a way to export and sync all your events to a 
third party calendar application such as Google Calendar.
However you can't choose which events you want to sync and 
sometimes the sync breaks as an event has characters not supported 
by some calendar clients.

That's where this service steps in. It removes removes common 
unsupported characters and filters your events to include the 
ones you choose. By default it means that it only includes events 
you have marked as maybe, interesting or going to. If you want to 
filter events in some other way, let me know on
[Google+](https://google.com/+simonbengtsson) or [twitter](https://twitter.com/simonbengtsson).

### Deploy
To deploy this service to your own server, simply upload everything in the `app`. With `rsync` this would like something like this:

`rsync -a --chown=www-data:www-data app/ username@example.com:/var/www/eventcal`

### Develop
The easiest way to start development is by using php's built in web server.

`php -S localhost:8080 -t app`

### Discussion - The Facebook API
It is possible to get event information from the Facebook Graph API and create an ical file from that. This would
make it easier for the user to sign up for the service, but would require a re-login every 60 days as that is the
 maximum period an access token is valid for.

### Contributions
Feature request, pull request and bug reports are more than welcome!
