<?php

use \Nette\Object,
	\Nette\Caching\Cache,
	\Nette\DI\Container,
	\Nette\Image,
	\Nette\Utils\Strings,
	\Nette\Utils\Html,
	\Nette\InvalidStateException;

/**
 * Image helper with automatic image resize.
 * Uses amazon s3 service
 *
 *
 * @author Roman Ozana, ozana@omdesign.cz
 * @link www.omdesign.cz
 *
 * Updated for new Nette 2.0 and for PHP 5.3+ by Martin Štekl
 * @author Martin Štekl <martin.stekl@gmail.com>
 * @link www.steky.cz
 * @license MIT
 *
 * Updated to use amazon s3 storage
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 *
 * in template
 * {= 'media/image.jpg'|resize:40}
 * {= 'media/image.jpg'|resize:?x20}
 * {= 'media/image.jpg'|resize:40:'alt'}
 * {= 'media/image.jpg'|resize:40:'alt':'title'}
 *
 */
class ImageHelper extends Object {

	/**
	 * example: http://www.leyter.com/
	 * @var string
	 */
	private $baseUrl;

	/**
	 * @var string
	 */
	private $tempDir;

    /** @var \HotelQuickly\AwsService */
    private $awsService;

    /** @var \HotelQuickly\Logger */
    private $logger;

	public function __construct($baseUrl, $tempDir, \HotelQuickly\AwsService $awsService, \HotelQuickly\Logger $logger) {
		$this->awsService = $awsService;
		$this->tempDir = $tempDir.'/';
		$this->baseUrl = $baseUrl;
		$this->logger = $logger;
	}

	/**
	 * Resizes given image to given dimensions
	 * @param  string  $imagePath   path name of file to resize => path on s3 server
	 * @param  string  $dimensions 120x30
	 * @param  boolean $cutToFit   if image can be cut to fit given dimensions
	 * @param  array   $data       html tags data such as title, class, ...
	 * @return \Nette\Utils\Html   html img element
	 */
	public function resizeImg($imagePath, $dimensions = '120x90', $cutToFit = false, $data = array())
	{
		$alt = isset($data['alt']) ? $data['alt'] : null;
		$title = isset($data['title']) ? $data['title'] : null;
		$class = isset($data['class']) ? $data['class'] : null;
        $setHeight = isset($data['setHeight']) ? $data['setHeight'] : true;
        $setWidth = isset($data['setWidth']) ? $data['setWidth'] : true;

		$info = pathinfo($imagePath);
		//$this->tempDir = $info['dirname'].'/';
		$title = !is_null($alt) && is_null($title) ? $alt : $title;
		$alt = is_null($alt) ? basename($imagePath, '.' . $info['extension']) : $alt;

		list($src, $width, $height) = $this->resizeAndSaveImage($imagePath, $dimensions, $cutToFit);

        $element = Html::el('img')->src($src)->alt($alt)->title($title)->class($class);

        if ($setHeight == true) {
            $element->height($height);
        }
        if ($setWidth == true) {
            $element->width($width);
        }
		return $element;
	}

	/**
	 * Same as $this->resizeImg but returns only src, not html tag
	 * @param  string  $imagePath   path name of file to resize => path on s3 server
	 * @param  string  $dimensions 120x30
	 * @param  boolean $cutToFit   if image can be cut to fit given dimensions
	 * @param  array   $data       settings
	 * @return string              src of image
	 */
	public function getResizedImgSrc($imagePath, $dimensions = '120x90', $cutToFit = false, $data = array())
	{
		$info = pathinfo($imagePath);

		list($src, $width, $height) = $this->resizeAndSaveImage($imagePath, $dimensions, $cutToFit);
        if (isset($data['returnWithDimensions'])) {
	        return array($src, $width, $height);
        }
		return $src;
	}

	public function resizeAndSaveImage($imagePath, $dimensions, $cutToFit = false)
	{
		// first check on s3 service
		if (!$this->awsService->isFile($imagePath)) {
			return array($imagePath, null, null);
		}

		$info = pathinfo($imagePath);

		$imageDir = $info['dirname'].'/';
		$imageName = basename($imagePath);

		////////////////////////////////////////
		// read dimensions
		////////////////////////////////////////
		$dim = explode('x', $dimensions);
		$newWidth = isset($dim[0]) ? $dim[0] : null;
		$newHeight = isset($dim[1]) ? $dim[1] : null;
		try {
			$resizedImageName = Strings::webalize(basename($imagePath, '.' . $info['extension']))
				. '-' . $newWidth
				. 'x'
				. $newHeight
				. '.'
				. $info['extension'];
			$resizedImagePath = $imageDir . $resizedImageName;

			// check if resized image is already on s3, if yes, return, if not, generate and save
			if ($this->awsService->isFile($resizedImagePath)) {
				$resizedImagePath = $this->baseUrl . $resizedImagePath;
				$result = array($resizedImagePath, $newWidth, $newHeight);
				return $result;
			}

			// need to download the original image and convert the sizes
			$tempImagePath = $this->tempDir . $imageName;

			// download image from s3 to temporary directory
			$this->awsService->downloadFile($imagePath, $tempImagePath);

			// load and resize image
			$image = Image::fromFile($tempImagePath);
			if ($cutToFit) {
				if ($image->height > $image->width) {
					$image->resize($newWidth, $newHeight, Image::FILL);
					$image->sharpen();
					$blank = Image::fromBlank($newWidth, $newHeight, Image::rgb(255, 255, 255));
					$blank->place($image, 0, '20%');
					$image = $blank;
				} else {
					$image->resize($newWidth, $newHeight, Image::FILL | Image::EXACT);
					$image->sharpen();
				}
			} else {
				$image->resize((int) $newWidth, (int) $newHeight, Image::SHRINK_ONLY);
				$image->sharpen();
			}

			// generate new image
			$image->save($tempImagePath, 85); // 85% quality
			// upload file to s3 storage
			$this->awsService->uploadFile($tempImagePath, $resizedImagePath);

			$result = array($this->baseUrl . $resizedImagePath, $image->width, $image->height);

			// free memory
			$image = null;
			unset($image);
			// remove temporary file
			if(file_exists($tempImagePath)) unlink($tempImagePath);
			return $result;
		} catch (\Exception $e) {
			//$this->logger->logError($e);
			return array($this->baseUrl . $imagePath, null, null);
		}

	}

}
