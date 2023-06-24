<?php 
namespace Docbox\utils;

use Docbox\model\DbConnection;

define("MB", 1048576);
define("MAX_UPLOAD_MB", 45);
/**
 *
 * @param string $name Nome da variável
 * @param string $type int ou str
 * @param string $arr get ou post
 * @param DbConnection $db
 */
function getReqParam($name, $type, $arr) {
	if(strcmp($type, "int") == 0) {
		if(strcmp($arr, "get") == 0) {
			return isset($_GET[$name]) ? intval($_GET[$name]) : 0;
		} else if(strcmp($arr, "post") == 0) {
			return isset($_POST[$name]) ? intval($_POST[$name]) : 0;
		}
	} else if(strcmp($type, "str") == 0) {
		$str = "";

		if(strcmp($arr, "get") == 0) {
			$str = isset($_GET[$name]) ? $_GET[$name] : "";
		} else if(strcmp($arr, "post") == 0) {
			$str = isset($_POST[$name]) ? $_POST[$name] : "";
		}

		$str = sanitizeDbString($str);

		return $str;
	} else if(strcmp($type, 'boolean') == 0) {// Checkboxes
		$res = FALSE;

		if(strcmp($arr, "get") == 0) {
			$res = isset($_GET[$name]) ? $_GET[$name] == "on" : FALSE;
		} else if(strcmp($arr, 'post') == 0) {
			$res = isset($_POST[$name]) ? $_POST[$name] == "on" : FALSE;
		}

		return $res;
	}
}

function cleanInput($input) {
	$search = array(
			'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
			'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
			'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
	);
	
	$output = preg_replace($search, '', $input);
	return $output;
}

function sanitizeDbString($input) {
	/*if (get_magic_quotes_gpc()) {
		$input = stripslashes($input);
	}*/
	$output  = cleanInput($input);
// 	$output = mysqli_real_escape_string($link, $input);// As querys devem ser prepared statements
	
// 	return utf8_decode($output); // Comentado para banco de dados Hostgator
	return $output;
}

function getBrazilRegions() {
	return array(
			array(1, "AC", "Acre"),
			array(2, "AL", "Alagoas"),
			array(3, "AM", "Amazonas"),
			array(4, "AP", "Amapá"),
			array(5, "BA", "Bahia"),
			array(6, "CE", "Ceará"),
			array(7, "DF", "Distrito Federal"),
			array(8, "ES", "Espírito Santo"),
			array(9, "GO", "Goiás"),
			array(10, "MA", "Maranhão"),
			array(11, "MG", "Minas Gerais"),
			array(12, "MS", "Mato Grosso do Sul"),
			array(13, "MT", "Mato Grosso"),
			array(14, "PA", "Pará"),
			array(15, "PB", "Paraíba"),
			array(16, "PE", "Pernambuco"),
			array(17, "PI", "Piauí"),
			array(18, "PR", "Paraná"),
			array(19, "RJ", "Rio de Janeiro"),
			array(20, "RN", "Rio Grande do Norte"),
			array(21, "RO", "Rondônia"),
			array(22, "RR", "Roraima"),
			array(23, "RS", "Rio Grande do Sul"),
			array(24, "SC", "Santa Catarina"),
			array(25, "SP", "São Paulo"),
			array(26, "SE", "Sergipe"),
			array(27, "TO", "Tocantins")
	);
}

function formatPhone($num) {
	if(strlen($num) == 11) {
		$num = substr_replace($num, "(", 0, 0);
		$num = substr_replace($num, ") ", 3, 0);
		$num = substr_replace($num, "-", 10, 0);
	}
	if(strlen($num) == 10) {
		$num = substr_replace($num, "(", 0, 0);
		$num = substr_replace($num, ") ", 3, 0);
		$num = substr_replace($num, "-", 9, 0);
	}
	
	return $num;
}

/**
 * Valida se senha está de acordo com o padrão de segurança
 * 
 * @return boolean
 */
function validatePassword($password) {
	/**
	 * TODO Adicionar os outros testes
	 */
	return mb_strlen($password) > 5;
}

function createToken($value) {
    return sha1 ( "Enlighten this one: $value");
}

class Utils {
	static function getPdfPageCount($pdf) {
		$image = new Imagick();
		$image->pingImage($pdf);
		return $image->getNumberImages();
	}
}

function cleanSpeciallChars($text) {
	$utf8 = array(
			'/[áàâãªä]/u'   =>   'a',
			'/[ÁÀÂÃÄ]/u'    =>   'A',
			'/[ÍÌÎÏ]/u'     =>   'I',
			'/[íìîï]/u'     =>   'i',
			'/[éèêë]/u'     =>   'e',
			'/[ÉÈÊË]/u'     =>   'E',
			'/[óòôõºö]/u'   =>   'o',
			'/[ÓÒÔÕÖ]/u'    =>   'O',
			'/[úùûü]/u'     =>   'u',
			'/[ÚÙÛÜ]/u'     =>   'U',
			'/ç/'           =>   'c',
			'/Ç/'           =>   'C',
			'/ñ/'           =>   'n',
			'/Ñ/'           =>   'N',
			'/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
			'/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
			'/[“”«»„]/u'    =>   ' ', // Double quote
			'/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
	);
	return preg_replace(array_keys($utf8), array_values($utf8), $text);
}