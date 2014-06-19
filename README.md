ErrorMonitoring
===============

Project for monitoring all errors and exceptions files across projects

## Requirements
* PHP >= 5.3
* MySQL >= 5.6

## Installation

1. Create folders ```log, temp, www_root/webtemp, temp/sessions``` in project root. If working on mac/linux run ```chmod 777 app/changelog && mkdir -m 777 log temp temp/cache www_root/webtemp temp/sessions```
2. Run ```composer install```
3. Create database, and run queries in changelog/init.sql
5. Configure project in app/config/config.local.neon
6. Visit ```localhost/changelog``` for additional database table installation

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

## Cron
### Import exception files
This function processes all exception files located in  **project-name/exception** folder. 
```
/cron/import/import-files
```
