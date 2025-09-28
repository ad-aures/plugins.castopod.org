# The Castopod Plugin Hub (plugins.castopod.org)

The Castopod Plugin Hub is the place to discover, download, and manage your
plugins for Castopod.

## Configure

For computing downloads every day at midnight, set the following cron job:

```sh
* * * * * php spark tasks:run >> /dev/null 2>&1
```
