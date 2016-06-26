UpcomingTasks
=======

## About

[UpcomingTasks](http://upcomingtasks.com) is a free web-based [Basecamp 2](https://basecamp.com/2) client I created to easily manage tasks on smaller devices.

Users also have the option to upgrade to a Pro account which unlocks [additional features](http://upcomingtasks.com/pages/pro.php).

It was created in [Sublime Text](http://www.sublimetext.com/) using the [Basecamp API](https://github.com/basecamp/bcx-api) and hosted by [Digital Ocean](http://digitalocean.com).

## Why

I was inspired by [Brad Frost](https://github.com/bradfrost)'s [TED talk](https://twitter.com/brad_frost/status/476515058738925568) about being open by default. If you haven't seen this talk yet, I'd recommend investing half an hour to [watch the video](https://www.youtube.com/watch?v=7rW9vTrN6OU) and read the [blog post](http://bradfrostweb.com/blog/post/creative-exhaust/).

As I'm self-taught, engaging with the [community](https://twitter.com/brendanmurty/lists/web-design/members), [listening to inspirational people](http://boagworld.com/show) and [reading about new techniques](https://signalvnoise.com/programming) helped me turn my passion in to my career.

I hope an aspiring web developer can learn something new from what I've done here and start their own career. Hopefully I can give back to the community that has taught me so much over the last few years.

## Contribute

If you have an idea for an update or have found a bug, please [submit a new issue](https://github.com/brendanmurty/upcomingtasks/issues/new?assignee=brendanmurty). I'm always open to discussion about how to make UpcomingTasks a better product for all users.

## Installation

Here's the steps that I go through to configure a new UpcomingTasks server on a [Digital Ocean](https://www.digitalocean.com) Ubuntu 14.04 VPS.

### Install required packages

    sudo apt-get update
    sudo apt-get -y install git
    sudo apt-get -y install apache2
    sudo apt-get -y install mysql-server libapache2-mod-auth-mysql php5-mysql
    sudo apt-get -y install php5 libapache2-mod-php5 php5-mcrypt
    sudo apt-get -y install php5-cgi php5-curl
    sudo apt-get -y install sendmail

### Initialise a Git clone of the code

You'll now need to [configure a SSH key for GitHub access](https://help.github.com/articles/generating-an-ssh-key/).

    cd /var/www/html/
    rm index.html
    git clone git@github.com:brendanmurty/upcomingtasks.git .

### Configure application authentication information

    cp libs/auth.php.sample libs/auth.php
    vim libs/auth.php

Update the authentication tokens and login information in this private file.

### Setup the database and create the "users" table

    mysql -u root -p
    [enter password]
    create database upcomingtasks;
    exit
    mysql -u root -p upcomingtasks < scripts/sql/users.sql

### Configure the server to use the customised ".htaccess" file

    sudo cp scripts/server/web-server.conf /etc/apache2/sites-available/000-default.conf
    vim /etc/apache2/sites-available/000-default.conf

Update the *ServerName* and *ServerAdmin* values to suit your server and email address.

    vim .htaccess

Update the domain references in this file.

    sudo service apache2 restart

### Setup the SSL certificate (optional)

If you've setup your Basecamp Integration app to use a HTTPS URL then you'll first need to purchase and [configure an SSL certificate](https://www.digitalocean.com/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority) on your server.

    sudo cp scripts/server/ssl.conf /etc/apache2/sites-available/default-ssl.conf
    vim /etc/apache2/sites-available/default-ssl.conf

Update the domain, admin, and SSL path values to suit your information and locations of SSL files.

    sudo service apache2 restart

## License

You can view the [License](https://github.com/brendanmurty/upcomingtasks/blob/master/license.md) file for rights and limitations when using the code here in your own projects.

The license is based on the [CSS-Tricks License](https://css-tricks.com/license/) which was created by [Chris Coyier](https://github.com/chriscoyier/).
