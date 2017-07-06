# Project CLI

A common interface for handling differences in projects.

[![Build Status](https://travis-ci.org/robballou/project.svg?branch=master)](https://travis-ci.org/robballou/project)

Many projects will have different setups for:

* Local development style
* Deployment styles
* Tooling commands
* Testing commands

This aims to provide a set of common terms with the configurability needed to address various needs.

## Requirements

1. PHP 7 (probably runs in later PHP 5 too)
1. [Composer](https://getcomposer.org/)

## Install

1. Git clone this to your preferred location.
1. In the repo, run `composer install && composer update`
1. Symlink the `project` script into your `$PATH`. For example: `ln -s ~/git/project/project ~/bin/project` (assuming `~/bin` is in your path).

## Configuration

You can have a global configuration file at `~/.project/config.yml` and project specific configuration in `PATH/TO/PROJECT/.project/config.yml`. If you have multiple configurations in your path, the "closest" configurations take precedence. You can view your config sources by running `project config:sources`.

## Terms/Commands

### build

* [Example config](https://github.com/robballou/src/master/examples/build.yml)

Run build tools. This relies heavily on how it's defined in your configuration. For example:

```yaml
build:
  dev:
    style: command
    command: gulp dev
```

Then you can run: `project build dev`

### connect

* [Example config](https://github.com/robballou/src/master/examples/connect.yml)

Connect to the environment via a shell (e.g., via SSH or docker).

### local

* [Example config](https://github.com/robballou/src/master/examples/local.yml)

Commands dealing with local development:

    # start the default dev environments
    project local:run
    project local:start
    project local:up

    # run the default environment
    project local:run default

    # run the "frontend" environment
    project local:run frontend

    # run two things
    project local:run default api

    # stop the default dev environments
    project local:stop
    project local:down

    # restart the default local dev
    project local:restart

    # list local components that can be controlled
    project local:list

Planned support:

- vagrant
- docker-compose
- drocker
- ???

### script

* [Example config](https://github.com/robballou/src/master/examples/script.yml)

Run a defined script.

    project script some_script
    project script some_script much args

### test

* [Example config](https://github.com/robballou/src/master/examples/test.yml)

Run tests with the project's preferred test suites.

    # run all the tests
    project test

    # run specific tests
    project test behat


### url

* [Example config](https://github.com/robballou/src/master/examples/url.yml)

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
