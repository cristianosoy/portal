# [DO!Hack](https://dohack.io) - Hackathon registration portal

[![Build Status](https://img.shields.io/travis/splitt3r/portal.svg?style=flat-square)](https://travis-ci.org/splitt3r/portal)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the hackathon registration portal for DO!Hack 2017. It´s implemented as a [Sprinkle](https://learn.userfrosting.com/sprinkles/introduction) for UserFrosting. It was developed by [iGore](https://github.com/iGore) and [me](https://github.com/splitt3r).

## Installation
In your `app/sprinkles/` folder do:
```
git submodule add -b master git@github.com:splitt3r/portal.git portal
```
Edit your `app/sprinkles.json`:
```
{
    ...,
    "base": [
        ...,
        "portal"
    ]
}
```
In your root folder do:
```
composer update
php bakery bake
```

### Configuration
Finally the slug of the default group needs to be `user` and you have to remove the `uri_dashboard` rigth from the default user role and add it to the other ones.

### [Swot](https://github.com/JetBrains/swot) / University Import
You can import recent [Swot](https://github.com/JetBrains/swot) changes by simply running:
```
php bakery import
```
For this to work you need to specify the path to your locally checked out [Swot](https://github.com/JetBrains/swot) copy in the sprinkles config files.

### Send statistics to Slack
You need to add the following line to your .env file:
```
SLACK_WEBHOOK=https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX
```
And add the following command as a cronjob (Should be run once a day)
```
php bakery slack
```

### Send emails to user groups
You can for example automatically send reminder emails to users who registered but didn´t create an application yet:
```
php bakery email --application (all|with|without) --template application-remind
```
The email template needs to exist as `application-remind.html.twig` in the `template/mail` directory.

## Style Guide
PHP: [UF Style Guide](https://github.com/userfrosting/UserFrosting/blob/master/STYLE-GUIDE.md) 

HTML & CSS: [Code Guide by @mdo](http://codeguide.co/)

## Dependencies
Base system: [UserFrosting](https://github.com/userfrosting/UserFrosting)

Email validation: [Swot](https://github.com/JetBrains/swot)
