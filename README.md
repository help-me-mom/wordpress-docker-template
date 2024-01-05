# Preconfigured local setup for WordPress development with Docker

This template provides:

- WordPress container (the version of wordpress can be changed)
- MySQL container (the version of mysql can be changed)
- Mail container to catch all emails WordPress sends
- xdebug extension and bookmarks to enable / disable debugging
- /cdn-bin/ scripts to update site url in the whole database

## Installation for a new project

Checklist:

- [ ] clone / download this repo: https://github.com/help-me-mom/wordpress-docker-template
- [ ] remove the line with `/website/src` from [`.gitignore`](./.gitignore)
- [ ] if needed, update mysql version in [`compose.yml`](./compose.yml) in `services > databse > image`  
  by default the latest one is used
- [ ] if needed, update wordpress version in [`website/docker.dev/Dockerfile`](./website/docker.dev/Dockerfile)
  in `FROM`  
  by default the latest one is used
- [ ] you might want to use a different hostname than `localhost`, because `localhost` won't let you send emails  
  for example, you can add `127.0.0.1 wp.localhost` to `/etc/hosts` or `C:\Windows\System32\drivers\etc\hosts` file
- [ ] you can execute `docker compose up --build` to build and start containers  
  you might need to wait until containers finish their bootstrap: creating databases, downloading WP, etc.
- [ ] open http://wp.localhost/ to finish WP installations
- [ ] open http://localhost:81/ to verify you got an email about a new WordPress Website
- [ ] that's it, now you can commit changes to git and start development

## Installation for an existing project

Checklist:

- [ ] ensure you have source files of the existing project
- [ ] ensure you have a database dump of the existing project
- [ ] clone / download this repo: https://github.com/help-me-mom/wordpress-docker-template
- [ ] remove the line with `/website/src` from [`/.gitignore`](./.gitignore)
- [ ] update mysql version in [`/compose.yml`](./compose.yml) in `services > databse > image`  
  you should use the same version as in the existing project
- [ ] update wordpress version in [`/website/docker.dev/Dockerfile`](./website/docker.dev/Dockerfile) in `FROM`  
  you should use the same version as in the existing project
- [ ] you might want to use a different hostname than `localhost`, because `localhost` won't let you send emails  
  for example, you can add `127.0.0.1 wp.localhost` to `/etc/hosts` or `C:\Windows\System32\drivers\etc\hosts` file
- [ ] copy files from the existing project into [`/website/src`](./website/src)
- [ ] update [`wp-config.php`](./website/src/wp-config.php) to respect local database connection  
  if you have defined `DB_NAME`, `DB_USER`, `DB_HOST`, etc, you need wrap them with an if-else block:
  ```php
  if (getenv('WORDPRESS_DB_HOST')) {
    define('DB_HOST', getenv('WORDPRESS_DB_HOST'));
    define('DB_USER', getenv('WORDPRESS_DB_USER'));
    define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD'));
    define('DB_NAME', getenv('WORDPRESS_DB_NAME'));
    define('DB_CHARSET', getenv('WORDPRESS_DB_CHARSET'));
    define('WP_DEBUG', getenv('WORDPRESS_DEBUG'));
  } else {
    // here you need to place existing definitions of the contants above
  }
  ```
- [ ] you can execute `docker compose up --build` to build and start containers  
  you might need to wait until containers finish their bootstrap: creating databases, etc.
- [ ] import the database dump: [instructions how to import a database](#import-database)
- [ ] replace domain name in the database: http://wp.localhost/cgi-bin/
- [ ] open http://wp.localhost/ to verify website works locally as expected
- [ ] that's it, now you can commit changes to git and start development

### Export database

To export the database from docker, you need to execute the next command:

> docker compose exec -T database mysqldump -uroot website --routines > website.sql

### Import database

To import a database into docker, you need to execute the next command,
where `website.sql` should be the file you want to import:

> docker compose exec -T database mysql -uroot website < website.sql

### Check emails

Emails can be checked at http://localhost:81/

## Deployment to production

TBD

## Debugging

TBD
