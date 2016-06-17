PhpStalkerTrap
==========

PhpStalkerTrap is a small script intended to catch someone's information such as IP, browser, operating system etc. It was designed to collect information from a stalker who was ripping the identity of someone else.

## Features

It mimics any web page, but when someone visits it, it triggers a script that sends data it can grab to an email address of your choice, and also logs the data.
Currently it can collect the IP address of the visitor, the user agent of its browser, the language it's set and the referer.

## Requirements

It requires PHP with Curl extension, and a configured mail() function.

## Installation

 1. Clone the repository using Git in the modules directory :  
 `git clone https://github.com/crachecode/PhpStalkerTrap.git`

 2. Access to config.php and set it up.

 3. Send the link to your stalker and maximise the chances he clicks it.
