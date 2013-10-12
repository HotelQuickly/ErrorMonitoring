<?php

use \Nette\Utils\Strings;
use Hotelquickly\InvalidArgumentException;

/**
 * Abstract class for template helpers
 *
 * @author     Petr Heinz
 * @package    Cedulart.cz
 */
abstract class Helpers
{

	/**
	 * HelperLoader
	 */
	public static function loader($helper)
	{
		$callback = callback(__CLASS__, $helper);
		if ($callback->isCallable()) {
			return $callback;
		}
	}

	public static function numberFormat($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',') {
		return number_format($number, $decimals, $dec_point, $thousands_sep);
	}

	public static function getTextStringsFromTemplate($filename) {

		// /[\"'](.*)[\"']/
		// /[\{][\!]{0,1}[_]?[\"'](.*)[\"'][\}]/
		$pattern = "/[\{][\!]{0,1}[_]?[\"'](.*)[\"'](.*)[\}]/smU";
		$emailTemplateContent = file_get_contents($filename);
		preg_match_all($pattern, (string) $emailTemplateContent, $matches, PREG_PATTERN_ORDER);

		$textStrings = array();
		foreach($matches[1] as $key => $value) {
			$textStrings[md5($value)] = $value;
		}

		return $textStrings;
	}

	public static function uppercase($str) {
		return strtoupper($str);
	}

	public static function formatCardNumber($cardNumber) {
		if (empty($cardNumber)) {
			return false;
		}
		$newCardNumber = substr($cardNumber, 0, 4)
			. '&nbsp;' . substr($cardNumber, 4, 4)
			. '&nbsp;' . substr($cardNumber, 8, 4)
			. '&nbsp;' . substr($cardNumber, 12, 4);

		return $newCardNumber;
	}

	public static function maskCardNumber($cardNumber) {
		if (empty($cardNumber)) {
			return false;
		}
		$maskedPart = '';
		$stringToMask = substr($cardNumber, 4, -4);
		for ( $i=0 ; $i<strlen($stringToMask) ; $i++ ) {
			$maskedPart .= is_numeric($stringToMask{$i})? '*' : $stringToMask{$i};
		}
		return substr($cardNumber, 0, 4) . $maskedPart . substr($cardNumber, -4);
	}

	public static function maskVoucherCode($voucherCode, $user = null) {
		if (empty($voucherCode)) {
			return false;
		}

		// Maybe this hould not be hardcoded?!
		$knownVouchers = array("QUICKLY", "FRIEND", "INVITE");
		if (in_array($voucherCode, $knownVouchers)) {
			return $voucherCode;
		}

		$maskedCode = substr($voucherCode, 0, 1) . str_repeat('*', strlen($voucherCode)-2) . substr($voucherCode, -1);

		if (!is_null($user) && $user instanceof Nette\Security\User && $user->isAllowed("Backend:Admin:UnmaskedVouchers", "show")) {
			return Nette\Utils\Html::el("abbr")
				->rel("tooltip")
				->title($voucherCode)
				->setText($maskedCode);
		}
		return $maskedCode;
	}

	public static function currencySign($value, $currencySignBefore, $currencySignAfter)
	{
		return $currencySignBefore.$value.$currencySignAfter;
	}

	public static function strtolower($val) {
		return strtolower($val);
	}

	public static function nvl($val1, $val2 = null) {
		if (empty($val1)) {
			if ($val2 === null) {
				return 'N/A';
			} else {
				return $val2;
			}
		} else {
			return $val1;
		}
	}

	/**
	 * Makes first letter in string uppercase
	 * @param type $string
	 * @return type
	 */
	public static function ucfirst($string)
	{
		return ucfirst($string);
	}
	public static function ucwords($string)
	{
		return ucwords($string);
	}

