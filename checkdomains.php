<?php
/*=========================================================
// File:        checkdomains.php
// Description: main file of checkdomains
// Created:     2020-03-02
// Licence:     GPL-3.0-or-later
// Copyright 2020 Michel Dubois

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

Inspired by @x0rz
https://github.com/x0rz/phishing_catcher
=========================================================*/


function traiteString($str) {
	$str = str_split($str);
	$temp = '';
	for($i=0; $i<count($str); $i++) {
		switch ($str[$i]) {
			case '+':
			case '=':
			case '|':
				$temp .= ' ';
				break;
			default:
				$temp .= $str[$i];
				break;
		}
	}
	$temp = str_split($temp);
	$output = '';
	for($i=0; $i<count($temp); $i++) {
		if (isset($temp[$i+1])) {
			$chrNum = sprintf("%d%d", ord($temp[$i]), ord($temp[$i+1]));
			switch ($chrNum) {
				case '4039': // remove ('
				case '3941': // remove ')
				case '4041': // remove ()
				case '4747': // remove //
					$output .= ' ';
					$i += 1;
					break;
				default:
					$output .= $temp[$i];
					break;
			}
		} else {
			$output .= $temp[$i];
		}
	}
	$output = strip_tags($output);
	return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}


function genNonce($length) {
	$nonce = random_bytes($length);
	$b64 = base64_encode($nonce);
	$url = strtr($b64, '+/', '-_');
	return rtrim($url, '=');
}


function genCaptcha() {
	if(isset($_SESSION['sess_captcha'])) {
		unset($_SESSION['sess_captcha']);
	}
	$imgWidth = 100;
	$imgHeight = 24;
	$nbrLines = 5;
	$img = imagecreatetruecolor($imgWidth, $imgHeight);
	$bg = imagecolorallocate($img, 0, 0, 0);
	imagecolortransparent($img, $bg);
	for($i=0; $i<=$nbrLines; $i++) {
		$lineColor = imagecolorallocate($img, rand(0,255), rand(0,255), rand(0,255));
		imageline($img, rand(1, $imgWidth-$imgHeight), rand(1, $imgHeight), rand(1, $imgWidth+$imgHeight), rand(1, $imgHeight), $lineColor);
	}
	$captchaNumber = ["un", "deux", "trois", "quatre", "cinq"];
	$val1 = rand(1, 5);
	$val2 = rand(1, 5);
	$_SESSION['sess_captcha'] = $val1 * $val2;
	$captchaString = $captchaNumber[$val1-1].'*'.$captchaNumber[$val2-1];
	$textColor = imagecolorallocate($img, 40, 45, 50);
	imagestring($img, 3, 0, 4, $captchaString, $textColor);
	ob_start();
	imagepng($img);
	$rawImageBytes = ob_get_clean();
	imagedestroy($img);
	return(base64_encode($rawImageBytes));
}


function headPage() {
	$_SESSION['nonce'] = genNonce(8);
	header("cache-control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Content-type: text/html; charset=utf-8");
	header('X-Content-Type-Options: "nosniff"');
	header("X-XSS-Protection: 1; mode=block");
	header("X-Frame-Options: deny");
	printf("<!DOCTYPE html><html lang='fr-FR'><head>");
	printf("<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	printf("<title>Test de domaines</title>");
	printf("<link href='data/style.css' rel='StyleSheet' type='text/css' media='all' />");
	printf("<script nonce='%s' src='data/checkdomains.js'></script>", $_SESSION['nonce']);
	printf("</head><body>");
}


function footPage() {
	printf("</body></html>");
	printf("<script nonce='%s'>document.body.addEventListener('load', displayStream());</script>", $_SESSION['nonce']);
}


function validateCaptcha($captcha) {
	if (strncmp($_SESSION['sess_captcha'], $captcha, 6) === 0) {
		return true;
	} else {
		return false;
	}
}


function destroySession() {
	session_unset();
	session_destroy();
	session_write_close();
	setcookie(session_name(),'',0,'/');
	header('Location: checkdomains.php');
}


function testForm() {
	$captcha = genCaptcha();
	printf("<div class='container ctn-full'><span class='oneliner brown'>// Saisissez des mots clefs</span><br />");
	printf("<form method='post' id='checkwords' action='checkdomains.php'>");
	printf("<table><tr><td>");
	printf("<input type='text' size='20' maxlength='20' name='word1' id='word1' placeholder='mot clef' required />&nbsp;&nbsp;&nbsp;&nbsp;");
	printf("<input type='text' size='20' maxlength='20' name='word2' id='word2' placeholder='mot clef' />&nbsp;&nbsp;&nbsp;&nbsp;");
	printf("<input type='text' size='20' maxlength='20' name='word3' id='word3' placeholder='mot clef' />");
	printf("</td><td>&nbsp;</td><td>");
	printf("<img src='data:image/png;base64,%s' alt='captcha'/>", $captcha);
	printf("</td><td>&nbsp;</td><td>");
	printf("<input type='text' size='10' maxlength='10' name='captcha' id='captcha' placeholder='Saisir le code' required />");
	printf("</td><td>&nbsp;</td><td>");
	printf("<input class='button' type='submit' value='Valider' />");
	printf("</td><td>&nbsp;</td><td>");
	printf("<input id='stop' class='button' type='button' value='Stopper' />");
	printf("</td><td>&nbsp;</td><td>");
	printf("<div id='download'></div>");
	printf("</td></tr></table>");
	printf("</form>");
	printf("</div>");
	printf("<script nonce='%s'>document.getElementById('stop').addEventListener('click', function(){stopStream();});</script>", $_SESSION['nonce']);
}


function displayAnalyse() {
	printf("<div id='globalstream' class='flex ctn-l ctn-half scroller'>");
	printf("<span class='oneliner brown'>// Domaines créés</span><br />");
	printf("</div>");
}


function displayResult($post='') {
	printf("<div id='wordmonitoring' class='flex ctn-r ctn-half scroller'>");
	printf("<span class='oneliner brown'>// Analyse en temps réels</span><br />");
	printf("<table><tr><td>&nbsp;");
	if (!empty($post['word1'])) {
		printf("<span class='white' id='txtword1'>%s</span>", traiteString($post['word1']));
	}
	printf("</td><td>&nbsp;");
	if (!empty($post['word2'])) {
		printf("<span class='white' id='txtword2'>%s</span>", traiteString($post['word2']));
	}
	printf("</td><td>&nbsp;");
	if (!empty($post['word3'])) {
		printf("<span class='white' id='txtword3'>%s</span>", traiteString($post['word3']));
	}
	printf("</td></tr></table>");
	printf("</div>");
}


session_start();
if (isset($_POST['captcha'])) {
	if (validateCaptcha($_POST['captcha'])) {
		headPage();
		testForm();
		displayAnalyse();
		displayResult($_POST);
		footPage();
	} else {
		destroySession();
	}
} else {
	headPage();
	testForm();
	displayAnalyse();
	displayResult();
	footPage();
}



?>
