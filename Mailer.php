<?php
require_once "Mail.php";

echo "mail";

class Mailer {
	
	function __construct(){
		
	}
	
	function __destruct(){
		
	}
	
	public function send($name, $email){
		
		$DB_USERNAME = "root";
		$DB_PASSWORD = "wittgenstein";
		$DB_DATABASE = "matrimonio_andreaepamela";
		$DB_SERVER = "localhost";
		
		if(isset($name) && !empty($name) && isset($email) && !empty($email)){
			include "inc/rain.tpl.class.php"; //include Rain TPL
			raintpl::$tpl_dir = "tpl/"; // template directory
			raintpl::$cache_dir = "tpl/tmp/"; // cache directory
			
			$template_vars = array('name'=>$name, 'header'=>"cid:PHP-CID-header");
			
			$tpl = new raintpl();
			$tpl->assign( $template_vars );
			$mail_content = $tpl->draw("mail", $return_string = true);
			
			$boundary = md5(uniqid(microtime()));
			
			$boundaryInner = md5(uniqid(microtime()));
			
			$mail_body = "\r\n--" . $boundary . "\r\n";
			$mail_body .= "Content-Type: multipart/alternative; boundary=" . $boundaryInner . "\r\n";
			
			$mail_body .= "\r\n--" . $boundaryInner . "\r\n";
			$mail_body .= "Content-Type: text/plain; charset=UTF-8; format=flowed\r\n";
			$mail_body .= "Content-Transfer-Encoding: 8bit\r\n";
			$mail_body .= "\r\n" . $mail_content . "\r\n";
			
			$tpl = new raintpl();
			$tpl->assign( $template_vars );
			$mail_content = $tpl->draw("mail_template", $return_string = true);
			
			$mail_body .= "\r\n--" . $boundaryInner . "\r\n";
			$mail_body .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$mail_body .= "Content-Transfer-Encoding: quoted-printable\r\n";
			$mail_body .= "\r\n" . $mail_content . "\r\n";
			
			$mail_body .= "\r\n--" . $boundaryInner . "--\r\n";
			$mail_body .= "\r\n--" . $boundary . "\r\n";
			
			$fileName = "header_mail.png";
			$inputFile = "../img/" . $fileName;
			$fileType = "image/png";
			$fileSize = filesize($inputFile);
			
			$encodedInputFile = $this->encodeAttachment($inputFile, $fileSize);
			if ($encodedInputFile === FALSE) {
				return FALSE;
			}
			
			$mail_body .= "Content-Type: " . $fileType . "; name=\"" . $fileName . "\"\r\n";
			$mail_body .= "Content-Transfer-Encoding: base64\r\n";
			$mail_body .= "Content-ID: <PHP-CID-header>\r\n";
			$mail_body .= "Content-Disposition: INLINE\r\n\r\n";
			$mail_body .= "$encodedInputFile";
			
			$fileName = "back.png";
			$inputFile = "../img/" . $fileName;
			$fileType = "image/png";
			$fileSize = filesize($inputFile);
				
			$encodedInputFile = $this->encodeAttachment($inputFile, $fileSize);
			if ($encodedInputFile === FALSE) {
				return FALSE;
			}
				
			$mail_body .= "\r\n--" . $boundary . "\r\n";
			$mail_body .= "Content-Type: " . $fileType . "; name=\"" . $fileName . "\"\r\n";
			$mail_body .= "Content-Transfer-Encoding: base64\r\n";
			$mail_body .= "Content-Disposition: INLINE\r\n";
			$mail_body .= "Content-ID: <PHP-CID-back>\r\n\r\n";
			$mail_body .= "$encodedInputFile";
			
			$mail_body .= "--" . $boundary . "--";
			
			//$from = "andreaandpamela@gmail.com";
			$from = "sposi@andreaepamela.it";
            $to = $email;
            $subject = "Lista nozze Andrea&Pamela";

            //$host = "ssl://smtp.gmail.com";
            $host = "ssl://smtps.aruba.it";
            $port = "465";

            $username = $from;
            //$password = "ap_wedding2015";
            $password = "zpP.32_st6";
			
	
			
			$headers = array ('MIME-Version' => "1.0",
					'From' => $from,
					'To' => $to,
					'Subject' => $subject,
					'Content-Type' => 'multipart/related; boundary=' . $boundary);
			
			$smtp = Mail::factory('smtp',
					array ('host' => $host,
							'port' => $port,
							'auth' => true,
							'username' => $username,
							'password' => $password
					        ));
			
			$mail = $smtp->send($to, $headers, $mail_body);
			
			//mail($to,"[ESWC 2014] Information for paper " . $id,$mail_body,$headers, "From: $from\n");
			
			if (PEAR::isError($mail)) {
				echo $mail->getMessage();
			}
	
			$db_handle = mysql_connect($DB_SERVER, $DB_USERNAME, $DB_PASSWORD);
			
			$db_found = mysql_select_db($DB_DATABASE, $db_handle);
					
			if($db_found){
			
				mysql_query("INSERT INTO lista_nozze (nome, email) VALUES ('" . str_replace("'", "\'", $name) . "', '" . str_replace("'", "\'", $email) . "')");
			}

		}
	}
	
	private function encodeAttachment($attach, $fileSize) {
		if ($file = @fopen($attach, 'r')) {
			if ($contents = @fread($file, $fileSize)) {
				$encodedAttach = chunk_split(base64_encode($contents));
				if (@fclose($file)) {
					return $encodedAttach;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
}
    
$http_origin = $_SERVER['HTTP_ORIGIN'];
if($http_origin == "http://www.andreaepamela.it" || $http_origin == "http://andreaepamela.it"){
    header("Access-Control-Allow-Origin: $http_origin");
}

$name = $_GET["name"];
$email = $_GET["email"];

$mailer = new Mailer();
$mailer->send($name, $email);

?>