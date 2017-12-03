<?
class CWebteam{
	
	public static $debug = true;
	public static $iterator = 0;
	public static $mail = 'kolonchuk@newsite.by';

	public static function print_pre(){

		if(!self::$debug){
			return;
		}

		foreach(func_get_args() as $arg){
			echo "<pre>";
			if(is_array($arg)) print_r($arg);
			else var_dump($arg);
			echo "</pre>";
		}
	}

	public static function print_str($text, $debug_mode = true){
		if($debug_mode){
			echo $text . PHP_EOL;
		}
	}

	public static function print_arr($arr, $debug_mode = true){
		if($debug_mode){
			print_r($arr);
		}
	}

	public static function log($text, $filename = false , $debug_mode = true, $flag = 'a'){

		if(!$debug_mode){
			return;
		}

		if(!$filename){
			$log_file = $_SERVER["DOCUMENT_ROOT"] . '/debug.txt';
		}else{
			$log_file = $_SERVER['DOCUMENT_ROOT'] . '/' . $filename;
		}

		fwrite(fopen($log_file , $flag),date('d.m.Y H:i:s') . ' ' . $text . "\r\n");
	}

	function log_to_file($arData, $file = '/debug.log'){
    
	    $content = "\n\n" . date('Y-m-d H:i:s') . "\n";
	    foreach ($arData as $k=>$v){
	        $sub = '';
	        if (is_array($v)) {
	            foreach ($v as $k2=>$v2)
	                $sub .= $k2 .' - '. $v2 . "\n";
	        }
	        else
	            $sub = $v;
	        $content .= $k . ' - ' . $sub . "\n";
	    }
	    $fn = $_SERVER["DOCUMENT_ROOT"].$file;
	    $fp = fopen($fn,"a") or die ("Error opening file in write mode!");
	    fputs($fp,$content);
	    fclose($fp) or die ("Error closing file!");
	}

	public static function send_mail($message){
		$sendTo = self::$mail;

		$headers = 'From: debug@bulbazubr.by' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		return mail($sendTo, 'report novatek.by', $message, $headers);
	}