	/**
	 * Resizes image and saves it with suffix '-w$w-h$h'
	 * @param string image (filename)
	 * @param string path
	 * @param int w (width)
	 * @param int h (height)
	 * @return string name of newly created file
	 */
	public static function resizeImage($filename, $path, $w, $h=null, $enlarge=false)
	{
		if (empty($filename)||!file_exists($path.$filename)) {
			return false;
		}
		$newFilename = $filename.'-w'.(is_null($w)?'-null':$w).'-h'.(is_null($h)?'-null':$h).'.jpg';
		if(!file_exists($path.$newFilename)){
			$img = Nette\Image::fromFile($path.$filename);
			$originalWidth = $img->getWidth();
			$img->resize($w, $h, $enlarge ? Nette\Image::ENLARGE : 0);
			if($originalWidth > $w) $img->sharpen();
			$img->save($path.$newFilename, 85, Nette\Image::JPEG);
		}
		return $newFilename;
	}

	public static function isGoodResolution($photo, $minRes) {
		foreach ($minRes as $minResolution) {
			if ((int) ($photo->orig_width) >= (int) ($minResolution['width']) AND (int) ($photo->orig_height) >= (int) ($minResolution['height']) ) {
				return true;
			}
		}

		return false;
	}

	public static function showMinResolutionAlert($photo, $minRes) {
		foreach ($minRes as $minResolution) {
			if ((int) $photo->orig_width >= (int) $minResolution['width'] AND (int) $photo->orig_height >= (int) $minResolution['height']) {
				return '';
			}
		}

		return '<label class="badge badge-important" style="display: inline; padding: 0px 5px; line-height: 0.8em;">'.'small'.'</label>';
	}

	public static function showRejectionStatus($photo) {
		if ($photo->photo_status->name == 'REJECTED') {
			return '<label title="Photo was rejected" class="badge badge-important" style="display: inline; padding: 0px 5px; line-height: 0.8em;">'.'rejected'.'</label>';
		} else if ($photo->photo_status->name == 'APPROVED') {
			return '<label title="Photo was approved" class="badge badge-success" style="display: inline; padding: 0px 5px; line-height: 0.8em;">'.'approved'.'</label>';
		} else  {
			return '<label title="Waiting for approval" class="badge badge-warning" style="display: inline; padding: 0px 5px; line-height: 0.8em;">'.'waiting'.'</label>';
		}
	}

	public static function showResolutionInfo($photo, $minRes) {
		foreach ($minRes as $minResolution) {
			if ((int) $photo->orig_width >= (int) $minResolution['width'] AND (int) $photo->orig_height >= (int) $minResolution['height']) {
				return '<label class="badge badge-success" style="display: inline; padding: 0px 5px; line-height: 0.8em;">'.'OK'.'</label>';
			}
		}

		return '<label class="badge badge-important" style="display: inline; padding: 0px 5px; line-height: 0.8em;">'.'small'.'</label>';
	}

	/**
	 * Gets nicer date format
	 * @param mixed date (MySQL DateTime or timestamp)
	 * @param bool withoutTime (returns only date without time)
	 * @return string
	 */
	public static function formatDate($date, $withoutTime = false)
	{
		if(!is_int($date)) $date = strtotime($date);
		switch(Nette\Environment::getVariable('lang')){
			case('cs'):
				if ($withoutTime) {
					return date('j.n.Y',$date);
				}
				return date('j.n.Y G:i',$date);
			case('en'):
				if ($withoutTime) {
					return date('j.n.Y',$date);
				}
				return date('j.n.Y G:i',$date);
			default:
				if ($withoutTime) {
					return date('j.n.Y',$date);
				}
				return date('Y/m/d H:i',$date);
		}
	}

	public static function formatDateWithoutTime($date)
	{
		return self::formatDate($date, true);
	}

