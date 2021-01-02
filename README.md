# LibCal Integration for Drupal (8/9)

## Introduction
This Drupal module provides a method to integrate LibCal Events to Drupal content by accessing and pulling data from LibCal Rest API. 

## Requirement

* Already established LibCal API setup with client ID and secret. For more information, please visit https://ask.springshare.com/libcal/faq/1407
* Drupal 8/9 install and setup. 

## Installation. 

* Dowload the source code of this repo to the module directory of your Drupal site. 
* Enable this module by Drupal interface or Drush command. 
* To start configuring the LibCal Rest API infomration by going to Configuration > System > LibCal or visit https://yoursite.com/admin/config/libcal, then fill out the setup form (Screenshot below)

![alt text](https://raw.githubusercontent.com/digitalutsc/drupal_libcal/dist/man_config.png "Configure LibCal Rest API")

## Usage

* When the module is enabled, the Event content type will be created, for more detail, please visit Structure > Content Types or visit http://yoursite.com/admin/structure/types/manage/event
* After complete setup LibCal API above, the download events will take place whenever your site's scheduled cron job run. To modify how often the download process run, please visit http://yoursite.com/admin/config/system/cron