	public static function file_transfer($file) {
		if (file_exists($file)) {
			if (ob_get_level())
				ob_end_clean();
			header('X-SendFile: ' . realpath($file));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($file));
			readfile($file);
			exit();
		}
	}


	public static function create_translit($field_name, $case = 'L'){

		if(empty($field_name)){
			return false;
		}

		$params = array(
			"max_len" => "100", // обрезает символьный код до 100 символов
			"change_case" => $case, // буквы преобразуются к регистру
			"replace_space" => "_", // меняем пробелы на нижнее подчеркивание
			"replace_other" => "_", // меняем левые символы на нижнее подчеркивание
			"delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
			"use_google" => "false", // отключаем использование google
		);
		return  CUtil::translit($field_name, "ru" , $params);
	}

	public  static function image_resize($params, $filePath) {

		$CACHE_IMG_PATH = $_SERVER["DOCUMENT_ROOT"] . '/upload/image_resize/';
		$RETURN_IMG_PATH = '/upload/image_resize/';
		$NO_IMAGE_SCR = '';

		$params["WIDTH"] = !isset($params["WIDTH"]) ? 100 : intval($params["WIDTH"]);
		$params["HEIGHT"] = !isset($params["HEIGHT"]) ? 100 : intval($params["HEIGHT"]);
		$params["MODE"] = !isset($params["MODE"]) ? 'in' : strtolower($params["MODE"]);

		$params["RESET"] = !empty($params["RESET"]);
		$params["QUALITY"] = (!isset($params["QUALITY"]) || intval($params["QUALITY"]) <= 0 ) ? 100 : $params["QUALITY"];
		$params["HIQUALITY"] = !isset($params["HIQUALITY"]) ? (($params["WIDTH"] <= 200 || $params["HEIGHT"] <= 200) ? 1 : 0 ) : 0;
		$params["SETWATERMARK"] = !empty($params["SETWATERMARK"]);

		$resetImage = !empty($params["RESET"]);

		unset($params["RESET"]);
		$pathToOriginalFile = "{$_SERVER["DOCUMENT_ROOT"]}/{$filePath}";

		$salt = md5(strtolower($filePath) . implode('_', $params));
		$salt = substr($salt, 0, 3) . '/' . substr($salt, 3, 3) . "/";

		$fileType = end(explode(".", basename($filePath)));
		$filename = md5(basename($filePath));
		$pathToFile = $salt . $filename . "." . $fileType;

		// если изображение существует
		if (is_file($CACHE_IMG_PATH . $pathToFile) == true) {
			$timeCreate = time() - filemtime($CACHE_IMG_PATH . $pathToFile);

			if ($_REQUEST["clear_cache_image"] == 'Y' || $resetImage || $timeCreate > (24 * 60 * 60) * 7 || !filesize($CACHE_IMG_PATH . $pathToFile)) { //при очистке кэша
				unlink($RETURN_IMG_PATH . $pathToFile);
			}
			else {
				return $RETURN_IMG_PATH . $pathToFile;
			}
		}


		if (!file_exists($pathToOriginalFile)) {
			$filePath = $NO_IMAGE_SCR;
			$pathToOriginalFile = "{$_SERVER["DOCUMENT_ROOT"]}/{$filePath}";
			$salt = md5(strtolower($filePath) . implode('_', $params));
			$salt = substr($salt, 0, 3) . '/' . substr($salt, 3, 3) . "/";
			$fileType = end(explode(".", basename($filePath)));
			$filename = md5(basename($filePath));
			$pathToFile = $salt . $filename . "." . $fileType;
			$params["MODE"] = "in";
			if (is_file($CACHE_IMG_PATH . $pathToFile) == true) {
				return $RETURN_IMG_PATH . $pathToFile;
			}
		}
		CheckDirPath($CACHE_IMG_PATH . $salt);

		$imgInfo = getImageSize($pathToOriginalFile);

		if (intval($params["WIDTH"]) == 0)
			$params["WIDTH"] = intval($params["HEIGHT"] / $imgInfo[1] * $imgInfo[0]);

		if (intval($params["HEIGHT"]) == 0)
			$params["HEIGHT"] = intval($params["WIDTH"] / $imgInfo[0] * $imgInfo[1]);


		//если вырезаться будет cut проверка размеров
		if (($params["WIDTH"] > $imgInfo[0] || $params["HEIGHT"] > $imgInfo[1]) && ($params["MODE"] != "in" && $params["MODE"] != "inv")) {
			$params["WIDTH"] = $imgInfo[0];
			$params["HEIGHT"] = $imgInfo[1];
		}

		if (!($imgInfo[0] == $params["WIDTH"] && $imgInfo[1] == $params["HEIGHT"]) || $params["SETWATERMARK"]) {

			$im = ImageCreateTrueColor($params["WIDTH"], $params["HEIGHT"]);

			imageAlphaBlending($im, false);

			switch (strtolower($imgInfo["mime"])) {
				case 'image/gif' :

					$params["HIQUALITY"] = false;
					$black = imagecolortransparent($im, imagecolorallocatealpha($im, 0, 0, 0, 127));
					imagesavealpha($im, true);
					imagefilledrectangle($im, 0, 0, $params["WIDTH"], $params["HEIGHT"], $black);
					$i0 = ImageCreateFromGif($pathToOriginalFile);
					break;
				case 'image/jpeg' : case 'image/pjpeg' :
				$icolor = ImageColorAllocate($im, 255, 255, 255);
				imagefill($im, 0, 0, $icolor);
				$i0 = ImageCreateFromJpeg($pathToOriginalFile);
				break;
				case 'image/png' :
					$params["HIQUALITY"] = false;
					$black = imagecolortransparent($im, imagecolorallocatealpha($im, 0, 0, 0, 127));
					imagesavealpha($im, true);
					imagefilledrectangle($im, 0, 0, $params["WIDTH"], $params["HEIGHT"], $black);
					$i0 = ImageCreateFromPng($pathToOriginalFile);
					break;
				default :
					return;
			}

			switch (strtolower($params["MODE"])) {
				case 'cut' :
					$k_x = $imgInfo[0] / $params["WIDTH"];
					$k_y = $imgInfo[1] / $params["HEIGHT"];
					if ($k_x > $k_y)
						$k = $k_y;
					else
						$k = $k_x;
					$pn["WIDTH"] = $imgInfo[0] / $k;
					$pn["HEIGHT"] = $imgInfo[1] / $k;
					$x = ($params["WIDTH"] - $pn["WIDTH"]) / 2;
					$y = ($params["HEIGHT"] - $pn["HEIGHT"]) / 2;


					imageCopyResampled($im, $i0, $x, $y, 0, 0, $pn["WIDTH"], $pn["HEIGHT"], $imgInfo[0], $imgInfo[1]);
					break;

				//вписана в квадрат без маштабирования (картинка может быть увеличена больше своего размера)
				case 'in' :

					if (($imgInfo[0] < $params["WIDTH"]) && ($imgInfo[1] < $params["HEIGHT"])) {
						$k_x = 1;
						$k_y = 1;
					}
					else {
						$k_x = $imgInfo[0] / $params["WIDTH"];
						$k_y = $imgInfo[1] / $params["HEIGHT"];
					}

					if ($k_x < $k_y)
						$k = $k_y;
					else
						$k = $k_x;

					$pn["WIDTH"] = $imgInfo[0] / $k;
					$pn["HEIGHT"] = $imgInfo[1] / $k;

					$x = ($params["WIDTH"] - $pn["WIDTH"]) / 2;
					$y = ($params["HEIGHT"] - $pn["HEIGHT"]) / 2;

					imageCopyResampled($im, $i0, $x, $y, 0, 0, $pn["WIDTH"], $pn["HEIGHT"], $imgInfo[0], $imgInfo[1]);


					// 1 первый параметр изборажение источник
					// 2 изображение которое вставляется
					// 3 4 -х и у с какой точки будет вставятся в изображении источник
					// 5 6 - ширина и высота куда будет вписано изображение

					break;
				default : imageCopyResampled($im, $i0, 0, 0, 0, 0, $params["WIDTH"], $params["HEIGHT"], $imgInfo[0], $imgInfo[1]);
					break;
			}


			if ($params["HIQUALITY"]) {
				$sharpenMatrix = array
				(
					array(-1.2, -1, -1.2),
					array(-1, 20, -1),
					array(-1.2, -1, -1.2)
				);
				// calculate the sharpen divisor
				$divisor = array_sum(array_map('array_sum', $sharpenMatrix));
				$offset = 0;
				// apply the matrix
				imageconvolution($im, $sharpenMatrix, $divisor, $offset);
			}


			$params["WATERMARK_PATH"] = $_SERVER["DOCUMENT_ROOT"] . (empty($params["WATERMARK_PATH"]) ? "/img/watermark.png" : $params["WATERMARK_PATH"]);

			if ($params["SETWATERMARK"] && file_exists($params["WATERMARK_PATH"])) {
				imageAlphaBlending($im, true);

				$params["WATERMARK_POSITION"] = empty($params["WATERMARK_POSITION"]) || abs($params["WATERMARK_POSITION"]) > 9 ? 5 : abs($params["WATERMARK_POSITION"]);


				list($widthWater, $heightWater) = getimagesize($params["WATERMARK_PATH"]);

				if ($params["WIDTH"] < $widthWater || $params["HEIGHT"] < $heightWater) {


					//ресайз по ширине
					$waterW = intval($params["WIDTH"] * 0.8);

					if ($waterW > $widthWater)
						$waterW = $widthWater;

					$waterH = intval($waterW / $widthWater * $heightWater);

					$waterMarkResize = array("HEIGHT" => $waterH, "WIDTH" => $waterW);

					if ($waterH > $params["HEIGHT"]) {
						$waterH = intval($params["HEIGHT"] * 0.8);
						$waterW = intval($waterH / $heightWater * $widthWater);
						$waterMarkResize = array("HEIGHT" => $waterH, "WIDTH" => $waterW);
					}

					if (strpos($params["WATERMARK_PATH"], $_SERVER["DOCUMENT_ROOT"]) === 0)
						$params["WATERMARK_PATH"] = substr($params["WATERMARK_PATH"], strlen($_SERVER["DOCUMENT_ROOT"]));

					if (!empty($params["RESET"]))
						$waterMarkResize["RESET"] = $params["RESET"];

					$params["WATERMARK_PATH"] = $_SERVER["DOCUMENT_ROOT"] . imageResize($waterMarkResize, $params["WATERMARK_PATH"]);
					list($widthWater, $heightWater) = getimagesize($params["WATERMARK_PATH"]);
				}

				$waterMark = ImageCreateFromPng($params["WATERMARK_PATH"]);

				$waterTop = $waterLeft = 0;
				if (in_array($params["WATERMARK_POSITION"], array(4, 5, 6)))
					$waterTop = intval($params["HEIGHT"] / 2) - intval($heightWater / 2);
				if (in_array($params["WATERMARK_POSITION"], array(7, 8, 9)))
					$waterTop = $params["HEIGHT"] - $heightWater;
				if (in_array($params["WATERMARK_POSITION"], array(2, 5, 8)))
					$waterLeft = intval($params["WIDTH"] / 2) - intval($widthWater / 2);
				if (in_array($params["WATERMARK_POSITION"], array(3, 6, 9)))
					$waterLeft = $params["WIDTH"] - $widthWater;

				$widthWater--;
				$heightWater--;
				imageCopyResampled($im, $waterMark, $waterLeft, $waterTop, 0, 0, $widthWater, $heightWater, $widthWater, $heightWater);
			}



			switch (strtolower($imgInfo["mime"])) {
				case 'image/gif' :
					@imageGif($im, $CACHE_IMG_PATH . $pathToFile);
					break;
				case 'image/jpeg' : case 'image/pjpeg' :@imageJpeg($im, $CACHE_IMG_PATH . $pathToFile, $params["QUALITY"]);
				break;
				case 'image/png' :
					@imagePng($im, $CACHE_IMG_PATH . $pathToFile);
					break;
			}

			imagedestroy($i0);
			imagedestroy($im);
		}
		else {
			copy($pathToOriginalFile, $CACHE_IMG_PATH . $pathToFile);
		}

		return $RETURN_IMG_PATH . $pathToFile;
	}

	public static function get_user_by_email($email){
	    $ar_user = [];
	    if(!empty($email)){
            $email = htmlspecialchars(trim($email));
            $filter = ['=EMAIL' => $email];
            $ar_user = \CUser::GetList($by = 'ID', $order = 'ASC', $filter)->Fetch();
        }
        return $ar_user;
    }

    public static function rand_string($pass_len = 10, $pass_chars = false){

        $allchars = "abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789";
        $string = "";

        if($pass_chars !== false) {
            $chars = $pass_chars;
        } else {
            $chars = $allchars;
        }

        $n = strlen($chars) - 1;

        for ($i = 0; $i < $pass_len; $i++){
            $string .= $chars[mt_rand(0, $n)];
        }

        return $string;
    }

    public static function user_pass_upd($user_id, $pass){
        $result = false;
        if((int)$user_id > 0 && strlen($pass) > 5){
            $user_obj = new \CUser();
            $upd_fields = [
                'PASSWORD' => $pass,
                'CONFIRM_PASSWORD' => $pass,
            ];
            $result = $user_obj->Update($user_id, $upd_fields);
        }
        return $result;
    }

    // получает значение страны по ее id в файле конфигураторе, исходя из языковой версии

	public static function get_country_by_id($country_id){
	    $country = '';
	    if((int)$country_id > 0){
	        $ar_countries = GetCountryArray(LANGUAGE_ID);
	        $ar_countries['reference_id'] = array_flip($ar_countries['reference_id']);
	        $reference_id =  $ar_countries['reference_id'][$country_id];
	        $country = $ar_countries['reference'][$reference_id];
	    }
	    return $country;
	}
	
}