	/**
	 * Returns time in words
	 * @todo Multilanguage
	 * @param timestamp $time
	 */
	public static function timeAgoInWords($time)
	{
        if (!$time) {
            return FALSE;
        } elseif (is_numeric($time)) {
            $time = (int) $time;
        } elseif ($time instanceof DateTime) {
            $time = $time->format('U');
        } else {
            $time = strtotime($time);
        }
        $delta = time() - $time;

        if ($delta < 0) {
            $delta = round(abs($delta) / 60);
            if ($delta == 0) return 'za okamžik';
            if ($delta == 1) return 'za minutu';
            if ($delta < 45) return 'za ' . $delta . ' ' . self::plural($delta, 'minuta', 'minuty', 'minut');
            if ($delta < 90) return 'za hodinu';
            if ($delta < 1440) return 'za ' . round($delta / 60) . ' ' . self::plural(round($delta / 60), 'hodina', 'hodiny', 'hodin');
            if ($delta < 2880) return 'zítra';
            if ($delta < 43200) return 'za ' . round($delta / 1440) . ' ' . self::plural(round($delta / 1440), 'den', 'dny', 'dní');
            if ($delta < 86400) return 'za měsíc';
            if ($delta < 525960) return 'za ' . round($delta / 43200) . ' ' . self::plural(round($delta / 43200), 'měsíc', 'měsíce', 'měsíců');
            if ($delta < 1051920) return 'za rok';
            return 'za ' . round($delta / 525960) . ' ' . self::plural(round($delta / 525960), 'rok', 'roky', 'let');
        }

        $delta = round($delta / 60);
        if ($delta == 0) return 'před okamžikem';
        if ($delta == 1) return 'před minutou';
        if ($delta < 45) return "před $delta minutami";
        if ($delta < 90) return 'před hodinou';
        if ($delta < 1440) return 'před ' . round($delta / 60) . ' hodinami';
        if ($delta < 2880) return 'včera';
        if ($delta < 43200) return 'před ' . round($delta / 1440) . ' dny';
        if ($delta < 86400) return 'před měsícem';
        if ($delta < 525960) return 'před ' . round($delta / 43200) . ' měsíci';
        if ($delta < 1051920) return 'před rokem';
        return 'před ' . round($delta / 525960) . ' lety';
    }

	public static function timeDiff($time){
        if (!$time) {
            return FALSE;
        } elseif (is_numeric($time)) {
            $time = (int) $time;
        } elseif ($time instanceof DateTime) {
            $time = $time->format('U');
        } else {
            $time = strtotime($time);
        }

		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60","60","24","7","4.35","12","10");

		$now = time();

		$isFuture = ($time > $now)? true : false;
		$difference = abs($now - $time);

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		 }

		 $difference = round($difference);

		 if($difference != 1) {
			$periods[$j].= "s";
		 }

