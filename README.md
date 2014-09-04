ErrorMonitoring
===============

Project for monitoring all errors and exceptions files across projects

## Requirements
* PHP >= 5.3
* MySQL >= 5.6

## Installation

1. Clone this repository to your local storage
2. Create folders ```log, temp, www_root/webtemp, temp/sessions``` in project root. If working on mac/linux run ```chmod 777 app/changelog && mkdir -m 777 log temp temp/cache www_root/webtemp temp/sessions```
3. Run ```composer install```
4. Create database, and run queries in app/changelog/init.sql
5. Configure database, AWS access keys, hipchat API key and others if neccessary in app/config/config.local.neon (use config.local.template.neon as template)
6. Visit ```localhost/error-monitoring/changelog``` (or appropriate name, depends how do you use your localhost) for additional database table installation

## Storage for exceptions
Currently only implementation is AWS S3 storage. Feel free to implement another storage and send pull request.

## Usage
Exceptions are uploaded to AWS S3 storage from other projects using [ErrorCollector](https://github.com/hotelquickly/errorCollector).

When you add ErrorCollector to new project, you need to first hit Scan for Projects button to find new project.
Button Load exceptions will download all not-yet-downloaded exceptions from all projects.

Configure cron if you want to automatically download exceptions.

```
/cron/import/import-files
```

## AWS S3 Config

Place this in config.local.neon

```
parameters:
        aws:
		    s3:
			    accessKeyId: 
			    secretAccessKey:
			    bucket:
			    region:
```
## AWS S3 directory structure
Exception files from your projects must be stored in this directory structure.
```
<project-name>/exception/<file-name>.html
```
All files in exception folder will be parsed and moved to archive folder.
```
<project-name>/archive/exception/<file-name>.html
```

