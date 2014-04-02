# TIMBY Web Platform

The TIMBY platform enables reporters to submit audio visual reports via a mobile phone to a remote server anonymously.

These reports are then recieved by moderators who are responsible for screening each report and establishing its authenticity.

Verified reports can then be visualized on a map.

Moderators can then compose stories involving related reports and publish them to the world for viewing or invite journalists to preview.

Core parts of this platform

* API
* Report Moderation
* Visual Representation of Reports
* Story Maker


## Getting Started

### Prerequisites

  * PHP 5.3 >
  * [Composer](http://getcomposer.org)
  * [EXIF tool](http://www.sno.phy.queensu.ca/~phil/exiftool) 

### Installation

### Configuration

Install grunt on the command line using 
```bash
npm install -g grunt-cli
```
Install all plugins
```bash
npm install
```

Run to watch for sass and js changes
```bash
grunt
```
You need to install SASS on the command line. On a mac this works fine
```bash
gem install sass
```

If using XAMPP or MAMP you might have to change ownership to give it permission to write in various folders.
```bash
chown 777 -R <name of directory>
```

Bower. This gives you
- [Typeplate](https://github.com/typeplate/typeplate.github.io)
- [Font Awesome](http://fortawesome.github.io/Font-Awesome/icons/)
- Angular (plus maps, checkbox and file upload helpers)
- Angular Sanitize
- Angular Route
- [TextAngular](https://github.com/fraywing/textAngular)
- Bourbon
- Neat
- RequireJS
- Chosen (multi-select)
- Underscore

```
bower install
```



## Documentation

### API Docs

### Platform Docs

