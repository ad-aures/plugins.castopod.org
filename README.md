<div align="center">

# The Castopod Plugin Repository (plugins.castopod.org) 📼🧩

**The Castopod Plugin Repository** is the place to discover, download, and
manage your plugins for [Castopod](https://castopod.org/). Built with
[CodeIgniter4](https://codeigniter.com/), a powerful PHP framework with a very
small footprint.

👉 **Visit the official Castopod plugin repository:
[plugins.castopod.org](https://plugins.castopod.org)** 👈

</div>

- [🎯 Requirements](#-requirements)
- [Bundle for production](#bundle-for-production)
- [⚙️ Configure](#️-configure)
  - [Set environment variables](#set-environment-variables)
  - [Supervisor](#supervisor)
  - [Cron task](#cron-task)
- [🛡️ Security](#️-security)
- [💸 Funding](#-funding)
- [📜 License](#-license)

## 🎯 Requirements

- [**PHP 8.4**](https://www.php.net/releases/8.4/en.php) with the following
  extensions:
  - [intl](https://www.php.net/manual/en/intl.installation.php)
  - [mbstring](https://www.php.net/manual/en/mbstring.installation.php)
  - [json](https://www.php.net/manual/en/json.installation.php)
  - [pgsql](https://www.php.net/manual/en/pgsql.installation.php) (with
    [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php))
  - [zip](https://www.php.net/manual/en/zip.installation.php)

- [**PostgreSQL**](https://www.postgresql.org/download/)
- [**Supervisor**](https://www.supervisord.org/installing.html) to crawl plugins
  automatically
- (optional) **Redis** for caching

## Bundle for production

Run [`./scripts/bundle.sh`](./scripts/bundle.sh) script to bundle the app into a
`dist` folder, discarding any file meant for development only.

## ⚙️ Configure

### Set environment variables

Rename the `.env.example` file to `.env` and replace placeholder values with
yours.

### Supervisor

Supervisor is used to consume the crawls queue, whenever a new plugin is added
to the queue, the supervisor program will run a job to crawl it.

```ini
# /etc/supervisor/conf.d/plugin-crawler.conf

[program:castopod_plugin_repository-crawler]
command=php spark queue:work crawls -wait 10
directory=/path/to/castopod-plugin-repository
autostart=true
autorestart=true
stdout_logfile=/path/to/castopod-plugin-repository/writable/logs/crawls-worker.log
stderr_logfile=/path/to/castopod-plugin-repository/writable/logs/crawls-worker.err.log
```

### Cron task

For computing downloads every day at midnight, set the following cron job:

```sh
* * * * * php spark tasks:run >> /dev/null 2>&1
```

## 🛡️ Security

Make all files read only except `writable` folder.

## 💸 Funding

The Castopod Plugin Repository project received funding through
[NGI0 Entrust](https://nlnet.nl/entrust), a fund established by
[NLnet](https://nlnet.nl) with financial support from the European Commission's
[Next Generation Internet](https://ngi.eu) program. Learn more at the
[NLnet project page](https://nlnet.nl/project/CastopodPlugins).

[<img src="https://nlnet.nl/logo/banner.png" alt="NLnet foundation logo" width="20%" />](https://nlnet.nl)

[<img src="https://nlnet.nl/image/logos/NGI0_tag.svg" alt="NGI Zero Logo" width="20%" />](https://nlnet.nl/entrust)

## 📜 License

Code released under the
[AGPL v3 License](https://choosealicense.com/licenses/agpl-3.0/).

Copyright (c) 2025-present, [Ad Aures](https://adaures.com/).
