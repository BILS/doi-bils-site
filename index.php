<?php
	ini_set('display_errors', 'On');
	error_reporting(E_ALL | E_STRICT);

	// In case a tmp file is needed 
	// Leftover stuff from attempts to fix strange namespace thingy
	// $date = new DateTime();
	// $ts = $date->getTimestamp();
	// $tmp_file_name = "tmp_$ts.xml";

	// initialize some variables
	$doi_json_file = "issued_dois.json"; // the issued dois json file name
	$xslt_file = ""; // the stylesheet file to use for the xslt transform
	$xml = ""; // the xml DOMDocument to transform

	if( ! isset($_GET['doi'])) { // Produce the list of issued DOIs
		// URI for all SND.BILS issued DOIs
		$xml_uri = "http://search.datacite.org/api?q=*&fq=datacentre_facet%3A%22SND.BILS+-+Bioinformatics+Infrastructure+for+Life+Sciences%22&fl=doi,creator,title,publisher,publicationYear,datacentre&fq=is_active:true&fq=has_metadata:true&wt=xml&indent=true";

		// Load XML file
		$xml = new DOMDocument;
		$xml->load($xml_uri);

		// Set XSLT transform stylesheet file
		$xslt_file = "index_page.xsl";


	} else { // Produce the landing page for the specified DOI
		$doi = $_GET['doi'];
		
		// URI for the specified DOI
		$uri_prefix = "http://data.datacite.org/application/x-datacite+xml";
		$xml_uri = "$uri_prefix/$doi";

		// Load XML file
		$xml = new DOMDocument;
		$xml->load($xml_uri);

		// // read resource info from issued_dois.json and insert in xml
	    $json_data = file_get_contents($doi_json_file);
	    $data = json_decode($json_data);

	    $data_link_arr = $data->{'DOIs'}->{$doi}->{'data_links'};

	    $top_element = $xml->getElementsByTagName("resource")->item(0);
	    $data_links_element = $xml->createElement( "data_links" );

	    // Leftover stuff from attempts to fix strange namespace thingy
	    // $data_links_element->setAttribute("xmlns", "http://datacite.org/schema/kernel-3");

	    $top_element->appendChild($data_links_element);
	    
	    // Leftover stuff from attempts to fix strange namespace thingy
	    // $ns_atr = $top_element->getAttributeNode ( "xmlns" );
	    // var_dump($ns_atr);
	    
	    foreach ($data_link_arr as $link) {
	    	$link_element = $xml->createElement( "data_link", $link );
	    	$data_links_element->appendChild($link_element);
	    	
	    }
	    // NB! The newly added elements don't seem to be considered to belong to the 
	    // same namespace as the parent element.
	    // This affects how the xsl select statements should be specified

	    // Leftover stuff from attempts to fix strange namespace thingy
	    // echo "<pre>";
	    // var_dump($top_element);
	    // echo $xml->savexml();
	    // echo "</pre>";


	 //    // ugly fix to get the dom to update(?) for the transform to work later on
	 //    // there must be someway to fix this
	    // $xml->save($tmp_file_name);
		// $xml->load($tmp_file_name);
		// unlink($tmp_file_name);

	    // echo "<pre>";
	    // echo $xml->saveXML();
	    // echo "</pre>";

		// Find references to publications and get doi metadata for these
		// and insert in xml
		// ==== MORE WORK NEEDED HERE! ====

		// Set XSLT transform stylesheet file
		$xslt_file = "landing_page.xsl";
	}

	// Load XSL file
	$xsl = new DOMDocument;
	$xsl->load($xslt_file);

	// Configure the transformer
	$proc = new XSLTProcessor;

	// Attach the xsl rules
	$proc->importStyleSheet($xsl);

	echo $proc->transformToXML($xml);

?>
