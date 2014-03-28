# TIMBY Dashboard

This includes a custom wordpress backend for managing reports and a fronted for moderators to view reports.

# Installing

To install wordpress in this directory run the install script

    $ bash install.sh

## Configuration

The TIMBY dashboard comes with a wordpress theme and a custom build of the [wordpress json rest api plugin](http://wordpress.org/plugins/json-api/)

Sign in to wordpress admin dashboard and enable the Timby theme and json rest plugin. 

Go to Settings > JSON API and activate the **Posts** and **Users** controllers

### To do

Explain how api users are created