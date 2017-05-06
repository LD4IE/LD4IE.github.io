<?php
/**
 * Author: Andrea Nuzzolese 
 */

$INDEX_PAGE = "home.html";

$URL = "http://www.scholarlydata.org";

$w3id = "https://w3id.org/scholarlydata/";

$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$pattern = "/^" . preg_quote($URL, '/') . "((\/)*|\/index.php(\/)*)$/";

if(preg_match($pattern, $actual_link))
	include $INDEX_PAGE;
else {
	
	//require_once( "inc/sparqllib.php" );
	
	$acceptHeader = str_replace('application/n-triples', 'text/plain', $_SERVER['HTTP_ACCEPT']);
	
	
	$mime = getBestSupportedMimeType();
	
	$actual_link = str_replace($URL . "/", $w3id, $actual_link);
	
	$sparql = "CONSTRUCT{<" . $actual_link . "> ?p ?o . ?s ?p <" . $actual_link . ">}"
			. "WHERE{ "
			. "{<" . $actual_link . "> ?p ?o} "
			. "UNION "
			. "{?s ?p <" . $actual_link . ">} "
			. "}";
	$requestString = $URL . "/sparql/?query=" . urlencode($sparql);
	
	$ch = curl_init();
	   
	curl_setopt($ch, CURLOPT_URL, $requestString);
	
	
	$headersCounter = 0;
	$headers = array();
	if(isset($_SERVER['HTTP_ACCEPT'])){
		if($mime == 'text/html')
			$accept = "application/rdf+xml";
		else $accept = $acceptHeader;
		
		$headers[0] = "Accept: " . $accept;
		$headersCounter += 1;
	}
	else {
		$headers[0] = "Accept: application/rdf+xml";
		$headersCounter += 1;
	}
	// don't give me the headers just the content
	curl_setopt($ch, CURLOPT_HEADER, 0);
	if(count($headers) > 0){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	
	// return the value instead of printing the response to browser
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	$response = curl_exec($ch);
	 
	$info = curl_getinfo($ch);
	$contentType = $info['content_type'];
	 
	// remember to always close the session and free all resources
	curl_close($ch);
	
	//$mime = getBestSupportedMimeType();
	
	if($mime == 'text/html'){
		include "inc/rain.tpl.class.php"; //include Rain TPL
		raintpl::$tpl_dir = "tpl/"; // template directory
		raintpl::$cache_dir = "tpl/tmp/"; // cache directory
			
		$tpl = new raintpl();
		$tpl->assign("entity", $actual_link);
		
		include_once("inc/arc2/ARC2.php");
		
		$parser = ARC2::getRDFParser();
		$parser->parse($URL, $response);
		$triples = $parser->getTriples();
		
		if(count($triples) > 1){
			$triplesMap = Array();
			
			for ($i = 0, $i_max = count($triples); $i < $i_max; $i++) {
	  			$triple = $triples[$i];
	  			$subject = $triple["s"];
	  			$predicate = $triple["p"];
	  			$object = $triple["o"];
	  			
	  			$preds = $triplesMap[$subject];
	  			if(!isset($preds)){
	  				$preds = Array();
	  			}
	  			$objs = $preds[$predicate];
	  			if(!isset($objs)){
	  				$objs = Array();
	  			}
	  			array_push($objs, $object);
	  			
	  			$preds[$predicate] = $objs;
	  			
	  			$triplesMap[$subject] = $preds;
			}
			
			$preds = $triplesMap[$actual_link];
			
			
			$internalContent = "";
			foreach($preds as $predicate => $objs){
				
				
				$internalContent .= "<span class='rdfa-predicate'><span class='title'>Property:</span> " . $predicate . "</span>";
				
				foreach($objs as $object){
					if(startsWith($object, "http://") || startsWith($object, "https://")){
						$internalContent .= "<a class='rdfa-object' rel='" . $predicate . "' href='" . $object . "'>" . $object . "</a>";
					}
					else{
						$internalContent .= "<span class='rdfa-object' property=" . $predicate . ">" . $object . "</span>";
					}
				}
			}
			
			$content = "<div class='entity' about='" . $actual_link . "'> <span class='title'>Subject:</span> " . $actual_link . $internalContent . "</div>";
			
			$internalContent = "";
			
			foreach($triplesMap as $entity => $preds){
				if($entity != $actual_link){
					foreach($preds as $predicate => $values){
						foreach($values as $object){
							$internalContent .= "<span class='rdfa-predicate'><span class='title'>Property:</span> " . $predicate . "</span>";
								
							if(startsWith($object, "http://") || startsWith($object, "https://")){
								$internalContent .= "<a class='rdfa-object' rel='" . $predicate . "' href='" . $object . "'>" . $object . "</a>";
							}
							else{
								$internalContent .= "<span class='rdfa-object' property=" . $predicate . ">" . $object . "</span>";
							}
							
						}
					}
					$content .= "<div class='entity' about='" . $entity . "'> <span class='title'>Subject:</span> " . $entity . $internalContent . "</div>";
					$internalContent = "";
				}
				
			}
			
			$tpl->assign("content", $content);
			
			/*
			$doc = new DOMDocument();
			$internalErrors = libxml_use_internal_errors(true);
			$doc->loadHTML($response);
			
			$docBody = $doc->getElementsByTagName("body");
			if ( $docBody && 0<$docBody->length ) {
				$content = $docBody->item(0);
				
				$tmp_doc = new DOMDocument();
				$tmp_doc->appendChild($tmp_doc->importNode($content,true));
				$innerHTML .= $tmp_doc->saveHTML();
				
				$tpl->assign("content", $innerHTML);
			}
			else $tpl->assign("content", "No content");
			*/
			//$tpl->assign("content", "No content");
		}
		else {
			http_response_code(404);
			$tpl->assign("content", "Sorry, the entity you searched for does not exist on Scholarlydata.org.");
		}
		
		$tpl->draw("template");
	}
	else if($mime != null) {
		header('Content-type: ' . $mime);
		header('Access-Control-Allow-Origin: *');
		
		$ok = true;
		if($mime == "text/turtle" && startsWith($response, "# Empty TURTLE")){
			$ok = false;
		}
		else if($mime == "application/n-triples" && startsWith($response, "# Empty NT")){
			$ok = false;
		}
		else if($mime == "application/rdf+json" && startsWith($response, "{ }")){
			$ok = false;
		}
		else if($mime == "application/rdf+xml"){
			include_once("inc/arc2/ARC2.php");
			
			$parser = ARC2::getRDFParser();
			$parser->parse($URL, $response);
			$triples = $parser->getTriples();
			if(count($triples) <= 1) $ok = false;
		}
		if($ok) echo $response;
		else http_response_code(404);
		
	}
	else{
		http_response_code(406);
	}
}



function rpl_ns($str){
	
	if(startsWith($str, "http://")){
		$str_new = $str;
		
		$str_new = str_replace("http://xmlns.com/foaf/0.1/", "foaf:", $str_new); 
		$str_new = str_replace("http://www.scholarlydata.org/ontology/conference-ontology.owl#", "scho:", $str_new);
		$str_new = str_replace("http://www.w3.org/2002/07/owl#", "owl:", $str_new);
		$str_new = str_replace("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:", $str_new);
		$str_new = str_replace("http://www.w3.org/2000/01/rdf-schema#", "rdfs:", $str_new);
		$str_new = str_replace("http://www.ontologydesignpatterns.org/ont/dul/DUL.owl#", "dul:", $str_new);
	
	 	$link = "<a href='" . $str . "'>" . $str_new . "</a>";
	}
	else $link = $str;
	
	return $link;
}

function startsWith($haystack, $needle) {
	// search backwards starting from haystack length characters from the end
	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function getBestSupportedMimeType() {
	
	$MIME_TYPES = Array ('text/html', 'application/rdf+xml', 'text/turtle', 'application/rdf+json', 'application/n-triples');
	
	$MIME_TYPES = array_map('strtolower', (array)$MIME_TYPES);
	
	// Values will be stored in this array
	$AcceptTypes = Array ();

	// Accept header is case insensitive, and whitespace isn’t important
	$accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
	
	// divide it into parts in the place of a ","
	$accept = explode(',', $accept);
	foreach ($accept as $a) {
		// the default quality is 1.
		$q = 1;
		// check if there is a different quality
		if (strpos($a, ';q=')) {
			// divide "mime/type;q=X" into two parts: "mime/type" i "X"
			list($a, $q) = explode(';q=', $a);
		}
		// mime-type $a is accepted with the quality $q
		// WARNING: $q == 0 means, that mime-type isn’t supported!
		$AcceptTypes[$a] = $q;
	}
	arsort($AcceptTypes);
 
	// let’s check our supported types:
	foreach ($AcceptTypes as $mime => $q) {
		
		if ($q && in_array($mime, $MIME_TYPES)) return $mime;
		else if($mime == '*/*') {
			
			return 'application/rdf+xml';
		}
	}
	// no mime-type found
	return null;
}

?>