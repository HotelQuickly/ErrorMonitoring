ErrorMonitoring
===============

Project for monitoring all errors and exceptions files across projects

## AWS S3 Config
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