ammana
======

Generator for job protocols - [ammana.es](https://ammana.es)

## Build status
[![Build Status](https://travis-ci.org/NoLegalTech/ammana.svg?branch=master)](https://travis-ci.org/NoLegalTech/ammana)
[![Build Status](https://travis-ci.org/NoLegalTech/ammana.svg?branch=pre)](https://travis-ci.org/NoLegalTech/ammana)
[![Build Status](https://travis-ci.org/NoLegalTech/ammana.svg?branch=dev)](https://travis-ci.org/NoLegalTech/ammana) (pro/pre/dev)

## Installation

### On a server

Your server needs to meet the following requirements:
 - php >= 7.1.8
 - mysql >= 5.5
 - git >= 2.1.4

Follow the next steps in your server (via ssh):
1. Clone the repository
2. If the server/path is intended for pre-production checkout the "pre" git branch
2. Create a mysql database
3. cp app/config/parameters.yml.dist app/config/parameters.yml
4. Edit app/config/parameters.yml to set up the database connection data

Follow the next steps in your local machine:
1. Clone the repository
2. cp deploy/parameters.yml.dist deploy/parameters.yml
3. Edit deploy/parameters.yml to set up the server connection data
4. Run "deploy/deploy pre" or "deploy/deploy pro" to deploy to pre-production or production respectively

NOTE: the deploy script NEVER deploys from your local copy, it takes the "pre" or "master" branches versions
from the repository to deploy to the server. If you need to deploy your own code version, fork the repository
so that you can have your own "pre" and "master" branches.


### For development

First you need to meet the following requirements:
 - php >= 7.1.8
 - mysql >= 5.5
 - git >= 2.1.4

Then follow the steps:
1. Clone the repository
2. Create a database in your local mysql
3. cp app/config/parameters.yml.dist app/config/parameters.yml
4. Edit app/config/parameters.yml to set up the database connection data
5. Run bin/rebuild_db
6. Run bin/run to run the web server

## Contributing

Read [this](/CONTRIBUTING.md).

## Licensing

The source code is licensed under GPL v3. License is available [here](/LICENSE).
