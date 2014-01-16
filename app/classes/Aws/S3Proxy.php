<?php

namespace HQ\Aws;

use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;


/**
 *  Proxy for Amazon storage web service (AWS S3)
 *
 *  @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 *  @see https://github.com/aws/aws-sdk-php
 *
 */
class S3Proxy extends \Nette\Object {

    /** Aws\S3\S3Client */
    private $s3Client;

    /** string bucket name used to store data */
    private $bucket;

    public function __construct($config)
    {
        // S3 CLIENT
        $this->s3Client = S3Client::factory(array(
            'key'    => $config['s3']['accessKeyId'],
            'secret' => $config['s3']['secretAccessKey'],
            'region' => $config['s3']['region']
        ));
        $this->bucket = $config['s3']['bucket'];

        // REGISTER STREAM WRAPPER
        $this->registerStreamWrapper();
    }

    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * Registers stream wrapper to be able use s3:// protocol
     * @return [type] [description]
     */
    public function registerStreamWrapper() {
        $this->s3Client->registerStreamWrapper();
    }

    /**
     * Uploads file to amazon s3
     * @param  string $sourcePath path name to file on local storage
     * @param  string $targetPath      path in which would be image saved in S3
     * @return                result
     */
    public function uploadFile($sourcePath, $targetPath, $publicAccess = true)
    {
        // check if the file does not already exists, if exist, don't upload
        if ($this->isFile($targetPath)) {
            return false;
        }
        return $this->s3Client->putObject(array(
            'Bucket'     => $this->bucket,
            'Key'        => $targetPath,
            'SourceFile' => $sourcePath,
            'ACL'        => $publicAccess ? CannedAcl::PUBLIC_READ : CannedAcl::PRIVATE_ACCESS
        ));
    }

    /**
     * Checks if file exists on s3 storage
     * @param  string  $filePath path to file on s3 storage
     * @return boolean
     */
    public function isFile($filePath)
    {
        if (substr($filePath, 0, 1) != '/') {
            $filePath = '/'.$filePath;
        }
        return is_file('s3://'.$this->bucket.$filePath);
    }

    /**
     * Downloads file from s3 and saves to local disk
     * @param  string $filePath   path to file on s3
     * @param  string $targetPath absolute path where to save file on local storage
     * @return bool
     */
    public function downloadFile($filePath, $targetPath)
    {
        $result = $this->s3Client->getObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $filePath,
            'SaveAs' => $targetPath
        ));

        return $result;
    }

    /**
     * Copies files on s3 storage
     * @param  string $origFilePath   The name of the source bucket and key name of the source object, separated by a slash (/). Must be URL-encoded
     * @param  string $targetFilePath new file path
     * @return Guzzle\Service\Resource\Model
     */
    public function copyFile($origFilePath, $targetFilePath, $publicAccess = true)
    {
        $origFilePath = str_replace('//', '/', $origFilePath);
        $targetFilePath = str_replace('//', '/', $targetFilePath);
        $result = $this->s3Client->copyObject(array(
            'Bucket' => $this->bucket,
            'CopySource' => $origFilePath,
            'Key'    => $targetFilePath,
            'ACL'    => $publicAccess ? CannedAcl::PUBLIC_READ : CannedAcl::PRIVATE_ACCESS
        ));

        return $result;
    }

    /**
     * List all files in bucket with given prefix
     * @param  string $prefix path on S3
     * @return array of items
     */
    public function getFiles($prefix = '')
    {
        return $this->getFilesIterator($prefix)->toArray();
    }

	/**
     * List all files in bucket with given prefix
     * @param  string $prefix path on S3
     * @return Iterator
     */
	public function getFilesIterator($prefix = '', $marker = '')
    {
        return $this->s3Client->getIterator('ListObjects', array(
            'Bucket' => $this->bucket,
			'Prefix' => $prefix,
			'Marker' => $marker
        ));
    }

    /**
     * Download all files and saves to local directory
     * Download only if file does not already exists
     * @param  string $localDir
     * @param  string $prefix   relative path to files on s3 storage
     * @return bool
     */
    public function downloadAllFiles($localDir, $prefix)
    {
        $files = $this->getFiles($prefix);

        foreach ($files as $key => $file) {
            if ($file['Size'] == 0) {
                continue;
            }
            $filename = basename($file['Key']);
            if (!is_file($localDir . $filename)) {
                $this->downloadFile($file['Key'], $localDir . $filename);
            }
        }

        return true;
    }

    public function deleteFile($filePath)
    {
        if (substr($filePath, 0, 1) != '/') {
            $filePath = '/'.$filePath;
        }
        $result = $this->s3Client->deleteObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $filePath
        ));

        return $result;
    }

	/**
	 * Returns the URL to an object identified by its key.
	 * If an expiration time is provided, the URL will be
	 * signed and set to expire at the provided time.
	 * @param Object key
	 * @param Expiration time
	 */
	public function getObjectUrl($key, $expires = null)
	{
		return $this->s3Client->getObjectUrl($this->bucket, $key, $expires);
	}
}

