<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:d='http://datacite.org/schema/kernel-3' >
<!-- 
The magic to get it to work is that the datacite namespace (which is declared for the <resource> element in the xml) 
is assigned to the prefix d (xmlns:d=...) in the declaration above.
The d: prefix is then used in the select attributes of the xsl statements
 -->
<xsl:template match="/">
  <html>
    <head>
      <meta charset="utf-8"/>
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="viewport" content="width=device-width, initial-scale=1"/>

      <title><xsl:value-of select="d:resource/d:identifier"/></title>

      <script type="text/javascript" src="http://code.jquery.com/jquery.js"></script>
      <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

      <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css"/>
      <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css"/>

      <style type="text/css">
        table {
           font-size: 105% !important;
        }
      </style>
    </head>
    <body>
      <xsl:element name="div">
      <xsl:attribute name="itemscope"></xsl:attribute>
      <xsl:attribute name="itemtype">http://schema.org/Dataset</xsl:attribute>
      <xsl:attribute name="class">container</xsl:attribute>
      <xsl:attribute name="itemid">http://doi.org/<xsl:value-of select="d:resource/d:identifier"/></xsl:attribute>
        <div class="page-header">
          <h3>doi:<xsl:value-of select="d:resource/d:identifier"/></h3>
        </div>
        <div class="panel panel-success">
        <div class="panel-heading">
          <h3 class="panel-title">Overview</h3>
        </div>
          <div class="panel-body">
            <table class="table table-striped">
              <tr>
                <td>Title</td>
                <td itemprop="name" ><xsl:value-of select="d:resource/d:titles/d:title"/></td>
              </tr>

              <tr>
                <td>Authors</td>
                <td>
                  <xsl:for-each select="d:resource/d:creators/d:creator">
                    <xsl:if test="d:nameIdentifier">
                      <xsl:element name="span">
                        <xsl:attribute name="itemscope"></xsl:attribute>
                        <xsl:attribute name="itemtype">https://schema.org/Person</xsl:attribute>
                        <xsl:attribute name="itemprop">author</xsl:attribute>
                        <xsl:attribute name="itemid">
                          <xsl:value-of select="d:nameIdentifier/@schemeURI"/>/<xsl:value-of select="d:nameIdentifier"/>
                        </xsl:attribute>
                        <xsl:element name="a">
                          <xsl:attribute name="href">
                            <xsl:value-of select="d:nameIdentifier/@schemeURI"/>/<xsl:value-of select="d:nameIdentifier"/>
                          </xsl:attribute>
                          <span itemprop="name">
                            <xsl:value-of select="d:creatorName"/>
                          </span>
                        </xsl:element>
                      </xsl:element>
                    </xsl:if>
                    <xsl:if test="not(d:nameIdentifier)">
                      <span itemprop="author" itemscope="" itemtype="https://schema.org/Person" >
                        <span itemprop="name">
                          <xsl:value-of select="d:creatorName"/>
                        </span>
                      </span>
                    </xsl:if>
                    <xsl:if test="not(position()=last())">, </xsl:if>
                    <xsl:if test="position()=last()-1">and </xsl:if>
                  </xsl:for-each>

                </td>
              </tr>
              <tr>
                <td>Description</td>
                <td itemprop="description">
                  <xsl:for-each select="d:resource/d:descriptions/d:description">
                    <xsl:value-of select="."/>
                    <br/>
                  </xsl:for-each>
                </td>
              </tr>
              <tr>
                <td>Year</td>
                <td><xsl:value-of select="d:resource/d:publicationYear"/></td>
              </tr>
              <tr>
                <td>doi</td>
                <td itemprop="identifier" ><xsl:value-of select="d:resource/d:identifier"/></td>
              </tr>
              <tr>
                <td>Access constraints</td>
                <td>
                  <xsl:for-each select="d:resource/d:rightsList/d:rights">
                    <xsl:element name="a">
                      <xsl:attribute name="href">
                        <xsl:value-of select="./@rightsURI"/>
                      </xsl:attribute>
                      <xsl:attribute name="itemprop">license</xsl:attribute>
                      <xsl:value-of select="."/>
                    </xsl:element>
                    <br/>
                  </xsl:for-each>
                </td>
              </tr>
              <tr>
                <td>Cite as</td>
                <td>
                  <xsl:for-each select="d:resource/d:creators/d:creator">
                    <xsl:value-of select="d:creatorName"/>
                    <xsl:if test="not(position()=last())">, </xsl:if>
                    <xsl:if test="position()=last()-1">and </xsl:if>
                  </xsl:for-each>
                  (<xsl:value-of select="d:resource/d:publicationYear"/>)
                  <xsl:value-of select="d:resource/d:titles/d:title"/> 
                  <!-- <br/> -->
                  <xsl:element name="a">
                    <xsl:attribute name="href">
                      http://doi.org/<xsl:value-of select="d:resource/d:identifier"/>
                    </xsl:attribute> 
                    doi:<xsl:value-of select="d:resource/d:identifier"/>
                  </xsl:element>
                </td>
              </tr>
              <tr>
                <td>Reference</td>
                <td>
                  <xsl:if test="d:resource/fullCitation">
                    <xsl:value-of select="d:resource/fullCitation" />
                    <!-- NOTE! This bit is parsing parts of the xml that was added in memory
                    in the PHP code in index.php. It seems the newly added element 
                    (fullCitation) is not considered to belong to the same
                    namespace as the parent nodes (notice the absence of the d: prefix in the
                    select statement above). -->
                    <xsl:element name="a">
                      <xsl:attribute name="href">
                        http://doi.org/<xsl:value-of select="d:resource/fullCitation/@citation_doi"/>
                      </xsl:attribute> 
                      doi:<xsl:value-of select="d:resource/fullCitation/@citation_doi"/>
                    </xsl:element>
                  </xsl:if>
                </td>
              </tr>


            </table>
          </div>
        </div>
        <div class="panel panel-success">
          <div class="panel-heading">
            <h3 class="panel-title">Download</h3>
          </div>
          <div class="panel-body">
            <!-- <xsl:for-each select="d:resource/d:data_links/d:data_link"> -->
            <xsl:for-each select="d:resource/data_links/data_link">
            <!-- NOTE! This bit is parsing parts of the xml that was added in memory
            in the PHP code in index.php. It seems the newly added elements 
            (data_links and data_link) are not considered to belong to the same
            namespace as the parent nodes (notice the absence of the d: prefix in the
            select statement above). -->
            
              <!-- test if string contains a url -->
              <xsl:if test="contains(.,'http')">
                <xsl:element name="a">
                  <xsl:attribute name="href">
                    <xsl:value-of select="."/>
                  </xsl:attribute>
                  <xsl:value-of select="."/>
                </xsl:element>
              </xsl:if>
              
              <!-- test if string contains a mail address -->
              <xsl:if test="contains(.,'@')">
                Please contact 
                <xsl:element name="a">
                  <xsl:attribute name="href">mailto:<xsl:value-of select="."/>?subject=Requesting access to DOI:<xsl:value-of select="./../../d:identifier"/> dataset</xsl:attribute>
                  <xsl:value-of select="."/>
                </xsl:element>
                in order to apply for access.
              </xsl:if>

              <br/>
            </xsl:for-each>

          </div>
        </div>

      </xsl:element>
    </body>
  </html>
</xsl:template>

</xsl:stylesheet>
