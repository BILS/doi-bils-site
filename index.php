<?php
	// // Debugging stuff
	// ini_set('display_errors', 'On');
	// error_reporting(E_ALL | E_STRICT);

	// In case a tmp file is needed 
	// Leftover stuff from attempts to fix strange namespace thingy
	// $date = new DateTime();
	// $ts = $date->getTimestamp();
	// $tmp_file_name = "tmp_$ts.xml";

	// initialize some variables
	
	// the issued dois json file name 
	// NB! This file is not part of this git repo. Must be checked out from a separate git repo
	$doi_json_file = "data/issued_dois.json"; 

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
		$doi = strtoupper($doi); //make it uppercase to work with the entries in the issued_dois.json file

		// URI for the specified DOI
		$uri_prefix = "http://data.datacite.org/application/x-datacite+xml";
		$xml_uri = "$uri_prefix/" . urlencode($doi);
			// get local file for testing/implementation
		// $split = preg_split('/\//', $doi);
		// $xml_uri = $split[2] . '.xml';


		// Load XML file
		$xml = new DOMDocument;
		$load_ok = $xml->load($xml_uri);

		if (!$load_ok) {
			echo "<br/>No such doi found: $doi";
			exit();
		}
		// // read resource info from issued_dois.json and insert in xml
	    $json_data = file_get_contents($doi_json_file);
	    $data = json_decode($json_data);

	    // Get the resource DOM element from the xml
	    $top_element = $xml->getElementsByTagName("resource")->item(0);
	    
	    if (isset($data->{'DOIs'}->{$doi})) {
	    	// echo "Found link for $doi";
		    $data_link_arr = $data->{'DOIs'}->{$doi}->{'data_links'};

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
	    }




		// Find references to publications and get doi metadata for these
		// and insert in xml

		// Get the refence doi from the xml
	    $ref_element = $top_element->getElementsByTagName("relatedIdentifier")->item(0);
	    // This gets the first relatedIdentifier and ignores the rest.
	    // Should possibly be improved to handle more than one relatedIdentifier
	    // and different types as well.
	    // NB! The xsl would also have to be changed for that to work properly
	    if (isset($ref_element)) {
		    $ref_doi = $ref_element->nodeValue;
		    // print_r($ref_doi);

			// Fetch the metadata for the reference from crossref.org
		    $crossref_uri = "http://search.crossref.org/dois?q=" . urlencode($ref_doi);
		    $ref_json_data = file_get_contents($crossref_uri);
		    $ref_data = json_decode($ref_json_data);

		    $ref =  $ref_data[0]->{'fullCitation'} ;
		    	// remove any html formating in the output
		    $ref = preg_replace('/<i>/', '', $ref);
		    $ref = preg_replace('/<\/i>/', '', $ref);

			// Add appropriate bits to the xml
		    $full_citation_element = $xml->createElement( "fullCitation", $ref );
		    $ref_attr = $xml->createAttribute("citation_doi");
		    $ref_attr->value = $ref_doi;
		    $full_citation_element->appendChild($ref_attr);
		    $top_element->appendChild($full_citation_element);
	    }

		// Set XSLT transform stylesheet file
		$xslt_file = "landing_page.xsl";

	    // Leftover stuff from attempts to fix strange namespace thingy

	 //    // ugly fix to get the dom to update(?) for the transform to work later on
	 //    // there must be someway to fix this
	    // $xml->save($tmp_file_name);
		// $xml->load($tmp_file_name);
		// unlink($tmp_file_name);
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
