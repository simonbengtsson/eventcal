# Facebook Event Calendar
Subscribe to a subset of your Facebook events using ical

Facebook provides a way to export and sync all your events to a third party calendar application such as Google Calendar. However you can't choose which events you want to sync and that's where this service steps in. Right know it simply removes events you have not responded to yet. If you want to filter events in some other way, let me know on [Google+](https://google.com/+simonbengtsson) or [twitter](https://twitter.com/someatoms).

### Deploy
To deploy this website, simply upload everything in the `app` folder to your server. This can for example be done with rsync.

`rsync -a app/ root@flown.io:~/sites/eventcal.flown.io`

### Contribute
Feature request, pull request and issues are welcome.