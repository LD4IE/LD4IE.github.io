<?php 


include_once("inc/arc2/ARC2.php");


$URL = "http://www.scholarlydata.org";
$actual_link = "http://www.scholarlydata.org/person/andrea-giovanni-nuzzolese";
$sparql = "describe <" . $actual_link . ">";
		
$requestString = $URL . "/sparql/?query=" . urlencode($sparql);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $requestString);


$headersCounter = 0;
$headers = array();

$headers[0] = "Accept: text/turtle";
$headersCounter += 1;

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

$parser = ARC2::getTurtleParser();

echo $response;

$parser->parse($response);

$triples = $parser->getTriples();

echo 'The graph contains ' . count($triples);

$triplesMap = Array();

for ($i = 0, $i_max = count($triples); $i < $i_max; $i++) {
	$triple = $triples[$i];
	$subject = $triple["s"];
	$trips = $triplesMap[$subject];
	if(!isset($trips)){
		$trips = Array();
	}
	array_push($trips, $triple);
	$triplesMap[$subject] = $trips;
}

$trips = $triplesMap["http://www.scholarlydata.org/person/andrea-giovanni-nuzzolese"];

echo "\n" . count($trips);
foreach($trips as $triple){
	echo $triple["o"];
}

?>