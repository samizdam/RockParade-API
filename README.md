RockParade
==========

[![Build Status](https://travis-ci.org/VAPC/RockParade-API.svg?branch=master)](https://travis-ci.org/VAPC/RockParade-API)
[![Coverage](https://codecov.io/gh/VAPC/RockParade-API/branch/master/graph/badge.svg)](https://codecov.io/gh/VAPC/RockParade-API)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/VAPC/RockParade-API/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/VAPC/RockParade-API/?branch=master)

Test coverage (interactive)
===========================

Click on graph, and it will opens separately. You can click any rectangle to see code coverage of represented class.

![Test coverage grapth](https://codecov.io/gh/VAPC/RockParade-API/branch/master/graphs/tree.svg "Test coverage graph")

Conversation
============
Join our Slack channel: https://rockparade.slack.com

Requirements
============
* PHP 7.0
* PHP-extension: ext-mbstring
* PHP-extension: ext-pdo
* PHP-extension: ext-xml
* PHP-extension: ext-curl
* MySQL 5.5
* Composer [link](https://getcomposer.org)

How to run server
=================
1. Run MySQL server.
2. Clone this repository and follow to its directory.
3. Run `composer install`. Enter database credentials.
3. Run symfony server: `php bin/console server:run`.
4. Server will be available on [http://localhost:8000](http://localhost:8000).

How to run tests
================
1. Go to project directory and run `composer install`.
2. Run tests `vendor/bin/phpunit tests`.

API documentation
=================
Last actual documentation is available at http://rockparade.creora.ru/api. You can try queries in sandbox.

Contribution
============
1. Take issue from [task tracker board](http://redmine.rockparade.creora.ru).
2. Fork repository on github.
3. When work is done, create pull request from your master branch to this repository master branch.
