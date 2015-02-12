<?php

// Stub file to start working from to create automatically updated
// landing pages and index page

// Load XML file
$xml = new DOMDocument;
$xml->load('GA00001.xml');

// Load XSL file
$xsl = new DOMDocument;
$xsl->load('landing_page.xsl');

// Configure the transformer
$proc = new XSLTProcessor;

// Attach the xsl rules
$proc->importStyleSheet($xsl);

echo $proc->transformToXML($xml);
?>