		return ($isFuture)? "in $difference $periods[$j]" : "$difference $periods[$j] ago";


	}

    /**
     * Plural: three forms, special cases for 1 and 2, 3, 4.
     * (Slavic family: Slovak, Czech)
     * @param  int
     * @return mixed
     */
    public static function plural($n)
	{
        $args = func_get_args();
        return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
    }

	public static function truncateHtml($text, $length = 150, $ending = '...', $exact = false, $considerHtml = false)
	{
		if ($considerHtml) {
			if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}

			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';

			foreach ($lines as $line_matchings) {
				if (!empty($line_matchings[1])) {
					if (preg_match('/^<(s*.+?/s*|s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(s.+?)?)>$/is', $line_matchings[1])) {
					} else if (preg_match('/^<s*/([^s]+?)s*>$/s', $line_matchings[1], $tag_matchings)) {
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
					} else if (preg_match('/^<s*([^s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					$truncate .= $line_matchings[1];
				}
				$content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length > $length) {
					$left = $length - $total_length;
					$entities_length = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}
					$truncate .= mb_substr($line_matchings[2], 0, $left+$entities_length);
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				if($total_length >= $length) {
					break;
				}
			}
		} else {
			if (mb_strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = mb_substr($text, 0, $length - mb_strlen($ending));
			}
		}
		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if (isset($spacepos)) {
					$truncate = mb_substr($truncate, 0, $spacepos);
			}
		}
		$truncate .= $ending;
		if($considerHtml) {
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}

	public static function getProfilePicture($userId, $thumb = false, $dimensions = '', $proporcial = false) {
		return '/n/user/photo/' . $userId . '?thumb=' . ($thumb == true ? 1 : 0)
			. '&dimensions=' . $dimensions
			. '&proporcial=' . ($proporcial == true ? 1 : 0);
	}

    public static function count($array) {
        return count($array);
    }

	public static function getAgeInYears($date) {
        $datetime1 = new DateTime($date);
        $datetime2 = new DateTime();
        $interval = $datetime1->diff($datetime2);

        return $interval->format('%y');
	}

    public static function createUrlAlias($string) {
        $string = Helpers::removeDiacritics($string);

        $search = array( ":", '"',  "+",  "(",  ")", "!", "?", '%', '&', '#', '@', '$', '^', '*', '=');
        $replace = array("",  "",   "",   "",   "",  "",  "", '', '', '', '', '', '', '', '-');
        $string = str_replace($search, $replace, $string);

        $string = trim($string);

        // Obcas sa stane, ze je url "Tati,co je to rasismus" -- tzn. je nutne pridat medzeru za carku a az potom ju replacnut pomlckou
        $search = array( ",",  ",",  " ", "/",  ".",  "--", "---");
        $replace = array(", ", "-",  "-", "-",  "-",  "-",  "-" );
        $string = str_replace($search, $replace, $string);

        // obcas sa stane, ze su dve pomlcky vedla seba, zrusit
        $search = array( "--");
        $replace = array("-" );
        $string = str_replace($search, $replace, $string);

        // na zaver otrimovat pomlcky na konci URL
        $string = trim($string, "-");

        return $string;
    }

    public static function removeDiacritics($string) {
        $search = explode( ",",  "',á,é,í,ó,ú,à,è,ì,ò,ù,ů,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,ř,ĺ,ľ,ť,š,ď,ý,ž,č,ň,ě,ô,ä,Á,É,Í,Ó,Ú,Ů,Ä,Ô,Ř,Ĺ,Ľ,Ť,Š,Ď,Ý,Ž,Č,Ň,Ě");
        $replace = explode(",",  " ,a,e,i,o,u,a,e,i,o,u,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,r,l,l,t,s,d,y,z,c,n,e,o,a,A,E,I,O,U,U,A,O,R,L,L,T,S,D,Y,Z,C,N,E");
        return str_replace($search, $replace, $string);
    }

    public static function generatePassword($length=8, $strength=8) {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        if ($strength & 1) {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($strength & 2) {
            $vowels .= "AEUY";
        }
        if ($strength & 4) {
            $consonants .= '23456789';
        }
        if ($strength & 8) {
            $consonants .= '@#$%';
        }

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }

    public static function makeUrlInText($text, $shorten = 100) {
		$ret = preg_replace(
			'#(http://|https://|ftp://|(www\.))([\w\-]*\.[\w\-\.]*([/?][^\s]*)?)#e',
			"'<a href=\"'.('\\1'=='www.'?'http://':'\\1').'\\2\\3\">'.((mb_strlen('\\2\\3')>".$shorten.")?(mb_substr('\\2\\3',0,".$shorten.").'&hellip;'):'\\2\\3').'</a>'",
			$text
		);

		return $ret;
    }

    public static function webalize($str) {
        return Strings::webalize($str);
    }

    public static function truncate($text, $length = 10, $ending = '…') {
    	$truncate = '';

		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
			$truncate = trim($truncate);
			$truncate .= $ending;
		}

		return $truncate;
    }

    public static function round($value, $precision = 0) {
    	return round($value, $precision);
    }

    public static function floor($value) {
    	return floor($value);
    }
    public static function ceil($value) {
    	return ceil($value);
    }

    /**
     * Count absolute value
     * @param  int $value
     * @return int
     */
   	public static function abs($value)
   	{
   		return abs($value);
   	}

	/**
	 * Check input string if contain other than a-z, A-Z, 0-9 and space
	 *
	 * @param string $input
	 *
	 * @return boolean
	 */
	public static function isContainSpecialCharacter($input) {
		if(!is_string($input)) throw new InvalidArgumentException('$input is not string: ' . gettype($input));
		return preg_match('/[^a-zA-Z0-9]/', $input) || false;
	}

}