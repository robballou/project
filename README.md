# Project CLI

A common interface for handling differences in projects.

[![Build Status](https://travis-ci.org/robballou/project.svg?branch=master)](https://travis-ci.org/robballou/project) [![Coverage Status](https://coveralls.io/repos/github/robballou/project/badge.svg?branch=providers)](https://coveralls.io/github/robballou/project?branch=providers)

Many projects will have different setups for:

* Local development style
* Deployment styles
* Tooling commands
* Testing commands

This aims to provide a set of common terms with the configurability needed to address various needs.

## Requirements

1. PHP 7 (probably runs in later PHP 5 too)
1. [Composer](https://getcomposer.org/)

**Note:** I hope to ship a docker image shortly so you could run it via docker without needing PHP 7 locally!

## Install

1. Git clone this to your preferred location.
1. In the repo, run `composer install && composer update`
1. Symlink the `project` script into your `$PATH`. For example: `ln -s ~/git/project/project ~/bin/project` (assuming `~/bin` is in your path).

## Configuration

You can have a global configuration file at `~/.project/config.yml` and project specific configuration in `PATH/TO/PROJECT/.project/config.yml`. If you have multiple configurations in your path, the "closest" configurations take precedence. You can view your config sources by running `project config:sources`.

Want to share some configuration in your repo, but want some personal touches? Or do you go your own way for your local environment? You can also provide a `config.local.yml` file for your specific nuances. Just put that file in your an appropriate place in your directory strucuture and make sure to ignore it for your version control.

## Terms/Commands

Basic terms:

* [build](#build)
* [config](#config)
* [connect](#connect)
* [local](#local)
* [script](#script)
* [test](#test)
* [url](#url)

And some further terms:

* [Drupal](#drupal) (supports Drupal specific functionality)
* [Pass Along Commands](#pass_along_commands) (supports passing common commands to their environments)

Future terms:

* Database
* Deploy

### build

* [Example config](https://github.com/robballou/src/master/examples/build.yml)

Run build tools. This relies heavily on how it's defined in your configuration. For example:

```yaml
build:
  dev:
    style: command
    command: gulp dev
```

Then you can run: `project build dev`. If you use `scripts` in a `package.json` file, you can also load those automatically:

```yaml
options:
  build:
    package_json_scripts: $PROJECT/package.json
```

For example, if you have a `watch` command in your `package.json`, you can now run `project build watch`.

### config

    # create the .project directory
    project config:create

    # view the examples/connect.yml example
    project config:example connect

    # list all the configuration in use for this directory
    project config:list

    # list all the source files from the current directory
    project config:sources

### connect

* [Example config](https://github.com/robballou/src/master/examples/connect.yml)

Connect to the environment via a shell (e.g., via SSH or docker).

### deploy

* [Example config](https://github.com/robballou/src/master/examples/deploy.yml)

Manage deployments across projects. Can use scripts, commands, ansible, whatever you need:

    # list available deployments
    project deploy:list

    # deploy the "staging" environment
    project deploy staging

Unlike most commands, the `deploy` command does not really have an idea of "default", so you need to specify an environment to deploy.

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
- docker

Local configuration also supports pre/post hooks for running specific commands before or after `local:start` and `local:stop`.

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

### drupal

* [Example config](https://github.com/robballou/src/master/examples/drupal.yml)

Currently this supports `drush` and `drupal` console commands.

    # execute drush locally (per your local environment configuration)
    project drush

    # execute drush on staging
    project -e staging drush

**Note:** This may change to use pass along commands in future releases.

### Pass along commands

Many software development frameworks include their own tools. While some may warrant making contributed commands, many can be handled as "pass along" commands. These basically work by handling some basic connection needs familiar to `project`, but then sending all arguments to the command directly.

For example, if you want to pass things to `rails` command on your local via docker-compose:

    options:
        passalong:
            # `project rails` should connect to a docker container called "web" via docker-compose
            rails:
                style: docker-compose
                container: web

### Custom commands

You can make your own commands. The project uses [Symfony Console component](https://symfony.com/doc/current/components/console.html) to build commands.
