# Project CLI

A common interface for handling differences in projects.

Many projects will have different setups for:

* Local development style
* Deployment styles
* Tooling commands
* Testing commands

This aims to provide a set of common terms with the configurability needed to address various needs.

## Install

1. Git clone this to your preferred location.
1. In the repo, run `composer install && composer update`
1. Symlink the `project` script into your `$PATH`. For example: `ln -s ~/git/project/project ~/bin/project` (assuming `~/bin` is in your path).

## Configuration

You can have a global configuration file at `~/.project/config.yml` and project specific configuration in `PATH/TO/PROJECT/.project/config.yml`.

## Terms/Commands

### build

Run build tools. This relies heavily on how it's defined in your configuration. For example:

```yaml
build:
  dev:
    style: command
    command: gulp dev
```

Then you can run: `project build dev`

### connect

Connect to the environment via a shell (e.g., via SSH or docker).

### local

Commands dealing with local development:

    # start any local dev environments
    project local:run
    project local:start
    project local:up

    # stop any local dev environments
    project local:stop
    project local:down

    # restart local dev
    project local:restart

Planned support:

- vagrant
- docker-compose
- drocker
- ???

### test

Run tests with the project's preferred test suites.

Planned support:

- phpunit
- behat
- JS testing frameworks, etc.

### url

Save common URLs for a project:

    # output all links
    project url

    # output a specific link
    project url stage

URLs are saved in the config files:

```yaml
url:
  stage: http://example.com
```
