/**
 * Toaster Web Performance Page Processing
 * Requires: jQuery 3+, Highcharts 3+, Google Charts
 * 
 * Version 5
 * 
 */
// globals
var toasterid = '';
var headerfields = '';
var headeranalysis = '';
var gzipanalysis = '';
var domainsdata = '';
var ReversedDomainsList = '';
var boolShowImages = new Boolean();
var network = '';
var nodeshape = "dot";
var nodecolouring = "groups";
var data = '';
var TPorientation = 'l'; // landscape
var importButton;
var exportButton;
var exportArea;
var container;
var browserengineversion = '';
var bgcol = 'd';
boolShowImages = false;
var boolShowFonts = new Boolean();
boolShowFonts = false;
var nTr = 0;
var lmd = '';
var fsize = '';
var server = '';
var etag = '';
var cchdrs = '';
var PageImage = '';
var HarFile = '';
var SiteTitle = '';
var PageTitle = '';
var BrwsEngine = '';
var map = '';
var rootlat;
var rootlong;
var viewObjID = 0;
var noofUniqueThirdParties = 0;
var tpchart_data = [];
var thirdpartynetworknodes_data = [];
var thirdpartynetworklinks_data = [];
var tphdata = '';
var tphoptions = '';
var mode = "D"; // "D" = domain ; "CP" = Company Product
var gchart;
var analysisOwner = '';
var analysisSite = '';
var analysisURL = '';
var analysisDisplayDate = new Date();
var analysisYear = analysisDisplayDate.getFullYear();
function loadConfigFile(configFile) {
    $.getJSON('config.json', function(data) {
console.log("config.json read: owner",data.owner);
        analysisOwner = data.owner;
        analysisSite = data.site;
        analysisURL = data.url;
    });
  }
function displayPageStatus(status) {
    document.getElementById("activitystatus").innerHTML = status;
}
function displayPageInfo() {
    document.getElementById("site").innerHTML = unescape(SiteTitle);
    document.getElementById("pagetitle").innerHTML = unescape(unescape(decodeEntities(PageTitle)));
    importButton = document.getElementById('import_button');
    exportButton = document.getElementById('export_button');
    exportArea = document.getElementById('input_output');
}
function displayToasterParms(t) {
    document.getElementById("parms").innerHTML = t;
}

function displayRootHeaders() {
    document.getElementById("headers").innerHTML = headerStr;
}
function getFileName(url) {
    //this removes the anchor at the end, if there is one
    url = url.substring(0, (url.indexOf("#") == -1) ? url.length : url.indexOf("#"));
    //this removes the query after the file name, if there is one
    url = url.substring(0, (url.indexOf("?") == -1) ? url.length : url.indexOf("?"));
    //this removes everything before the last slash in the path
    url = url.substring(url.lastIndexOf("/") + 1, url.length);
    //return
    return url;
}
/* Formating function for third paties details */
function fnFormatThirdParties(d) {
    //console.log(d);
    var colMimeType = 0;
    var colTransBytes = 0;
    var colDomType = 0;
    var col_id = 0;
    var sOut = '';
    var matched = false;
    sOut += '<table class="obj3pdetails">';
    sOut += "<thead><th>Object Type</th><th>URL</th><th>Size (Bytes)</th><th>Compressed?</th><th>Minified?</th><th>JS Document Write</th><th>Image Metadata Free?</th>";
    sOut += "</thead>";
    sOut += "<body>";
    // work through 3p objects array
    $.each(NewObj, function () {
        var tbl_row = "";
        matchedDomain = -1;
        $.each(this, function (k, v) {
            var compressionStatus = '';
            var minStatus = '';
            var imageMDStatus = '';
            var mdTypes = '';
            var jsdocwrite = 0;
            var jsdocwriteStatus = '>-';
            //console.log(k + ": " + v);
            if (k == 'id') {
                // get response date time for this object now as it is last on the array
                respsonsedatetime = NewObj[Number(v)]['response_datetime'];
                id = NewObj[Number(v)]['id'];
                objsource = NewObj[Number(v)]['Object source'];
                objtype = NewObj[Number(v)]['Object type'];
                domref = NewObj[Number(v)]['Domain ref'];
                domain = NewObj[Number(v)]['Domain'];
                mimetype = NewObj[Number(v)]['Mime type'];
                httpstatus = NewObj[Number(v)]['HTTP status'];
                jsdocwrite = NewObj[Number(v)]["JS docwrite"];
                bytesize = NewObj[Number(v)]['Content length transmitted'];
                imagemetadatasize = NewObj[Number(v)]['Metadata bytes'];
                compression = NewObj[Number(v)]['Compression'];
                if (compression == "gzip" || compression == "br" || compression == "deflate") {
                    minifiedSize = NewObj[Number(v)]['Content size minified compressed'];
                }
                else {
                    minifiedSize = NewObj[Number(v)]['Content size minified uncompressed'];
                }
                if (bytesize > (minifiedSize * 1.1) && minifiedSize > 0)
                    compressedSize = NewObj[Number(v)]['Content size minified compressed'];
                else
                    compressedSize = NewObj[Number(v)]["Content size compressed"];
                filesection = NewObj[Number(v)]['file_section'];
                filetiming = NewObj[Number(v)]['file_timing'];
                // check metadata
                if (NewObj[Number(v)]["EXIF bytes"] > 0)
                    mdTypes += "EXIF bytes; ";
                if (NewObj[Number(v)]["APP12 bytes"] > 0)
                    mdTypes += "APP12 bytes; ";
                if (NewObj[Number(v)]["IPTC bytes"] > 0)
                    mdTypes += "IPTC bytes; ";
                if (NewObj[Number(v)]["XMP bytes"] > 0)
                    mdTypes += "XMP bytes; ";
                if (NewObj[Number(v)]["Comment bytes"] > 0)
                    mdTypes += "Comment";
                if (NewObj[Number(v)]["ICC colour profile bytes"] > 0)
                    mdTypes += "ICC colour profile bytes; ";

                //console.log("looking for domain " + domain + " in row domains: " + d);
                matchedDomain = d.indexOf(domain);
                if (domref == "3P" && matchedDomain >= 0 && (httpstatus >= 200 && httpstatus < 400)) {
                    //console.log(id + ": " + objtype  + " = " + objsource);
                    if (objtype != "Image") {
                        if ((httpstatus >= 200 && httpstatus < 300) && bytesize > 0) { // not a redirection
                            if ((compression == 'gzip' || compression == 'deflate' || compression == 'br')) {
                                compressionStatus = ' class="pass" title="' + compression + '"><span class="glyphicon glyphicon-ok"></span>';
                            }
                            else
                                // not served with compressionm but ok if its a small file
                                if (bytesize < 1400)
                                    compressionStatus = ' class="pass" title="' + 'small file < 1400 bytes' + '"><span class="glyphicon glyphicon-ok"></span>';
                                else
                                    compressionStatus = ' class="fail" title="None (compressed size: ' + compressedSize.toString() + ' bytes)"><span class="glyphicon glyphicon-remove"></span>';
                            if (objtype != "Font") {
                                if (bytesize > (minifiedSize * 1.1))
                                    if (bytesize < 1400)
                                        minStatus = ' class="pass" title="' + 'small file < 1400 bytes' + '"><span class="glyphicon glyphicon-ok"></span>'
                                    else
                                        minStatus = ' class="fail" title="> 1.1 x min size (' + minifiedSize.toString() + ' bytes)"><span class="glyphicon glyphicon-remove"></span>';
                                else
                                    minStatus = ' class="pass" title=" < 1.1 x min size (' + minifiedSize.toString() + ' bytes)"><span class="glyphicon glyphicon-ok"></span>'
                            }
                            else
                                minStatus = ">-";
                            // docwrite for js files
                            if (objtype == "JavaScript") {
                                if (jsdocwrite > 0) {
                                    jsdocwriteStatus = ' class="fail" title="' + jsdocwrite.toString() + ' docwrites)"><span class="glyphicon glyphicon-remove"></span>';
                                }
                                else {
                                    jsdocwriteStatus = ' class="pass" title="No docwrites"><span class="glyphicon glyphicon-ok"></span>';
                                }
                            }
                            else
                                jsdocwriteStatus = ">-";
                        }
                        else { // a redirection or null length
                            if (httpstatus >= 300 && httpstatus < 400) {
                                minStatus = 'title="redirection">-';
                                compressionStatus = 'title="redirection">-';
                                jsdocwriteStatus = ">-";
                            }
                            else {
                                minStatus = '>-';
                                compressionStatus = '>-';
                                jsdocwriteStatus = ">-";
                            }
                        }
                        imageMDStatus = ">-";
                    }
                    else {
                        //image
                        //console.log(d + " - " + imagemetadatasize);
                        minStatus = ">-";
                        compressionStatus = ">-";
                        jsdocwriteStatus = ">-";
                        if (imagemetadatasize > 0) {
                            imageMDStatus = ' class="fail" title="' + mdTypes + '"><span class="glyphicon glyphicon-remove"></span>';
                        }
                        else {
                            imageMDStatus = ' class="pass" title="None"><span class="glyphicon glyphicon-ok"></span>';
                        }
                    }
                    // add a table row to the output
                    sOut += "<tr><td>" + objtype + "</td><td>" + unescape(objsource) + "</td><td>" + bytesize.toLocaleString() + "</td><td" + compressionStatus + "</td><td" + minStatus + "</td><td" + jsdocwriteStatus + "</td><td" + imageMDStatus + "</td></tr>"
                }
            }
        })
    })
    sOut += "</body>";
    sOut += '</table>';
    return sOut;
}
function TotalContentThirdParties(d) {
    //console.log(d);
    var colMimeType = 0;
    var colTransBytes = 0;
    var colDomType = 0;
    var col_id = 0;
    var sOut = '';
    var matched = false;
    var mdTypes = '';
    var totalJS = 0;
    var totalCSS = 0;
    var totalFont = 0;
    var totalHTML = 0;
    var totalData = 0;
    var totalOther = 0;
    var totalImage = 0;
    // work through 3p objects array
    $.each(NewObj, function () {
        var tbl_row = "";
        matchedDomain = -1;
        $.each(this, function (k, v) {
            //console.log(k + ": " + v);
            if (k == 'id') {
                // get response date time for this object now as it is last on the array
                respsonsedatetime = NewObj[Number(v)]['response_datetime'];
                id = NewObj[Number(v)]['id'];
                objsource = NewObj[Number(v)]['Object source'];
                objtype = NewObj[Number(v)]['Object type'];
                domref = NewObj[Number(v)]['Domain ref'];
                domain = NewObj[Number(v)]['Domain'];
                mimetype = NewObj[Number(v)]['Mime type'];
                httpstatus = NewObj[Number(v)]['HTTP status'];
                // jsdocwrite = NewObj[Number(v)]["JS docwrite"];
                bytesize = NewObj[Number(v)]['Content length transmitted'];
                // imagemetadatasize = NewObj[Number(v)]['Metadata bytes'];
                // compression = NewObj[Number(v)]['Compression'];
                // if (compression == "gzip" || compression == "br" || compression == "deflate") {
                //     minifiedSize = NewObj[Number(v)]['Content size minified compressed'];
                // }
                // else {
                //     minifiedSize = NewObj[Number(v)]['Content size minified uncompressed'];
                // }
                // if (bytesize > (minifiedSize * 1.1) && minifiedSize > 0)
                //     compressedSize = NewObj[Number(v)]['Content size minified compressed'];
                // else
                //     compressedSize = NewObj[Number(v)]["Content size compressed"];
                // filesection = NewObj[Number(v)]['file_section'];
                // filetiming = NewObj[Number(v)]['file_timing'];
                // check metadata
                // if (NewObj[Number(v)]["EXIF bytes"] > 0)
                //     mdTypes += "EXIF bytes; ";
                // if (NewObj[Number(v)]["APP12 bytes"] > 0)
                //     mdTypes += "APP12 bytes; ";
                // if (NewObj[Number(v)]["IPTC bytes"] > 0)
                //     mdTypes += "IPTC bytes; ";
                // if (NewObj[Number(v)]["XMP bytes"] > 0)
                //     mdTypes += "XMP bytes; ";
                // if (NewObj[Number(v)]["Comment bytes"] > 0)
                //     mdTypes += "Comment";
                // if (NewObj[Number(v)]["ICC colour profile bytes"] > 0)
                //     mdTypes += "ICC colour profile bytes; ";

                //console.log("looking for domain " + domain + " in row domains: " + d);
                matchedDomain = d.indexOf(domain);
                if (domref == "3P" && matchedDomain >= 0 && (httpstatus >= 200 && httpstatus < 400)) {
                    //console.log(id + ": " + objtype  + " = " + objsource);
                    switch (objtype)
                    {
                        case "JavaScript":
                            totalJS += bytesize;
                            break;
                        case "StyleSheet":
                            totalCSS += bytesize;
                            break;
                        case "Image":
                            totalImage += bytesize;                       
                            break;
                        case "Font":
                            totalFont += bytesize;
                            break;                        
                        case "Data":
                            totalData += bytesize;
                            break;
                        case "HTML":
                            totalHTML += bytesize;
                            break;
                        default:
                            totalOther += bytesize;
                            break;
                    }


                }
            }
        })
    })

    var arr = {totalJS,totalCSS,totalImage,totalFont,totalData,totalHTML,totalOther};
    return arr;
}
function decodeEntities(encodedString) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
    return textArea.value;
}
function getWoffFontDetails(lf)
{
    var returnValue = '';
$.ajax({
    url: '/toaster/get_fontinfo_woff.php',
    beforeSend: function () {
        //$('#tab_imageoptimisation').addClass('wait');
//console.log("AJAX request WOFF Fontinfo for: " + lf);
    },
    type: 'GET',
    async: false,
    data: { 'pathname': lf },
    success: function (response) {
//console.log("AJAX request WOFF Fontinfo was successful:" + response);
        //$('#tab_imageoptimisation').removeClass('wait');
        returnValue = response;
   
    },
    error: function (error) {
        console.log("AJAX request WOFF Fontinfo was a failure: " + error);
        //$('#tab_imageoptimisation').removeClass('wait');
    }
});
return(returnValue);
} // end function getWoffFontDetails

/* Formating function for row details */
function fnFormatDetails(oTable, nTr) {
    //console.log("table opened: " + oTable.selector); // get table name opened e.g. #TPperformance_table
    var oSettings = oTable.fnSettings();
    var colMimeType = 0;
    var colTransBytes = 0;
    var colDomType = 0;
    var col_id = 0;
    // lookup column containing the mimetype
    for (var col = 1; col < 10; col++) {
        col_nm = oSettings.aoColumns[col].sTitle;
        if (col_nm == 'Mime Type') {
            colMimeType = col;
            break;
        }
    }
    // lookup column containing the transbytes
    for (col = 1; col < 10; col++) {
        col_nm = oSettings.aoColumns[col].sTitle;
        if (col_nm == 'Content Size') {
            colTransBytes = col;
            break;
        }
    }
    // lookup column containing the domtype
    for (col = 1; col < 10; col++) {
        col_nm = oSettings.aoColumns[col].sTitle;
        if (col_nm == 'Domain Type') {
            colDomType = col;
            break;
        }
    }
    //console.log('mimetype col:' + colMimeType);
    var aData = oTable.fnGetData(nTr);
    var objID = aData[1];
    var objType = aData[2];
    //console.log('objType =' + objType);
    var objSrcURL = unescape(unescape(aData[3])); // not displayed as column
    // also 4 , shortname shown as column
    var objLocFile = aData[5]; // not displayed as column
    var objImgType = aData[11];
    var fontname = aData[15]; // for fonts only
    //console.log('objImgType =' + objImgType);
    var ObjLabel = "Full URL";
    var TransBytes = aData[colTransBytes];
    var mimetype = aData[colMimeType];
    var domtype = aData[colDomType];
    //console.log("mimetype = " + mimetype);
    //console.log("objLocFile="+ objLocFile);
    var windir = objLocFile.substr(1, 1);
    //console.log("char = " + windir);
    var objLocFileurl = '';
    var objLocFileCnv = '';

    if (windir == ':') {
        objLocFileurl = objLocFile.substr(2); // strip the c:\ from the front
        objLocFileCnv = objLocFileurl.replace(/[/\\*]/g, '\/');
        //console.log("WIN objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
    }
    else { // linux
        if(objLocFile.indexOf("/usr/share") !== -1)
        { // local
        objLocFileurl = objLocFile.substr(10); // strip the /usr/share from the front
        objLocFileCnv = objLocFileurl.replace(/[/\\*]/g, '\/');
        }
        else // webpagetoaster.com
            objLocFileCnv = objLocFile;
//console.log("LINUX objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
    }
    objLocFileCnv.trim();
    //console.log("objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
    //console.log(objID + " = " + objType+ ": " + objImgType);
    var sOut = '';

    var hdrinfo = getHeadersForObjectRow(objID);
    var hdranalysis = analyseHeader(objID, hdrinfo.toString(), mimetype, false);
    //console.log('hdr raw: '+hdrinfo);
    //console.log('hdr str: '+hdrinfo.toString());
    //console.log('hdr analysis: '+hdranalysis);

    if (objSrcURL.indexOf('base64') > 0) {
        objSrcURL = objSrcURL.substr(0, 32) + "...";
        objLabel = "Inline Image";
    }
    // 1st table, 2 column
    sOut += '<table id="tabobjectinfo" class="tabobjectinfo" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px;background-color:#CDCDCD;">';
    sOut += '<caption>Object Information</caption>';
    sOut += '<tr><td>' + ObjLabel + ':</td><td class=\"objlabel\">' + objSrcURL + '</td></tr>';// fullname
    if (domtype != 'redirection')
        sOut += '<tr><td>Save Path:</td><td>' + objLocFileCnv + '</td></tr>';// fullname
    //sOut += '<tr><td>Extra info:</td><td>further details here</td></tr>';
    // split query parameters in a new row
    var parms = '';
    var url = unescape(unescape(decodeEntities(aData[3])));
    //console.log("url = " + aData[3]);
    //console.log("decoded url = " + url );
    var queryString = url.substring(url.indexOf('?') + 1);
    if (queryString != url) {
        parms = queryString.replace(/&/g, "<br/>");
        sOut += '<tr><td>Parameters:</td><td>' + parms + '</td></tr>';// fullname
    }
    sOut += '</table><br/>';

    // 2nd  table HEADERS 2 column, not for base64 inline images
    if (objSrcURL.indexOf('base64') == -1) {
        var headertstable = '';
        headertstable += '<table id="tabhdrs" class="tabhdrs" cellpadding="5" cellspacing="0" border="1" width="1200px" style="padding-left:50px;background-color:#CDCDCD;">';
        headertstable += '<caption>Response Headers and Analysis</caption>';
        // original
        headertstable += '<tr><td class="headersD">' + hdrinfo + '</td><td class="headersA">' + hdranalysis + '</td></tr>';
        headertstable += '</table><br/>';
        sOut += headertstable;
        // table for headers tab
        var headertstablev = '';
        //headertstablev += '<table cellpadding="5" cellspacing="0" border="1" width="50%" style="padding-left:50px;background-color:#CDCDCD;">';
        //headertstablev += '<caption>Response Headers and Analysis</caption>';
        //headertstablev += '<tr><td class="headersD">'+ hdrinfo +'</td></tr>';
        headertstablev += '<tr><td class="headersV">' + hdranalysis + '</td></tr>';
        //headertstablev += '</table><br/>';

        //$("#headers").html(hdrinfo);

        // prepare for detailed display
        displayObjectDetail(objID, objSrcURL);
    }
    if ((objType == 'JavaScript' || objType == 'StyleSheet' || objType == 'Data' || objType == 'HTML') && domtype != 'redirection') {
        //console.log("displaying content for: " + objLocFileCnv);
        var brush = '';
        var comments = '';
        var commentstr = '';
        switch (objType) {
            case "JavaScript":
                brush = 'js';
                break;
            case "StyleSheet":
                brush = 'css';
                break;
            default:
                brush = 'xml';
                break;
        }
        sOut += 'Comments Only<br/><pre><code class="sunburst filecode" id="filec' + objID + '">Please wait... loading comments</code></pre>';
        if(location.host == "www.webpagetoaster.com")
            sOut += 'Full File<br/><pre><code class="sunburst filecode" id="file' + objID + '">Please wait... loading the full file</code></pre>';
        else
            sOut += 'Beautified, Full File<br/><pre><code class="sunburst filecode" id="file' + objID + '">Please wait... loading and beautifing the full file</code></pre>';
        $.post(
            "/toaster/getafilebeautify.php",
            { name: objLocFileCnv, type: objType },
            function (data) {
                //console.log(data);
                comments = extractComments(data);
                if (comments !== null) {
                    commentstr = comments.join();
                    //console.log(comments);
                    $('#filec' + objID).text(commentstr);
                }
                else {
                    $('#filec' + objID).remove();
                }
                // add code to display
                $('#file' + objID).text(data);
                // highlight code
                $('pre code').each(function (i, block) {
                    hljs.highlightBlock(block);
                });
                //console.log(extractComments(data));
            });
    }
    // 3rd table IMAGES, multi column
    if (objType == 'Image') {
        // change local file url to a _tn file
        var flen = objLocFileCnv.length;
        var ext = objLocFileCnv.substr(objLocFileCnv.lastIndexOf('.') + 1);
        var fpath = objLocFileCnv.substr(0, flen - (ext.length + 1));
        var sOutTN = '';
        tnfile = fpath + '_tn.' + ext;
        var filename = getFileName(objLocFileCnv);
        //console.log("Thumbnail check: original: " + objLocFileCnv);
        //console.log("Thumbnail check: thumbnail: " + tnfile);


        // update the image data on the table row
        $.ajax({
            url: tnfile, //or your url
            success: function (data) {
                sOutTN = '<tr><td class=\"rowimg\"><img src=\"' + tnfile + '\"></td></tr>';
                sOutTN += '<tr><td class=\"filename\" colspan=\"100%\">Thumbnail of ' + filename + '</td></tr>';
                //alert(tnfile + ' exists');
                //console.log("Thumbnail check: thumbnail found: " + tnfile );
            },
            error: function (data) {
                sOutTN = '';
                //alert(tnfile + ' does not exist');
                //console.log("Thumbnail check: thumbnail not found");
            },
            async: false
        });
        //console.log(sOutTN);
        //console.log("tn checked");
        sOut += '<table id="tabmetadata" class="tabmetadata">';
        sOut += '<caption>Image Structure and MetaData Analysis</caption>';
        var Structure = getImageDataforObjectRow(objID, "Structure");
        var comments = getImageDataforObjectRow(objID, "Comments");


        sOut += '<tr>';
        if (Structure.length > 11) {
            sOut += '<td class=\"structure tabmetadata\">Structure:<br/>' + Structure + '</td>';
        }
        //console.log(objImgType);
        //console.log(Structure);
        //console.log(comments);
        if (objImgType == 'JPEG/EXIF' || objImgType.substr(0, 9) == 'JPEG/JFIF') {
            var EXIFinfo = getImageDataforObjectRow(objID, "EXIF");
            var IPTCinfo = getImageDataforObjectRow(objID, "IPTC");
            var XMPinfo = getImageDataforObjectRow(objID, "XMP");
            var ICCinfo = getImageDataforObjectRow(objID, "ICC");
            var APP12info = getImageDataforObjectRow(objID, "APP12");
            //console.log(APP12info);
            // format XML
            xml_raw = XMPinfo;
            xml_formatted = formatXml(xml_raw);
            xml_escaped = xml_formatted.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/ /g, '&nbsp;').replace(/(?:\r\n|\r|\n)/g, '<br/>');
            //console.log('sending metadata to table');
            // multi columns in one row
            if (EXIFinfo.length > 5)
                sOut += '<td class=\"exif tabmetadata\">EXIF:<br/>' + EXIFinfo + '</td>';
            if (APP12info.length > 5)
                sOut += '<td class=\"exif tabmetadata\">APP12:<br/>' + APP12info + '</td>';
            if (IPTCinfo.length > 4)
                sOut += '<td class=\"iptc tabmetadata\">IPTC:<br/>' + IPTCinfo + '</td>';
            if (XMPinfo.length > 4)
                sOut += '<td class=\"xmp tabmetadata\">XMP:<br/><pre><code>' + xml_escaped + '</code></pre></td>';
            //if(ICCinfo.length > 14)
            //sOut += '<td class=\"icc\">ICC Profile:<br/><pre><code>' + ICCinfo + '</code></pre></td>';
            sOut += '</tr>';
        }

        if (objImgType == 'PNG') {
            XMPinfo = getImageDataforObjectRow(objID, "XMP");
            //console.log("PNG checking XML: " + XMPinfo);
            // format XML
            xml_raw = XMPinfo;
            xml_formatted = formatXml(xml_raw);
            //console.log(xml_formatted);
            xml_escaped = xml_formatted.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/ /g, '&nbsp;').replace(/(?:\r\n|\r|\n)/g, '\n').replace(/'> <'/g, '\n');
            // multi columns in one row
            if (XMPinfo.length > 4)
                sOut += '<td class=\"xmp tabmetadata\">XMP:<br/><pre><code>' + xml_escaped + '</code></pre></td></tr>';
        }
        if (objImgType.substr(0, 9) == 'GIF87a' || objImgType.substr(0, 9) == 'GIF89a') {
            XMPinfo = getImageDataforObjectRow(objID, "XMP");
            //console.log("GIF checking XML: " + XMPinfo);
            //console.log(ImageData);
            // format XML
            xml_raw = XMPinfo;
            xml_formatted = formatXml(xml_raw);
            xml_escaped = xml_formatted.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/ /g, '&nbsp;').replace(/(?:\r\n|\r|\n)/g, '\n').replace(/'> <'/g, '\n');
            // multi columns in one row
            if (XMPinfo.length > 4)
                sOut += '<td class=\"xmp tabmetadata\">XMP:<br/><pre><code>' + xml_escaped + '</code></pre></td></tr>';
        }

        if (objImgType == 'WEBP Extended') {
            EXIFinfo = getImageDataforObjectRow(objID, "EXIF");
            IPTCinfo = getImageDataforObjectRow(objID, "IPTC");
            XMPinfo = getImageDataforObjectRow(objID, "XMP");
            // format XML
            xml_raw = XMPinfo;
            xml_formatted = formatXml(xml_raw);
            xml_escaped = xml_formatted.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/ /g, '&nbsp;').replace(/(?:\r\n|\r|\n)/g, '\n').replace(/'> <'/g, '\n');
            // multi columns in one row
            if (EXIFinfo.length > 5)
                sOut += '<td class=\"exif tabmetadata\">EXIF:<br/>' + EXIFinfo + '</td>';
            if (IPTCinfo.length > 5)
                sOut += '<td class=\"iptc tabmetadata\">IPTC:<br/>' + IPTCinfo + '</td>';
            if (XMPinfo.length > 4)
                sOut += '<td class=\"xmp tabmetadata\">XMP:<br/><pre><code>' + xml_escaped + '</code></pre></td></tr>';
        }
        //console.log("file '" + objLocFileCnv + "'" );
        //objLocFileCnv = objLocFileCnv.replace(" ",/%20/g);
        //console.log("file %20" + objLocFileCnv );
        if (comments.length > 0) {
            sOut += '<tr><td colspan=\"100%\">Comments: ' + comments + '</td>';
            //console.log("comments: " + comments);
        }
        sOut += '</tr>';
        sOut += sOutTN;
        sOut += '<tr><td class=\"rowimg\"><img src=\"' + objLocFileCnv + '\"></td></tr>';
        sOut += '<tr><td class=\"filename\" colspan=\"100%\">' + unescape(filename) + '</td></tr>';
        sOut += '</table>';
    } // end if image

    // 3rd table FONT, multi column
    if (objType == 'Font') {
        var id = objID;
        var flen = objLocFileCnv.length;
        var ext = objLocFileCnv.substr(objLocFileCnv.lastIndexOf('.') + 1);
        var fpath = objLocFileCnv.substr(0, flen - (ext.length + 1));
        var filename = getFileName(objLocFileCnv);
        // console.log("objlocfilecnv check: " + objLocFileCnv);
        // console.log("objlocfileurl check: " + objLocFileurl);
        // console.log("filename  check: " + filename);
        // console.log("objLocFile check: " + objLocFile);

        var fntname = filename.slice(0,filename.lastIndexOf("."));
        var fileext = filename.slice(filename.lastIndexOf(".") + 1);
//console.log("file extension check: " + fileext);
        
        switch (fileext)
        {
            case "woff":
            case "woff2":
            var chars = "";
            var puachars = "";
            var noofGlyphs = 0;
//console.log("fontname = " + fontname);
            filename = filename.toLowerCase();
            var lcfontname = '';
             ;
            if (fontname)
                lcfontname = fontname.toLowerCase();
            if(((filename.indexOf("icon") != -1 || filename.indexOf("fontawesome") != -1  || lcfontname.indexOf("icon") != -1) && filename.indexOf("woff2") == -1 ) )
            {

                var jsondata = getWoffFontDetails(objLocFileCnv);
//console.log(jsondata);
                $.each(jsondata, function (index, data) {
//console.log(index, data)
                    switch(index)
                    {
                        case "fontname":
//console.log("fontname = " + data);
                            fontname = data;
                            break;
                        case "cmap":
                        $.each(data, function (idx,cmapsubtable) {
//console.log(idx,cmapsubtable);
                            var startglyph = parseInt(cmapsubtable[0], 16);
                            var endglyph = parseInt(cmapsubtable[1], 16);

                            if(cmapsubtable[0] != "ffff")
                            {
                                for (var c = startglyph; c <= endglyph; c++) { // end of area 63743   // U+E000..U+F8FF = 57344 to 63743 = 6400
                                    puachars += String.fromCharCode(c);
                                    noofGlyphs++;
                                }
                            }
                        });
                        break;
                    } // end switch
                   });
                   var charsh = '';
                //    var charsh = '<span class="hchar">&nbsp;&nbsp;</span><span class="hchar">00</span><span class="hchar">01</span><span class="hchar">02</span><span class="hchar">03</span>';
                //    charsh = charsh + '<span class="hchar">04</span><span class="hchar">05</span><span class="hchar">06</span><span class="hchar">07</span>';
                //    charsh = charsh + '<span class="hchar">08</span><span class="hchar">09</span><span class="hchar">0a</span><span class="hchar">0b</span>';
                //    charsh = charsh + '<span class="hchar">0c</span><span class="hchar">0d</span><span class="hchar">0e</span><span class="hchar">0f</span>';
                    var chars128 = '';
                //    for (c = 32; c < 128; c++) chars128 = chars128 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
                    var chars256 = '';
                //    for (c = 32; c < 256; c++) chars256 = chars256 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
                    var chars2096 = '';
                //    for (c = 32; c < 2096; c++) chars2096 = chars2096 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
       
       

                }
            else
            {
                for (var c = 32; c <= 127; c++) { // standard
                    chars += String.fromCharCode(c);
                }
                var charsh = '<span class="hchar">&nbsp;&nbsp;</span><span class="hchar">00</span><span class="hchar">01</span><span class="hchar">02</span><span class="hchar">03</span>';
                charsh = charsh + '<span class="hchar">04</span><span class="hchar">05</span><span class="hchar">06</span><span class="hchar">07</span>';
                charsh = charsh + '<span class="hchar">08</span><span class="hchar">09</span><span class="hchar">0a</span><span class="hchar">0b</span>';
                charsh = charsh + '<span class="hchar">0c</span><span class="hchar">0d</span><span class="hchar">0e</span><span class="hchar">0f</span>';
                var chars128 = '';
                for (c = 32; c < 128; c++) chars128 = chars128 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
                var chars256 = '';
                for (c = 32; c < 256; c++) chars256 = chars256 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
                var chars2096 = '';
                for (c = 32; c < 2096; c++) chars2096 = chars2096 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
    
    
            }




            var objfile = filename;
            var name_with_ext = objfile.split('\\').pop().split('/').pop();
            var name_without_ext = name_with_ext.substring(name_with_ext.lastIndexOf("/") + 1, name_with_ext.lastIndexOf("."));
            var lc = jssavedir.slice(-1);

            if (lc != "/")
                jssavedir = jssavedir + '/';
            var fontdef = '<style type="text/css">@font-face {font-family: "'+name_with_ext+'";src: local("'+name_with_ext+'"), url("'+objLocFileCnv+'") format("opentype");}';
            var fontfamily = '.f'+id+' {font-family: "'+name_with_ext+'";font-size: 36px;}</style>';
            var fonthtml = '<h3>' + fontname + ' (' + name_with_ext + ')</h3>' + '<p class="f' + id + '">' + chars;
            var puafonthtml = '<h3><br/>Icon font (unicode)</h3>' + 'Number of Glyphs: ' + noofGlyphs + '<p class="f' + id + '" ><span class="fonticons">' + puachars + "</span>";
            var charset256 = '<h3>Character Set</h3>' + '<p class="f' + id + '"><span class="charset">' + charsh + chars256;
            var charset2096 = '<br/>Unicode<br/><span class="charset">' + charsh + chars2096;

            if((filename.indexOf("icon") != -1 || filename.indexOf("fontawesome") != -1) && filename.indexOf("woff2") == -1)
                fonthtml +=  puafonthtml;
            //fonthtml += '<br/>ASCII<span class="asciitab">' + charsh + chars128;
            else
                fonthtml += charset256;
            fonthtml + - '</span></p>';
            var allfonthtml = fontdef + fontfamily + fonthtml;
            var fontinfo_phenx = '<iframe class="phenx_font" src="/toaster/win_tools/phenx/www/font_info.php?fontfile=' + objLocFileurl + '"></iframe>';
            sOut += '<tr>';
            sOut += '<td colspan=\"100%\">' + allfonthtml + '</td>';
            sOut += '</tr>';
            sOut += '</table>';
            sOut += '<br>';
            //console.log("fileext = '" + fileext + "'");
 
            break;

            case "otf":
            case "ttf":
                var chars = "";
                for (var c = 32; c <= 127; c++) {
                    chars += String.fromCharCode(c);
                }
                var charsh = '<span class="hchar">&nbsp;&nbsp;</span><span class="hchar">00</span><span class="hchar">01</span><span class="hchar">02</span><span class="hchar">03</span>';
                charsh = charsh + '<span class="hchar">04</span><span class="hchar">05</span><span class="hchar">06</span><span class="hchar">07</span>';
                charsh = charsh + '<span class="hchar">08</span><span class="hchar">09</span><span class="hchar">0a</span><span class="hchar">0b</span>';
                charsh = charsh + '<span class="hchar">0c</span><span class="hchar">0d</span><span class="hchar">0e</span><span class="hchar">0f</span>';
                var chars128 = '';
                for (c = 32; c < 128; c++) chars128 = chars128 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
                var chars256 = '';
                for (c = 32; c < 256; c++) chars256 = chars256 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
                var chars2096 = '';
                for (c = 32; c < 2096; c++) chars2096 = chars2096 + ((c % 16 ? '' : '<br/><span class="hchar">' + c.toString(16) + ' </span>') + '<span class="fchar">' + String.fromCharCode(c) + '</span>');
                var objfile = filename;
                var name_with_ext = objfile.split('\\').pop().split('/').pop();
                var name_without_ext = name_with_ext.substring(name_with_ext.lastIndexOf("/") + 1, name_with_ext.lastIndexOf("."));
                var lc = jssavedir.slice(-1);

                if (lc != "/")
                    jssavedir = jssavedir + '/';
                //var fontdef = '<style type="text/css">@font-face {font-family: "'+name_with_ext+'";src: local("'+name_with_ext+'"), url("'+objLocFileCnv+'") format("opentype");}';
                //var fontfamily = '.f'+id+' {font-family: "'+name_with_ext+'";font-size: 36px;}</style>';
                var fonthtml = '<h3>' + fontname + ' (' + name_with_ext + ')</h3>' + '<p class="f' + id + '">' + chars;
                var charset256 = '<h3>Character Set</h3>' + '<p class="f' + id + '"><span class="charset">' + charsh + chars256;
                var charset2096 = '<br/>Unicode<br/><span class="charset">' + charsh + chars2096;

                //fonthtml += '<br/>ASCII<span class="asciitab">' + charsh + chars128;
                fonthtml += charset256;
                fonthtml + - '</span></p>';
                //var allfonthtml = fontdef + fontfamily + fonthtml;
                var fontinfo_phenx = '<iframe class="phenx_font" src="/toaster/win_tools/phenx/www/font_info.php?fontfile=' + objLocFileurl + '"></iframe>';
                sOut += '<tr>';
                sOut += '<td colspan=\"100%\">' + fonthtml + '</td>';
                sOut += '</tr>';
                sOut += '</table>';
                sOut += '<br>';
                //console.log("fileext = '" + fileext + "'");
                    sOut += fontinfo_phenx;
                break;
            case "svg":
                console.log("expanding SVG font");
                 //var allfonthtml = fontdef + fontfamily + fonthtml;
                var fontinfo_viewsvg = '<iframe class="svg_font" src="/win/win_tools/viewsvg/font_info.php?fontfile=' + objLocFileCnv + '"></iframe>';
                //console.log("fileext = '" + fileext + "'");
                sOut += fontinfo_viewsvg;
                break;
            default:
                break;
        }


    } // end if font
    return sOut;
}

function extractComments(textstring) {
    //var rx = textstring.match(/(?:\/\*(?:[\s\S]*?)\*\/)|(?:\/\/(?:.*)$)/gm);
    var rx = textstring.match(/(?:\/\*(?:[\s\S]*?)\*\/)|(?:([\s;])+\/\/(?:.*)$)/gm);
    return rx;
}

/* Formating function for row details - image optimisation*/
function fnFormatOptImgDetails(oTable, nTr) {
    var oSettings = oTable.fnSettings();
    var colMimeType = 0;
    var colTransBytes = 0;
    var col_id = 0;
    // lookup column containing the transbytes
    for (var col = 1; col < 6; col++) {
        col_nm = oSettings.aoColumns[col].sTitle;
        if (col_nm == 'Original Size') {
            colTransBytes = col;
            break;
        }
    }
    //console.log('mimetype col:' + colMimeType);
    var aData = oTable.fnGetData(nTr);
    var objID = aData[1];
    //var objType = aData[2];
    //console.log('objType =' + objType);
    var objSrcURL = unescape(aData[2]); // displayed as column
    // also 4 , shortname shown as column
    var objLocFile = aData[3]; // not displayed as column
    var objImgType = aData[6];
    //console.log('objImgType =' + objImgType);
    var ObjLabel = "Full URL";
    var TransBytes = aData[colTransBytes];
    var mimetype = aData[colMimeType];
    //console.log("mimetype = " + mimetype);
    var windir = objLocFile.substr(1, 1);
    //console.log("char = " + windir);
    var objLocFileurl = '';
    var objLocFileCnv = '';
    if (windir == ':') {
        objLocFileurl = objLocFile.substr(2); // strip the c:\ from the front
        objLocFileCnv = objLocFileurl.replace(/[/\\*]/g, '\/');
        //console.log("WIN objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
    }
    else { // linux
        if(objLocFile.indexOf("/usr/share") !== -1)
        { // local
        objLocFileurl = objLocFile.substr(10); // strip the /usr/share from the front
        objLocFileCnv = objLocFileurl.replace(/[/\\*]/g, '\/');
        }
        else // webpagetoaster.com
            objLocFileCnv = objLocFile;
//console.log("LINUX objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
    }
    objLocFileCnv.trim();
    //console.log("objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv)
    //console.log(objID + " = " + objType+ ": " + objImgType);

    var hdrinfo = getHeadersForObjectRow(objID);
    var hdranalysis = analyseHeader(objID, hdrinfo.toString(), mimetype, false);
    //console.log('hdr raw: '+hdrinfo);
    //console.log('hdr str: '+hdrinfo.toString());
    //console.log('hdr analysis: '+hdranalysis);

    if (objSrcURL.indexOf('base64') > 0) {
        objSrcURL = objSrcURL.substr(0, 32) + "...";
        objLabel = "Inline Image";
    }
    var sOut = '';
    // 1st table, 2 column
    sOut += '<table id="tabobjectinfo" class="tabobjectinfo" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px;background-color:#CDCDCD;">';
    sOut += '<caption>Object Information</caption>';
    sOut += '<tr><td>' + ObjLabel + ':</td><td class=\"objlabel\">' + objSrcURL + '</td></tr>';// fullname
    sOut += '<tr><td>Save Path:</td><td>' + objLocFileCnv + '</td></tr>';// fullname
    //    sOut += '<tr><td>Extra info:</td><td>further details here</td></tr>';
    sOut += '</table><br/>';
    var toasturlfolder = jssavedir;
    var escapedFilepath = objLocFileCnv.replace(/\\/g, "/");
    var originalimage = encodeURIComponent(escapedFilepath);
    //var filename = getFileName(escapedFilepath);
    var filename = escapedFilepath.substring(escapedFilepath.lastIndexOf("/") + 1, escapedFilepath.lastIndexOf("."));
//console.log("opt fp: "+escapedFilepath);
console.log("opt fn: "+filename);
    // imgsplit2 iframe
    sOut += '<iframe id="imgsplit_' + objID + '" class="imgsplit2" src="/toaster/iframe_splitimage.php?path=' + toasturlfolder + '&originalimg=' + originalimage + '&fn=' + filename + '"></iframe>';
console.log("sOut: "+ sOut);
    return sOut;
}

function displayObjectDetail(objID, objSrcURL) {
    var o = '';
    var i = viewObjID;
    var c = NewObj.length;
    var dt = '';
    var ft = '';
    var mt = '';
    var cc = false;
    if ($.isNumeric(objID) === false) {
        //console.log("pre obj id: " + i);
        // get id of current object through looking up the url
        objSrcURL = $("#headerobject").html();
        if (objSrcURL == 'Root Object Headers')
            objSrcURL = unescape(NewObj[0]['Object source']);
        //console.log("obj url: " + objSrcURL);
        hdrinfo = '';
        $.each(NewObj, function (key, val) {
            o = this['Object source'];
            if (o == objSrcURL) {
                i = Number(this['id']);
                //console.log("obj id: " + i);
            }
        });
        if (objID == '-') {
            //console.log("obj change -");
            objID = i - 1;
            if (objID < 0)
                objID = 0;
        }
        if (objID == '+') {
            //console.log("obj change + for max objs:" + c);
            objID = i + 1;
            if (objID >= c)
                objID = c;
        }
        if (objID == '=') {
            var cc = true;
            //console.log("obj change - for max objs:" + c);
            objID = i;
            if (objID >= c)
                objID = c;
        }
        viewObjID = objID;
        // get source url of new object
        var o = objID + " " + unescape(NewObj[objID]['Object source']);
        //console.log('"'+o+'"');
        var $btn = $('#divdd').find('#objitemslist');
        $btn.html(o + ' <span class="caret"></span>');
        objSrcURL = unescape(NewObj[objID]['Object source']);
    }
    dt = NewObj[objID]['Domain type'];
    ft = NewObj[objID]['Object type'];
    mt = NewObj[objID]['Mime type'];
    sc = NewObj[objID]['HTTP status'];
    //console.log('sc: ' + sc);

    var hdrinfo = getHeadersForObjectRow(objID);
    var hdranalysis = analyseHeader(objID, hdrinfo.toString(), mimetype, false);
    if (cc === true) {
        //console.log("cchdrs = " + cchdrs);
        hdrinfo = cchdrs.toString();
        //console.log('in CC headers' + hdrinfo);
        hdrinfo = hdrinfo.split("\r\n").join("<br/>");
        //console.log('in CC headers' + hdrinfo.toString());
        hdranalysis = analyseHeader(objID, hdrinfo, mimetype, false);
    }
    // split hdrinfo into new table and table rows
    //console.log(hdrinfo);
    var rows = '';
    var hdrarray = hdrinfo.split("<br/>");
    //console.log(hdrarray)
    var main = '';
    var directives = '';
    var directivesonly = '';
    $.each(hdrarray, function (key, val) {
        var pos = val.indexOf(':');
        if (pos > 0) {
            main = val.substr(0, pos);
            directives = val.substr(pos);
            directivesonly = val.substr(pos + 2);
            //console.log("'"+directivesonly+"'");
        }
        else {
            main = val;
            directives = '';
            directivesonly = '';
        }

        switch (main.toLowerCase()) {
            case 'cache-control':
                row = '<tr><td class=\"ccbrown\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                break;
            case 'last-modified':
                row = '<tr><td class=\"ccbrown\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                lmd = directivesonly;
                break;
            case 'expires':
                if (directivesonly == '-1')
                    row = '<tr><td class=\"ccamber\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                else
                    row = '<tr><td class=\"ccbrown\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                break;
            case 'etag':
                var diro = directivesonly.trim();
                var lendir = diro.length;
                //console.log(directivesonly +": length directive: " + lendir);
                if (lendir == 2) // blank etags: ""
                {
                    row = '<tr><td class=\"ccgreen\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                }
                else
                    if (hasNumbers(directivesonly) === false)
                        row = '<tr><td class=\"ccred\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                    else
                        row = '<tr><td class=\"ccbrown\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                break;
            case 'age':
                row = '<tr><td class=\"ccbrown\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                break;
            case 'pragma':
                row = '<tr><td class=\"ccred\"><div class=\"cc\">' + main + directives + '</div></td></tr>';
                break;
            case 'set-cookie':
                row = '<tr class=\"cookies\"><td><div>' + main + directives + '</div></td></tr>';
                break;
            default:
                row = '<tr><td>' + main + directives + '</td></tr>';
        }
        rows = rows + row;
    });
    var newhdrtable = '<table id=\"hdrs\" class=\"headersn\">' + rows + '</table>';
    //console.log(newhdrtable);

    // send to the screen
    $("#headerobject").html(objSrcURL);
    $("#objectinfo").html('<span class=\"sc\">' + sc + '</span>' + '<span class=\"sc\">' + ft + '</span>' + '<span class=\"sc\">(' + mt + ')</span>');
    $("#headersn").html(newhdrtable);
    $("#headeranalysis").html(hdranalysis);
    $('.headersn td').click(function () {
        $(this).each(function () {
            var classes = ['ccbrown', 'ccgreen', 'ccamber', 'ccred', ''];
            this.className = classes[($.inArray(this.className, classes) + 1) % classes.length];
        });
    });
}

function hasNumbers(t) {
    var regex = /\d/g;
    //console.log(t+': ' + regex.test(t));
    return regex.test(t);
}


function getHeadersForObjectRow(id) {
    hdrinfo = '';
    $.each(Headers, function (key, val) {
        var hfound = false;
        $.each(val, function (k, v) {
            //extrainfo = extrainfo + 'key: ' + k + ':<br/>';
            //extrainfo = extrainfo + 'val: ' + v + ':<br/>';
            //console.log("id found (" + id + ") :key: " + k + " val: " + v);-
            if (k == 'id' && v == id) {
                //console.log(k + ": " + id + "; " + v);
                hfound = true;
            }
            if (k == 'Headers' && hfound === true) {
                hdrinfo = hdrinfo + v;
                hfound = false;
                //	var arrayLength = v.length;
                //	for (var i = 0; i < arrayLength; i++) {
                //		hdrinfo = hdrinfo + v[i];
            }
            //}
        });
    });


    hdrinfo = hdrinfo.replace(/\n/g, "<br/>");
    return hdrinfo;
}
function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function getImageDataforObjectRow(id, type) {
    var extrainfo = '';
    var exifinfo = '';
    var iptcinfo = '';
    var xmpinfo = '';
    var comments = '';
    var structure = '';
    var iccprofile = '';
    var app12info = '';
    var cstr = '';
    var sstr = '';
    var edata = '';
    idfound = false;
    if (ImageData.length > 0) {
        //console.log("Processing image data: " + type);
        $.each(ImageData, function (key, val) {
            row = val['id'];


            //console.log("Processing image data row: " + row + " = " + id);
            //extrainfo = extrainfo + key + ':<br/>';
            //extrainfo = extrainfo + id + ':<br/>';

            $.each(val, function (kx, vx) {
                //console.log("image data found " + kx);
                if (kx == 'id' && vx == id) {
                    //console.log("image info found for row id: " + id);
                    ////console.log(kx + ": " + id + "; " + vx);
                    idfound = true;
                }

                //console.log("image data found " + kx);
                //exifd = val['EXIF'];
                //iptcd = vx['IPTC'];
                //console.log("exif info found " + exifd);
                //console.log("iptc info found " + iptcd);
                //console.log(kx + ' ' + val['EXIF']);
                if (kx == "EXIF" && idfound === true) {
                    //console.log("EXIF found: " + vx );
                    exifinfo = vx;
                    //exifinfo = val['EXIF'];
                } // end EXIF
                if (kx == "IPTC" && idfound === true) {
                    //console.log("iptc vx="+vx);
                    if (vx !== null)
                        iptcinfo = iptcinfo + vx;
                } // end IPTC

                if (kx == "APP12" && idfound === true) {
                    //console.log("app12 vx="+vx);
                    if (vx !== null)
                        app12info = app12info + vx;
                } // end APP12

                if (kx == "XMP" && idfound === true) {
                    //console.log(vx);
                    $.each(vx, function (ky, vy) {
                        //console.log("ky XMP: " + ky + ": " + vy); // individual letters
                        //console.log(vx);
                        xmpstr = vy;
                        xmpinfo = xmpinfo + xmpstr;
                    });
                } // end XMP
                if (kx == "ICC" && idfound === true) {
                    //console.log(vx);
                    $.each(vx, function (ky, vy) {
                        //console.log("ky XMP: " + ky + ": " + vy); // individual letters
                        //console.log(vx);
                        iccstr = vy;
                        iccprofile = iccprofile + iccstr + "\r\n";
                    });
                } // end ICC
                if (kx == "Comments" && idfound === true) {
                    $.each(vx, function (ky, vy) {
                        //console.log("ky Comment: " + ky + ": " + vy);
                        //console.log(vy);
                        $.each(vy, function (kz, vz) {
                            //console.log("kz Comment: " + kz + ": " + vz);
                            //console.log(vz);
                            cstr = cstr + vz;
                        });
                    });
                    //console.log(id+" Comments: "+cstr);
                    comments = comments + cstr + "<br/>";
                } // end comments

                if (kx == "Structure" && idfound === true) {
                    $.each(vx, function (ky, vy) {
                        //console.log("ky Structure: " + ky + ": " + vy);
                        //console.log(vy);
                        $.each(vy, function (kz, vz) {
                            //console.log("kz Structure: " + kz + ": " + vz);
                            //console.log(vz);
                            sstr = sstr + vz;
                        });
                    });
                    //console.log(id+" Structure: "+sstr);
                    structure = structure + sstr + "<br/>";
                } // end structure

            });
            idfound = false;
        });
    }
    //console.log('sending ' + type);
    switch (type) {
        case "EXIF":
            //console.log('end: '+exifinfo);
            return exifinfo;
            break;
        case "IPTC":
            return nl2br(iptcinfo);
            break;
        case "XMP":
            return xmpinfo;
            break;
        case "Comments":
            return comments;
            break;
        case "ICC":
            return iccprofile;
            break;
        case "Structure":
            return structure;
            break;
        case "APP12":
            return app12info;
            break;
        default:
            return '';
            break;
    }
    return '';
}
function gm_initialize() {
    var rootlatlongsplit = rootlatlong.split(',');
    rootlat = parseFloat(rootlatlongsplit[0]);
    rootlong = parseFloat(rootlatlongsplit[1]);
    var mapOptions = {
        center: new google.maps.LatLng(65, -105), //rootlat,rootlong
        zoom: 4,
        minZoom: 4,
        maxZoom: 9
    };
    map = new google.maps.Map(document.getElementById("map-canvas"),
        mapOptions);
    //console.log("gm_init called: rootlatlong: " + rootlatlong);
    google.maps.event.addDomListener(window, "resize", function () {
        //console.log("gm reizing");
        var center = new google.maps.LatLng(rootlat, rootlong);
        google.maps.event.trigger(map, "resize");
        map.setCenter(center);
    });
}
function moveToLocation(lat, lng) {
    var center = new google.maps.LatLng(lat, lng);
    map.panTo(center);
    //console.log("gm_markers");
}
function gm_markers() {
    var center = new google.maps.LatLng(rootlat, rootlong);
    //map.setCenter(center);
    //console.log("gm_markers");

    // markers
    //map marker - NCC group home Manchester or user position elsewhere
    var alatlong = extlatlong.split(',');
    //console.log(extloc + "; extlatlong: "+ extlatlong);
    var NCCLatlng = new google.maps.LatLng(Number(alatlong[0]), Number(alatlong[1]));
    var NCCmarker = new google.maps.Marker({
        position: NCCLatlng,
        map: map,
        icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
        title: extloc
    });
    ReversedDomainsList = DomainsList;
    ReversedDomainsList.reverse();
    var DomainLatlng = new google.maps.LatLng(rootlat, rootlong);
    var Domainmarker = '';
    var linecolor = '';
    // for each line in the domains table, look for shard, CDN and 3P and plot
    $.each(DomainsList, function () {
        //console.log(this);
        var domainName = this['Domain Name'];
        var domainRef = this['Domain Type'];
        var location = this['Location'];
        var network = this['Network'];
        if (network !== '')
            network = " (" + network + ")";
        var edgename = this['Edge Name'];
        var edgeloc = this['Edge Loc'];
        var edgeIP = this['Edge IP'];
        var lat = this['Latitude'];
        var longt = this['Longitude'];
        var domcount = this['Count'];
        var distance = this['Distance'];
        var loc = '';
        if (edgeloc === '')
            loc = location;
        else
            loc = edgeloc;
        //console.log(domainName + ": " + edgeloc);
        if ((!lat && !longt) || (lat === '' && longt === '') || (lat == 0 && longt == 0)) {
            //return false; // jquery break
            return; // jquery continue
        }
        // add lat and long randomness
        //var randomNumber = (Math.floor(Math.random() * 201) - 100)/900;
        //var randomNumber2 = (Math.floor(Math.random() * 201) - 100)/900;
        //lat += randomNumber;
        //longt += randomNumber2;
        //console.log(domainName + ": " + domainRef);
        // set marker depending upon type
        switch (domainRef) {
            case "Shard":
                //map marker - Shard
                DomainLatlng = new google.maps.LatLng(lat, longt);
                Domainmarker = new google.maps.Marker({
                    position: DomainLatlng,
                    map: map,
                    icon: 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
                    title: domainName + ', ' + loc + network
                });
                linecolor = '#FFFF00';
                break;
            case "CDN":
                //map marker - CDN
                DomainLatlng = new google.maps.LatLng(lat, longt);
                Domainmarker = new google.maps.Marker({
                    position: DomainLatlng,
                    map: map,
                    icon: 'https://maps.google.com/mapfiles/ms/icons/orange-dot.png',
                    title: domainName + ', ' + loc + network
                });
                linecolor = '#FFA500';
                break;
            case "3P":
                //map marker - 3P
                DomainLatlng = new google.maps.LatLng(lat, longt);
                Domainmarker = new google.maps.Marker({
                    position: DomainLatlng,
                    map: map,
                    icon: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                    title: domainName + ', ' + loc + network,
                    zIndex: google.maps.Marker.MAX_ZINDEX - 1
                });
                linecolor = '#00FF00';
                break;
            case "Primary":
                //map marker - Primary
                DomainLatlng = new google.maps.LatLng(lat, longt);
                Domainmarker = new google.maps.Marker({
                    position: DomainLatlng,
                    map: map,
                    icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                    title: domainName + ', ' + loc + network,
                    zIndex: google.maps.Marker.MAX_ZINDEX + 1
                });
                linecolor = '#0000FF';
                break;
            default:
                break;
        }
        //console.log("drawing lines");
        // draw line back to ncc group marker
        // draw line
        var Domain2homeCoordinates = [
            NCCLatlng,
            DomainLatlng
        ];
        var stroke = 2;
        if (domcount >= 5)
            stroke = 5;
        else
            if (domcount >= 20)
                stroke = max(50, domcount);
        var Domain2home = new google.maps.Polyline({
            path: Domain2homeCoordinates,
            geodesic: true,
            strokeColor: linecolor,
            strokeOpacity: 1.0,
            strokeWeight: stroke
        });
        //console.log("setmap b4");
        Domain2home.setMap(map);
        //console.log("setmap after");
    });
}
function mainDisplay(browserenginever) {
    loadConfigFile(); // get config file;
    browserengineversion = browserenginever; // rescope the variable
    $('#input_output').remove();
    //document.getElementById("ttime").innerHTML= ttime;
    //document.getElementById("rdtime").innerHTML= rdtime;
    //document.getElementById("contime").innerHTML= contime;
    //document.getElementById("dnstime").innerHTML= dnstime;
    //document.getElementById("dstime").innerHTML= dstime;
    //document.getElementById("dsstime").innerHTML= dsstime;
    //document.getElementById("headers").innerHTML= headerStr;
    //document.getElementById("content").innerHTML= contentanalysis;

    // build content analysis
    document.getElementById("headeranalysis").innerHTML = "Number of header fields: " + headerfields.length + "<br />" + rootheaderanalysis;
    var basedomain = "Primary Domain: " + domain + "<br >";

    displayTableFileStats(true);
    displayTableFileOrdering();
    displayTableTimings();
    displayTableRootRedirs();
    displayTableNewObj('subset'); // or all
    displayTableNewObj('hdrs');
    displayTableNewObj('img');
    displayTableNewObj('css');
    displayTableNewObj('js');
    displayTableNewObj('fonts');
    displayTableNewObj('tpperformance');
    displayTableOptObj('optJPGimg');
    displayTableOptObj('optPNGimg');
    displayTableOptObj('optGIFimg');
    displayTableOptObj('optWEBPimg');
    displayTableOptObj('optBMPimg');
    displayTableOptObj('optGIFanim');
    displayTable3PObjects(browserenginever);
    displayTable3PTagManagers();

    displayTableLinks();
    displayTableGZIPFiles();
    displayTableErrors();
    // Cache Analysis display, 1, 2, 3 - Hackathon December 2014
    DisplayCacheHeaderDates();
    displayChartCacheAnalysis();
    displayTableCacheAnalysis();

    displayTableDomains();
    displayTableDomains3P();
    displayTableDomainsLocations();
    //console.log(JSON.stringify(GzipTotals));
    displayTableReverseIP();
    //displayTableTests();
    displayTableRules();
    // display table of css seleccctor usage
    displayTableCssSelectors();
    //populate ibject view select box
    popobjviewselect();
    //document.getElementById("diags").Text= Diags;
    //console.log(Diags);
    displayTable3PChain();
    createTPChart(2); // options 1 or 2 only
    displayFonts();
    displayMaturityScore(browserenginever);
    displayTable3PContent();
    // perform JavaScript after the document is scriptable.
    $(function () {
        // setup ul.tabs to work as tabs for each div directly under div.panes
        $("ul.tooltabs").tooltabs("div.panes > div");
        $("ul.subtabs").tooltabs("div.subpanes > div");
    });
    displayPageStatus('');
    $("ul.tooltabs").click(function () {
        //console.log("tab clicked");
        $(window).resize();
        google.maps.event.trigger(map, 'resize');
    });
    $("ul.subtabs").click(function () {
        //console.log("tab clicked");
        $(window).resize();
    });

    $("#removeClass").click(function () {
        $('.headersn td').removeClass('ccbrown');
        $('.headersn td').removeClass('ccamber');
        $('.headersn td').removeClass('ccgreen');
        $('.headersn td').removeClass('ccred');
    });
    $("#objPrevious").click(function () {
        var checkStatus = $('#shcookies').is(':checked');
        displayObjectDetail('-');
        $('#chresult').html('');
        $('#shcookies').prop('checked', checkStatus);
        var getType = '.cookies';
        if (checkStatus === false) {
            $(getType, '.headersn').hide();
        } else {
            $(getType, '.headersn').show();
        }
    });
    $("#objNext").click(function () {
        var checkStatus = $('#shcookies').is(':checked');
        displayObjectDetail('+');
        $('#chresult').html('');
        $('#shcookies').prop('checked', checkStatus);
        //console.log(checkStatus);
        var getType = '.cookies';
        if (checkStatus === false) {
            $(getType, '.headersn').hide();
        } else {
            $(getType, '.headersn').show();
        }
    });
    $('body').on('keydown', function (args) {
        if (args.keyCode == 37) {
            //console.log('left');
            $("#objPrevious").click();
            return false;
        }
        if (args.keyCode == 39) {
            //console.log('right');
            $("#objNext").click();
            return false;
        }
    });
    $('#selectType :checkbox').click(function () {
        var getType = '.' + $(this).attr('class');
        var checkStatus = $(this).is(':checked');
        if (checkStatus === false) {
            $(getType, '.headersn').hide();
        } else {
            $(getType, '.headersn').show();
        }
    });
    // var button = document.getElementById('btn-download');
    // button.addEventListener('click', function (e) {
    //     var canvas = document.querySelector('canvas');
    //     var dataURL = canvas.toDataURL('image/png');
    //     var redirectWindow = window.open(dataURL, '_blank');
    //     redirectWindow.location;
    // });
    $('#toggle_bgcolour').on('click', function (e) {
        $('#TPnetwork').toggleClass("bgwhite"); //you can list several class names
        if (bgcol == 'd') {
            bgcol = 'w';
        }
        else {
            bgcol = 'd';
        }
        e.preventDefault();
        var netval = $("input[type='radio'][name='netlevel']:checked").val();
        //console.log(netval + " clicked");
        thirdpartynetworknodes_data = [];
        thirdpartynetworklinks_data = [];
        visjs_thirdpartynetwork(netval);
    });
    $('#toggle_orientation').on('click', function (e) {
        if (TPorientation == "p") {
            $('#TPnetwork').addClass('landscape');
            $('#TPnetwork').removeClass('portrait');
            TPorientation = "l";
        }
        else {
            $('#TPnetwork').addClass('portrait');
            $('#TPnetwork').removeClass('landscape');
            TPorientation = "p";
        }
        e.preventDefault();
    });
    $('#focusnode').on('click', function (e) {
        // find search term in node list
        var term = $("#focussearch").val().toLowerCase();
        console.log("focussing on " + term);
        var found = false;
        for (k in thirdpartynetworknodes_data) {
            var label = thirdpartynetworknodes_data[k]['label'].toLowerCase();
            if (label.indexOf(term) !== -1) {
                network.focus(thirdpartynetworknodes_data[k]['id'], { scale: 1.5 });
                network.selectNodes([thirdpartynetworknodes_data[k]['id']]);
                break;
            }
        }
    });
    $('#fitnodes').on('click', function (e) {
        network.fit();
    });
    $("input[type='radio'][name='netlevel']").click(function () {
        var netval = $("input[type='radio'][name='netlevel']:checked").val();
        //console.log(netval + " clicked");
        thirdpartynetworknodes_data = [];
        thirdpartynetworklinks_data = [];
        visjs_thirdpartynetwork(netval);
    });
    $("input[type='radio'][name='netshape']").click(function () {
        var netval = $("input[type='radio'][name='netlevel']:checked").val();
        nodeshape = $("input[type='radio'][name='netshape']:checked").val().toLowerCase();
        console.log(nodeshape + " clicked");
        if (nodeshape == "dot") {
            $("#export_button").attr("disabled", true);
            $("#export_button").addClass("button3");
        }
        else {
            $("#export_button").removeAttr("disabled");
            $("#export_button").removeClass("button3");
        }
        thirdpartynetworknodes_data = [];
        thirdpartynetworklinks_data = [];
        visjs_thirdpartynetwork(netval);
    });
    $("input[type='radio'][name='netcolouring']").click(function () {
        var netval = $("input[type='radio'][name='netlevel']:checked").val();
        nodecolouring = $("input[type='radio'][name='netcolouring']:checked").val().toLowerCase();
        console.log("node colouring " + nodecolouring + " clicked");
        thirdpartynetworknodes_data = [];
        thirdpartynetworklinks_data = [];
        visjs_thirdpartynetwork(netval);
    });
    $("input[type='radio'][name='netlayout']").click(function () {
        var netval = $("input[type='radio'][name='netlayout']:checked").val();
        var options = '';
        switch (netval) {
            case "N":
                options = {
                    layout: {
                        randomSeed: undefined,
                        improvedLayout: true,
                        hierarchical: {
                            enabled: false,
                        }
                    }
                }
                break;
            case "UD":
                options = {
                    layout: {
                        randomSeed: undefined,
                        improvedLayout: true,
                        hierarchical: {
                            enabled: true,
                            levelSeparation: 150,
                            nodeSpacing: 100,
                            treeSpacing: 200,
                            blockShifting: true,
                            edgeMinimization: true,
                            parentCentralization: true,
                            direction: 'UD',        // UD, DU, LR, RL
                            sortMethod: 'directed'   // hubsize, directed
                        }
                    }
                }
                break;
            case "LR":
                options = {
                    layout: {
                        randomSeed: undefined,
                        improvedLayout: true,
                        hierarchical: {
                            enabled: true,
                            levelSeparation: 150,
                            nodeSpacing: 100,
                            treeSpacing: 200,
                            blockShifting: true,
                            edgeMinimization: true,
                            parentCentralization: true,
                            direction: 'LR',        // UD, DU, LR, RL
                            sortMethod: 'directed'   // hubsize, directed
                        }
                    }
                }
                break;
        } // end switch
        network.setOptions(options);
    });
    if (window.innerHeight == screen.height)
        $('#TPnetwork').css('height', window.innerHeight * 0.92);
    else
        $('#TPnetwork').css('height', window.innerHeight * 0.91);
    $(window).resize(function () {
        var bIsFullScreen = window.innerHeight == screen.height;
        if (window.innerHeight == screen.height)
            $('#TPnetwork').css('height', window.innerHeight * 0.92);
        else
            $('#TPnetwork').css('height', window.innerHeight * 0.91);
    });
    // button "View Images" - get array of the contents of the 4th column on the table
    $('#ViewImages').click(function () {
        //alert("View images clicked");
        //         var cells = new Array();
        //        $('#images_table tr td:nth-child(4)').each(function(){
        //			var celldata = $(this).html();
        //
        //				var h1 = celldata.indexOf('a href=') + 8;
        //				var href = celldata.substr(h1);
        //				var h2 = href.indexOf('"');
        //				href = href.substr(0,h2);
        //
        //	            cells.push(href);
        //
        //          });
        //console.log(cells);

        var cellids = new Array();
        $('#images_table tr td:nth-child(2)').each(function () {
            var celldata = $(this).html();

            cellids.push(celldata);
        });
        //console.log(cellids);

        $('a#ViewHAR').attr({ target: '_blank', href: HarFile });

        // get the domain
        var domainURL = NewObj[0]['Object file'];
        //lookup the url and get the local file from the object array
        $("#theDiv").html('');
        var imagestoshow = new Array();
        $.each(cellids, function (key, val) {
            var local = '';
            var remote = '';
            var csize = '';
            var imgdim = '';
            //console.log("row id: " + val);
            if (isNaN(val) === false) {
                remote = unescape(NewObj[val]['Object source']);
                local = NewObj[val]['Object file'];
                csize = parseInt(NewObj[val]['Content length transmitted']).toLocaleString() + " bytes";
                imgdim = NewObj[val]['Image actual size'];

                var windir = local.substr(1, 1);
                //console.log("char = " + windir);
                if (windir == ':') {
                    local = local.substr(3); // strip the c:\ from the front
                    //console.log("WIN objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
                }
                else { // linux
                    if(local.indexOf("/usr/share") !== -1)
                    { // local
                    var objLocFileurl = local.substr(10); // strip the /usr/share from the front
                    local = objLocFileurl.replace(/[/\\*]/g, '\/');
                    }
                    else // webpagetoaster.com
                        local = local;
            //console.log("LINUX objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
                }
                if (imgdim != '1 x 1 px' && imgdim != '2 x 2 px' && parseInt(NewObj[val]['Content length transmitted']) > 0) {
                    if(local.substring(0,4) != "http")
                        local = "/" + local;
                    // adjust linux path
                    if(local.indexOf("/usr/share") !== -1)
                    { // local
                        local = local.substr(11); // strip the /usr/share from the front
                    }
                    if(local.indexOf("/usr/share") !== -1)
                    { // local
                        local = local.substr(11); // strip the /usr/share from the front
                    }
                    if(local.indexOf("//toast") == 0)
                    { // local
                        local = local.substr(1); // strip the first / from the front
                    }
                    local = local.replace(/[/\\*]/g, '\/');
                    console.log(local);
                    shortname = getShortName(remote);
                    caption = shortname + "<br/>" + csize;
                    imagestoshow.push(local);
                     $("#theDiv").append('<figure><img id="theImg" src=\"' + local + '" title="' + remote + '"><figcaption>' + caption + '</figcaption></figure>');
                }

            } // end if value is not false
        }); // end for each
        //console.log(imagestoshow);
        boolShowImages = true;
        $('#ViewImages').unbind('click');
    }); // end function to view images

    $('#HideImages').click(function () {
        boolShowImages = false;
        $("#theDiv").html('');
    }); // end function to view images

    $('#StatStyles').click(function () {
        var statval = $(this).val();

        if (statval == 'HL') {
            displayTableFileStats(false);
            $(this).val('LL');
            $('#StatStyles > span').removeClass("glyphicon-alert").addClass("glyphicon-info-sign");
        }
        else {
            displayTableFileStats(true);
            $(this).val('HL');
            $('#StatStyles > span').removeClass("glyphicon-info-sign").addClass("glyphicon-alert");
        }
        //console.log("Stat Styles clicked: " +statval);
    });

    $('#theDiv').click(function () {
        var color = $('#theDiv img').css('background-color');
        //console.log(color);
        if (color !== 'rgb(255, 255, 255)') {
            $('#theDiv img').css("background-color", "white");
        }
        else {
            $('#theDiv img').css("background-color", "#1E1E1E");
        }
    });
    var initURL = unescape(NewObj[0]['Object source']);
    // initial detailed display
    displayObjectDetail(0, initURL);

    $('#objCacheChk').click(function () {
        //console.log("url: " + $('#headerobject').html());
        //console.log("lmd: " + lmd);
        //console.log("etag: " + etag);
        $.ajax({
            type: "GET",
            url: "/toaster/cacheheadertest.php",
            data: { url: $('#headerobject').html(), dtm: lmd, etag: etag },
            success: function (msg) {
                cchdrs = msg;
                displayObjectDetail('=');
                //$('#chresult').html('If-Modified-Since result: ' + msg);
                dmsg = msg;
                //console.log(msg);
            }
        }); // Ajax Call
    }); //event handler

    gen_thumbnails();
    //GOOGLE MAPS
    // init google maps
    gm_initialize();
    // set markers
    gm_markers();
    // // setup thirdparty chart options
    $('input[name="tphsz"]').change(function () {
        console.log("radio button checked");
        if ($('#tphd1').prop('checked')) {
            console.log('Option 1 is checked!');
            createTPChart(1);
        }
        else {
            if ($('#tphd2').prop('checked')) {
                console.log('Option 2 is checked!');
                createTPChart(2);
            }
            else {
                if ($('#tphd3').prop('checked')) {
                    console.log('Option 3 is checked!');
                    createTPChart(3);
                }
                else {
                    console.log('Option 4 is checked!');
                    createTPChart(4);
                }
            }
        }
    });
} // end function main display

function popobjviewselect() {
    //NewObj[objID]['Object source']
    for (i = 0; i < NewObj.length; i++) {
        //$('<option/>').val(NewObj[i]['Object source']).html(NewObj[i]['Object source']).appendTo('#objitems');
        //role="presentation"><a role="menuitem" tabindex="-1" href="#">Action
        var obj = unescape(NewObj[i]['Object source']);
        $('.dropdown-menu').append('<li><a role="menuitem" tabindex="-1" href="#"' + '</a>' + i + ' ' + obj + '</li>');
    }
    //$('#objitemslist').click(function(){
    //   var value = $(this).val();
    //   alert(value);
    //});
    $('#divdd.dropdown ul.dropdown-menu li a').click(function (e) {
        var $div = $(this).parent().parent().parent();
        var $btn = $div.find('button');
        $btn.html($(this).text() + ' <span class="caret"></span>');
        //alert($(this).text());
        $div.removeClass('open');
        e.preventDefault();
        var selitem = $(this).text();
        var item = selitem.split(' ');
        var idno = item[0];
        var src = item[1];
        //alert(idno + " = " + src);
        var checkStatus = $('#shcookies').is(':checked');
        displayObjectDetail(idno, src);
        $('#chresult').html('');
        $('#shcookies').prop('checked', checkStatus);
        //console.log(checkStatus);
        var getType = '.cookies';
        if (checkStatus === false) {
            $(getType, '.headersn').hide();
        } else {
            $(getType, '.headersn').show();
        }
        return false;
    });

}

function displayTableTests() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Id" + "</th>" + "<th>" + "Ruleset" + "</th>" + "<th>" + "Test Name" + "</th>" + "<th>" + "Result" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(Tests, function () {
        var tbl_row = "";
        test_result = "info";
        $.each(this, function (k, v) {
            //alert ("k=" + k + "; v = " + v);
            switch (k) {
                case "Result":
                    var vl = v.toString();
                    switch (vl.toLowerCase()) {
                        case 'fail':
                            test_result = "danger";
                            break;
                        case 'pass':
                            test_result = "success";
                            break
                        default:
                            test_result = "info";
                    }
                    tbl_row += '<td class="' + test_result + '">' + v + '</td>';
                    break;
                default:
                    tbl_row += "<td>" + v + "</td>";
            }
        })
        tbl_body += '<tr class="' + test_result + '">' + tbl_row + '</tr>';
        //console.log("table body: " + tbl_body);
    })
    $("#tests_table tbody").html(tbl_body);
}
function displayTableRules() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Rule No." + "</th>" + "<th>" + "Ruleset" + "</th>" + "<th>" + "Conformance" + "</th>" + "<th>" + "Recommendation" + "</th>" + "<th>" + "Test Result" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(Rules, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            //alert ("k=" + k + "; v = " + v);
            switch (k) {
                case "Result":
                    var vl = v.toString();
                    switch (vl.toLowerCase()) {
                        case 'fail':
                            test_result = "danger";
                            break;
                        case 'pass':
                            test_result = "success";
                            break
                        case 'warning':
                            test_result = "warning";
                            break;
                        case 'n/a':
                            test_result = "na";
                            break;
                        default:
                            test_result = "none";
                            break;
                    }
                    tbl_row += '<td class="' + test_result + '">' + v + '</td>';
                    break;
                default:
                    tbl_row += "<td>" + v + "</td>";
            }
        })
        tbl_body += '<tr class="' + test_result + '">' + tbl_row + '</tr>';
        //console.log("table body: " + tbl_body);
    })
    $("#rules_table tbody").html(tbl_body);
}


function displayTablePageStats() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Type" + "</th>" + "<th>" + "Stats" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(PageStats, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            tbl_row += "<td>" + k + "</td>";
            tbl_row += "<td>" + v + "</td>";
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("table row: " + tbl_row);
    })
    $("#pagestats_table tbody").html(tbl_body);
}
function displayTableFileOrdering() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Element" + "</th>" + "<th>" + "Source or DOM injection" + "</th>" + "<th>" + "File type" + "</th>" + "<th>" + "File name" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(FileOrderList, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            tbl_row += "<td>" + v + "</td>";
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("Order table row: " + tbl_row);
    })
    $("#fileordering_table tbody").html(tbl_body);

    //console.log("ordering data: " + FileOrderList);
    // create dataset for pie chart headers
    var datasetH = "[";
    var datasetB = "[";
    var len = FileOrderList.length;
    var countHeadJSb4 = 0;
    var countHeadJSMb4 = 0;
    var countHeadCSSb4 = 0;
    var countBodyJSb4 = 0;
    var countBodyCSSb4 = 0;

    var countHeadJS = 0;
    var countHeadJSM = 0;
    var countHeadCSS = 0;
    var countBodyJS = 0;
    var countBodyCSS = 0;
    $.each(FileOrderList, function (key, element) {
        //console.log('key: ' + key + '\n' + 'value: ' + element);

        //console.log('value 0: ' + element['Section']);
        //console.log('value 1: ' + element['File Type']);
        //console.log('value 2: ' + element['File']);
        if (element['Section'] == "HEAD") {
            if (element['File Type'] == "JavaScript") {
                countHeadJS += 1;
                if (element['Timing'] == 'source')
                    countHeadJSb4 += 1;
            }
            else {
                if (element['File Type'] == "JavaScript (Modernizr)") {
                    countHeadJSM += 1;
                    if (element['Timing'] == 'source')
                        countHeadJSMb4 += 1;
                }
                else {
                    countHeadCSS += 1;
                    if (element['Timing'] == 'source')
                        countHeadCSSb4 += 1;
                }
            }
        }
        if (element['Section'] == "BODY") {
            if (element['File Type'] == "JavaScript") {
                countBodyJS += 1;
                if (element['Timing'] == 'source')
                    countBodyJSb4 += 1;
            }
            else {
                countBodyCSS += 1;
                if (element['Timing'] == 'source')
                    countBodyCSSb4 += 1;
            }
        }

        //console.log(simpleObjInspect(elementv));
        //console.log("header data row: " + data_row);
    })

    // before version
    var pctHeadJS = (countHeadJSb4 + countHeadJSMb4) / (countHeadJSb4 + countHeadJSMb4 + countHeadCSSb4) * 100;
    var pctHeadCSS = (countHeadJSb4 + countHeadJSMb4) / (countHeadJSb4 + countHeadJSMb4 + countHeadCSSb4) * 100;
    datasetH += '["JavaScript",';
    datasetH += countHeadJSb4.toString() + '],';
    datasetH += '["StyleSheet",';
    datasetH += countHeadCSSb4.toString() + '],';
    datasetH += '["JavaScript (Modernizr)",';
    datasetH += countHeadJSMb4.toString() + ']';
    datasetH += "]";
    //console.log("header dataset: " + datasetH);
    var dsH = jQuery.parseJSON(datasetH);
    plotChartPieOrderingHdrsb4(dsH);
    if (countBodyJSb4 + countBodyCSSb4 > 0) {
        var pctBodyJSb4 = parseInt(countBodyJSb4 / (countBodyJSb4 + countBodyCSSb4) * 100);
        var pctBodyCSSb4 = parseInt(countBodyCSSb4 / (countBodyJSb4 + countBodyCSSb4) * 100);
        datasetB += '["JavaScript",';
        datasetB += countBodyJSb4.toString() + '],';
        datasetB += '["StyleSheet",';
        datasetB += countBodyCSSb4.toString() + ']';
        datasetB += "]";
        //console.log("body dataset: " + datasetB);
        var dsB = jQuery.parseJSON(datasetB);
        plotChartPieOrderingBodyb4(dsB);
    }
    if ((countHeadJS + countHeadJSM) != (countHeadJSb4 + countHeadJSMb4) || countHeadCSS != countHeadCSSb4 || countBodyJS != countBodyJSb4 || countBodyCSS != countBodyCSSb4) {
        // after version
        // reset dataset for pie chart headers
        var datasetH = "[";
        var datasetB = "[";
        var len = FileOrderList.length;
        var pctHeadJS = (countHeadJS + countHeadJSM) / (countHeadJS + countHeadJSM + countHeadCSS) * 100;
        var pctHeadCSS = countHeadCSS / (countHeadJS + countHeadCSS) * 100;
        datasetH += '["JavaScript",';
        datasetH += countHeadJS.toString() + '],';
        datasetH += '["StyleSheet",';
        datasetH += countHeadCSS.toString() + '],';
        datasetH += '["JavaScript (Modernizr)",';
        datasetH += countHeadJSMb4.toString() + ']';
        datasetH += "]";
        //console.log("header dataset: " + datasetH);
        var dsH = jQuery.parseJSON(datasetH);
        plotChartPieOrderingHdrs(dsH);
        if (countBodyJS + countBodyCSS > 0) {
            var pctBodyJS = parseInt(countBodyJS / (countBodyJS + countBodyCSS) * 100);
            var pctBodyCSS = parseInt(countBodyCSS / (countBodyJS + countBodyCSS) * 100);
            datasetB += '["JavaScript",';
            datasetB += countBodyJS.toString() + '],';
            datasetB += '["StyleSheet",';
            datasetB += countBodyCSS.toString() + ']';
            datasetB += "]";
            //console.log("body dataset: " + datasetB);
            var dsB = jQuery.parseJSON(datasetB);
            plotChartPieOrderingBody(dsB);
        }
        //update the heading text because there's been modification
        $("#cssjstext").html("<h3>CSS & JS Ordering Analysis (before any JS modification)</h3>");
    }
    else {
        // remove div "HCtextfilesAFTER"
        document.getElementById("HCtextfilesAFTER").remove();
    }
}
function simpleObjInspect(oObj, key, tabLvl) {
    key = key || "";
    tabLvl = tabLvl || 1;
    var tabs = "";
    for (var i = 1; i < tabLvl; i++) {
        tabs += "\t";
    }
    var keyTypeStr = " (" + typeof key + ")";
    if (tabLvl == 1) {
        keyTypeStr = "(self)";
    }
    var s = tabs + key + keyTypeStr + " : ";
    if (typeof oObj == "object" && oObj !== null) {
        s += typeof oObj + "\n";
        for (var k in oObj) {
            if (oObj.hasOwnProperty(k)) {
                s += simpleObjInspect(oObj[k], k, tabLvl + 1);
            }
        }
    } else {
        s += "" + oObj + " (" + typeof oObj + ") \n";
    }
    return s;
}

function displayTableFileStats(bHighlight) {
    /*	var tbl_body = "";
     //var tob, tzb, tsb, tsp = 0;
     tbl_row = "<th>"+"File Stat"+"</th>"+"<th>"+"Count"+"</th>";
     tbl_body += "<tr class=\"header\">"+tbl_row+"</tr>";
     $.each(FileStats, function() {
     var tbl_row = "";
     $.each(this, function(k , v) {
     tbl_row += "<td>"+k+"</td>";
     tbl_row += "<td>"+v+"</td>";
     })
     tbl_body += "<tr>"+tbl_row+"</tr>";
     //console.log("Stats table row: " + tbl_row);
     })
     //$("#filestats_table tbody").html(tbl_body);
     */
    // list stats
    var list_body = '<ul id=\"statslist\">';
    $.each(FileListStats, function () {
        var spanstat = '';
        var spantype = '';
        var spantxt = '';
        var listyle = 'info'; // default blue info colour
        $.each(this, function (k, v) {
            switch (k) {
                case 'value':
                    var s = String(v);
                    var l = s.length;
                    if (l <= 9)
                        spanstat = '<span class=\"flstat\">' + v + '</span>';
                    else
                        spanstat = '<span class=\"flstatmed\">' + v + '</span>';
                    break;
                case 'type':
                    spantype = '<span class=\"fltype\">' + v + '</span>';
                    break;
                case 'text':
                    spantxt = '<span class=\"fltxt\">' + v + '</span>';
                    break;
                case 'state':
                    listyle = v;
                    break;
            }
        })
        if (bHighlight == true)
            listyle = "info";
        list_body += '<li class="statslist' + listyle + '">' + spanstat + spantype + spantxt + '</li>';
        //console.log("Stats table row: " + tbl_row);
    })
    list_body += '</ul>';
    $("#filestats_list").html(list_body);
    //console.log(list_body);

}
function displayTableImages() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Image File" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(Imgfileset, function (k, v) {
        tbl_row = "<td>" + v + "</td>";
        tbl_body += "<tr>" + tbl_row + "</tr>";
    })
    //console.log("Image table row: " + tbl_row);
    $("#images_table tbody").html(tbl_body);
}
function getShortName(longname) {
    // check for base 64
    dipos = longname.indexOf('data:');
    //console.log ('dipos =' + dipos);
    if (dipos >= 0) {
        shortname = longname.substr(0, 30) + "..";
    }
    else {
        // remove querystring if it exists
        qpos = longname.indexOf('?');
        if (qpos >= 0)
            longname = longname.substr(0, qpos);
        //longname = unescape(longname);
        splits = longname.split("/");
        lsplits = splits.length;
        //console.log ("splitcount: " + lsplits + " longname  " + longname);
        if (lsplits == 1) {
            //console.log ("file " + longname);
            // local file
            splits = longname.split("\\");
            lsplits = splits.length - 1;
            shortname = splits[0] + "\\" + splits[1] + "\\" + splits[2] + ".." + "\\" + splits[lsplits];
        }
        else {
            // url
            //console.log ("url " + longname);
            lsplits = splits.length - 1;
            shortname = splits[0] + "//" + splits[2] + ".." + "/" + splits[lsplits];
            //console.log ("url " + longname + " ==> " + shortname);
            // if still too long, truncate
            if (shortname.length > 64) {
                //console.log (longname + " =64=> " + shortname);
                shortname = shortname.substr(0, 31) + ".." + shortname.substr(-32, 32);
            }
        }
    }
    //console.log(longname + " -> " + shortname);
    if (shortname != "")
        shortname = unescape(shortname);

    return shortname;
}

function displayTableNewObj(tbltype) {
    var tbl_body = "";
    var tbl_head = "";
    var colhdrs = new Array();
    var container = '';
    var sortcol = 1;
    var ascdesc = 'asc';
    var lastcol = 0;
    switch (tbltype) {
        case 'all':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Parent File', 'Mime Type', 'Domain', 'Domain Type', 'HTTP Status', 'Ext.', 'Header Bytes', 'Content Transmitted Bytes', 'Compression', 'Content GZIP Compressed Bytes', 'Content Uncompressed Bytes', 'Minified Uncompressed Bytes', 'Minified GZIP Compressed Bytes', 'Combined Files', 'JS Defer', 'JS Async', 'doc.write Count', 'DateTime'];
            container = "#newobj_table";
            break;
        case 'subset':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Parent File', 'Mime Type', 'Domain', 'Domain Type', 'HTTP Status', 'Ext.', 'Header Bytes', 'Content Transmitted Bytes', 'Compression', 'Content GZIP Compressed Bytes', 'Content Uncompressed Bytes', 'Minified Uncompressed Bytes', 'Minified GZIP Compressed Bytes', 'Combined Files'];
            container = "#newobj_table";
            break;
        case 'hdrs':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Mime Type', 'Domain', 'Domain Type', 'HTTP Status', 'Header Bytes', 'Content Transmitted Bytes', 'Compression', 'Server', 'HTTP Protocol', 'Age', 'Date', 'Last Modified Date', 'Cache Control Private', 'Cache Control Public', 'Cache Control Max Age', 'Cache Control S-Max Age', 'Cache Control No Cache', 'Cache Control No Store', 'Cache Control No Transform', 'Cache Control Must Revalidate', 'Cache Control Proxy Revalidate', 'Connection', 'Expires', 'Etag', 'Keep Alive', 'Pragma', 'Set-Cookie', 'Upgrade', 'Vary', 'Via', 'X-Served-By', 'X-Cache', 'X-Px', 'X-Edge-Location', 'CF-Ray', 'X-CDN-Geo', 'X-CDN', 'DateTime'];
            container = "#headers_table";
            break;
        /*
         "hdrs_Server" => $server,
         "hdrs_Protocol" => $protocol,
         "hdrs_responsecode" => $responsecode,
         "hdrs_date" => $date,
         "hdrs_lastmodifieddate" => $lastmodifieddate,
         "hdrs_cachecontrol" => $cachecontrol,
         "hdrs_cachecontrolPrivate" => $cachecontrolPrivate,
         "hdrs_cachecontrolPublic" => $cachecontrolPublic,
         "hdrs_cachecontrolNoCache" => $cachecontrolNoCache,
         "hdrs_cachecontrolNoStore" => $cachecontrolNoStore,
         "hdrs_cachecontrolNoTransform" => $cachecontrolNoTransform,
         "hdrs_cachecontrolMustRevalidate" => $cachecontrolMustRevalidate,
         "hdrs_cachecontrolProxyRevalidate" => $cachecontrolProcyValidate,
         "hdrs_connection" => $connection,
         "hdrs_contentencoding" => $contentencoding,
         "hdrs_contentlength" => $contentlength,
         "hdrs_expires" => $expires,
         "hdrs_etag" => $etag,
         "hdrs_keepalive" => $keepalive,
         "hdrs_pragma" => $pragma,
         "hdrs_setcookie" => $setcookie,
         "hdrs_upgrade" => $upgrade,
         "hdrs_vary" => $vary,
         "hdrs_via" => $via, */

        case 'css':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Parent File', 'Mime Type', 'Domain', 'Domain Type', 'Reference', 'Header Size', 'Content Transmitted Bytes', 'Compression', 'Content GZIP Compressed Bytes', 'Content Uncompressed Bytes', 'Minified Uncompressed Bytes', 'Minified GZIP Compressed Bytes', 'Combined Files', 'Section', 'Timing'];
            container = "#css_table";
            sortcol = 11;
            ascdesc = 'desc';
            break;
        case 'js':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Parent File', 'Mime Type', 'Domain', 'Domain Type', 'Header Size', 'Content Transmitted Bytes', 'Compression', 'Content GZIP Compressed Bytes', 'Content Uncompressed Bytes', 'Minified Uncompressed Bytes', 'Minified GZIP Compressed Bytes', 'Combined Files', 'JS Defer', 'JS Async', 'doc.write Count', 'Section', 'Timing'];
            container = "#js_table";
            sortcol = 10;
            ascdesc = 'desc';
            break;
        case 'fonts':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Parent File', 'Mime Type', 'Domain', 'Domain Type', 'Header Size', 'Content Transmitted Bytes', 'Compression', 'Content GZIP Compressed Bytes', 'Content Uncompressed Bytes', 'Font name'];
            container = "#fonts_table";
            sortcol = 10;
            ascdesc = 'desc';
            break;
        case 'img':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Mime Type', 'Domain', 'Domain Type', 'Ext.', 'Content Size', 'Image Type', 'Encoding', 'Responsive Image', 'Display Size', 'Actual Size', 'Metadata Bytes', 'EXIF Bytes', 'APP12 Bytes', 'IPTC Bytes', 'XMP Bytes', 'Comment Bytes', 'ICC Colour Profile Bytes', 'Colour Type', 'Colour Depth', 'Interlace', 'JPEG Est. Quality', 'Saved Quality', 'Chroma Subsampling', 'Animation'];
            container = "#images_table";
            sortcol = 10;
            ascdesc = 'desc';
            break;
        case 'tpperformance':
            colhdrs = ['ID', 'Filetype', 'URL', 'URL', 'Local File', 'Domain', 'OK or in Error?', 'File size < 50KB', 'Is Compressed?', 'Is Minified?', 'JS Defer', 'JS Async', 'JS DocWrite', 'Image Matadata', 'is Cached?', "Doc section", "Doc Timing"];
            container = "#TPperformance_table";
            break;
    }
    lastcol = colhdrs.length + 1;
    //console.log("building obj table: " + container);
    //console.log("colhdrs= " + colhdrs);
    // build column header row
    tbl_row = '';
    for (var i = 0; i < colhdrs.length; i++) {
        tbl_row = tbl_row + "<th>" + colhdrs[i] + "</th>";
    }
    tbl_head = "<tr>" + tbl_row + "</tr>";
    var objtype = '';
    var id = '';
    var httpstatus = '';
    var objsource = '';
    var objlocal = '';
    var bytesize = 0;
    var respsonsedatetime = '';
    var domref = '';
    var domain = ''
    var compression = '';
    var minifiedSize = 0;
    var compressedSize = 0;
    var minifuedPCT = 0;
    var minifiedThreshold = 5; // 5% difference allowed
    var isMinified = '';
    var isCompressed = '';
    var filesection = '';
    var filetiming = '';
    var filename = '';
    // build the column headings
    $.each(NewObj, function () {
        var tbl_row = "";

        $.each(this, function (k, v) {
            //console.log(k + ": " + v);

            if (k == 'id') {
                // get response date time for this object now as it is last on the array
                respsonsedatetime = NewObj[Number(v)]['response_datetime'];
                id = NewObj[Number(v)]['id'];
                objsource = NewObj[Number(v)]['Object source'];
                objlocal = NewObj[Number(v)]['Object file'];
                objtype = NewObj[Number(v)]['Object type'];
                domref = NewObj[Number(v)]['Domain ref'];
                domain = NewObj[Number(v)]['Domain'];
                mimetype = NewObj[Number(v)]['Mime type'];
                httpstatus = NewObj[Number(v)]['HTTP status'];
                bytesize = NewObj[Number(v)]['Content length transmitted'];
                compression = NewObj[Number(v)]['Compression'];
                if (compression == "gzip" || compression == "br" || compression == "deflate")
                    minifiedSize = NewObj[Number(v)]['Content size minified compressed'];
                else
                    minifiedSize = NewObj[Number(v)]['Content size minified uncompressed'];
                filesection = NewObj[Number(v)]['file_section'];
                filetiming = NewObj[Number(v)]['file_timing'];
                var escapedFilepath = objlocal.replace(/\\/g, "/");
                filename = escapedFilepath.substring(escapedFilepath.lastIndexOf("/") + 1, escapedFilepath.lastIndexOf("."));
                //console.log("dt found: " + objsource + ': ' + respsonsedatetime);
            }
            //console.log('id: "' + id + '"');

            //console.log(k + " = " + v);
            if (k == 'File extension' && v == 'ext')
                v = '';
            if (v === undefined || v === null)
                v = '';
            tooltip = '';
            switch (tbltype) {
                case 'all':
                    switch (k) {
                        case 'id':
                        case 'Object type':
                        case 'Domain':
                        case 'Domain ref':
                        case 'HTTP status':
                        case 'Mime type':
                        case 'File extension':
                        case 'Header size':
                        case 'Content length transmitted':
                        case 'Compression':
                        case 'Content size compressed':
                        case 'Content size uncompressed':
                        case 'Content size minified uncompressed':
                        case 'Content size minified compressed':
                        case 'Combined files':
                        case 'JS defer':
                        case 'JS async':
                        case 'JS docwrite':
                        case 'response_datetime':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Object source':
                        case 'Object file':
                        case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log(k + ": longname:"+v);
                                //console.log(k + ": shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + shortname + '</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + v + '</td>';
                            break;
                        default:
                    }  // end switch all
                    break;
                case 'subset':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td>" + v + "</td>";
                            break;
                        case 'Object type':
                        case 'Domain':
                        case 'Domain ref':
                        case 'HTTP status':
                        case 'File extension':
                        case 'Mime type':
                        case 'Header size':
                        case 'Content length transmitted':
                        case 'Compression':
                        case 'Content size compressed':
                        case 'Content size uncompressed':
                        case 'Content size minified uncompressed':
                        case 'Content size minified compressed':
                        case 'Combined files':
                            if (objtype == 'StyleSheet')
                                tbl_row += "<td class=\"css\" title=\"" + k + "\">" + v + "</td>";
                            else
                                if (objtype == 'JavaScript')
                                    tbl_row += "<td class=\"js\" title=\"" + k + "\">" + v + "</td>";
                                else
                                    if (objtype == 'Image')
                                        tbl_row += "<td class=\"img\" title=\"" + k + "\">" + v + "</td>";
                                    else
                                        tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Mime type':
                            var smime = v.replace('/', '_');
                            smime = smime.replace('-', '_');
                            var imgicon = "<img src=\"/toaster/images/mimetypes128x128/" + smime + ".png\" width=32 height=32   ></img>";
                            //console.log("mimetype image = " + imgicon);
                            if (objtype == 'StyleSheet')
                                tbl_row += "<td class=\"css\" title=\"" + k + "\">" + imgicon + v + "</td>";
                            else
                                if (objtype == 'JavaScript')
                                    tbl_row += "<td class=\"js\" title=\"" + k + "\">" + imgicon + v + "</td>";
                                else
                                    if (objtype == 'Image')
                                        tbl_row += "<td class=\"img\" title=\"" + k + "\">" + imgicon + v + "</td>";
                                    else
                                        tbl_row += "<td title=\"" + k + "\">" + imgicon + v + "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("os url: "+v);
                                //console.log("os shortname: "+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else {
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            }
                            break;
                        case 'Object file':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log(k + ": longname:"+v);
                                //console.log(k + ": shortname:"+shortname);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + v + '</td>'; // full column5 to be hidden
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\">' + v + '</td>'; // full column5 to be hidden
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            break;
                        default:
                    }  // end switch all
                    break;
                case 'hdrs':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Object type':
                        case 'Domain':
                        case 'Domain ref':
                        case 'HTTP status':
                        case 'Mime type':
                        //case 'File extension':
                        case 'Header size':
                        case 'Content length transmitted':
                        case 'Compression':
                        case 'hdrs_Protocol':
                        case 'hdrs_Server':
                        case 'hdrs_cachecontrolPrivate':
                        case 'hdrs_cachecontrolPublic':
                        case 'hdrs_cachecontrolNoCache':
                        case 'hdrs_cachecontrolNoStore':
                        case 'hdrs_cachecontrolNoTransform':
                        case 'hdrs_cachecontrolMustRevalidate':
                        case 'hdrs_cachecontrolProxyRevalidate':
                        case 'hdrs_connection':
                        case 'hdrs_etag':
                        case 'hdrs_keepalive':
                        case 'hdrs_pragma':
                        case 'hdrs_setcookie':
                        case 'hdrs_upgrade':
                        case 'hdrs_vary':
                        case 'hdrs_via':
                        case 'hdrs_xservedby':
                        case 'hdrs_xcache':
                        case 'hdrs_xpx':
                        case 'hdrs_xedgelocation':
                        case 'hdrs_cfray':
                        case 'hdrs_xcdngeo':
                        case 'hdrs_xcdn':
                        case 'response_datetime':
                            tooltip = k;
                            if (objtype == 'StyleSheet')
                                tbl_row += "<td class=\"css\" title=\"" + k + "\">" + v + "</td>";
                            else
                                if (objtype == 'JavaScript')
                                    tbl_row += "<td class=\"js\" title=\"" + k + "\">" + v + "</td>";
                                else
                                    if (objtype == 'Image')
                                        tbl_row += "<td class=\"img\" title=\"" + k + "\">" + v + "</td>";
                                    else
                                        tbl_row += "<td title=\"" + tooltip + "\">" + v + "</td>";
                            break;
                        case 'hdrs_age':
                        case "hdrs_cachecontrolMaxAge":
                        case "hdrs_cachecontrolSMaxAge":
                            if (v > 0) {
                                var tooltip = convertSeconds(v);
                                //console.log('conv seconds = ' + tooltip);
                                if (objtype == 'StyleSheet')
                                    tbl_row += "<td class=\"css\" title=\"" + tooltip + "\">" + v + "</td>";
                                else
                                    if (objtype == 'JavaScript')
                                        tbl_row += "<td class=\"js\" title=\"" + tooltip + "\">" + v + "</td>";
                                    else
                                        if (objtype == 'Image')
                                            tbl_row += "<td class=\"img\" title=\"" + tooltip + "\">" + v + "</td>";
                                        else
                                            tbl_row += "<td title=\"" + tooltip + "\">" + v + "</td>";
                            }
                            else {
                                tbl_row += "<td title=\"" + k + "\">" + "</td>";
                            }
                            break;
                        // fields with tooltip = years, months, weeks, days ago
                        case 'hdrs_date':
                        case 'hdrs_expires':
                        case 'hdrs_lastmodifieddate':
                            today = new Date(respsonsedatetime);
                            past = new Date(v);
                            tooltip = getNiceTime(past, today, 5, 'ago')
                            if (tooltip == 'now') {
                                //console.log(objtype + '(' + respsonsedatetime + ') ' + objsource + ': ' + today + " - " + past + ': ' + tooltip);
                                tooltip = '';
                            }
                            if (objtype == 'StyleSheet')
                                tbl_row += "<td class=\"css\" title=\"" + tooltip + "\">" + v + "</td>";
                            else
                                if (objtype == 'JavaScript')
                                    tbl_row += "<td class=\"js\" title=\"" + tooltip + "\">" + v + "</td>";
                                else
                                    if (objtype == 'Image')
                                        tbl_row += "<td class=\"img\" title=\"" + tooltip + "\">" + v + "</td>";
                                    else
                                        tbl_row += "<td title=\"" + tooltip + "\">" + v + "</td>";
                            break;

                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else {
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            }
                            break;
                        case 'Object file':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log(k + ": longname:"+v);
                                //console.log(k + ": shortname:"+shortname);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        default:
                    }  // end switch all
                    break;
                case 'css':
                    switch (k) {
                        case 'id':
                        case 'Object type':
                        case 'Mime type':
                        case 'Domain':
                        case 'Domain ref':
                        //case 'File extension':
                        case 'CSS ref':
                        case 'Header size':
                        case 'Content length transmitted':
                        case 'Compression':
                        case 'Content size compressed':
                        case 'Content size uncompressed':
                        case 'Content size minified uncompressed':
                        case 'Content size minified compressed':
                        case 'Combined files':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'file_section':
                        case 'file_timing':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            //console.log("css table row: " + tbl_row);
                            //console.log("details: " +  filesection + " " + filetiming);
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else {
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log(k + ": longname:"+v);
                                //console.log(k + ": shortname:"+shortname);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else
                                tbl_row += '<td>' + v + '</td>'; // full column5 to be hidden
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            break;
                        default:
                    }  // end switch all
                    break;
                case 'js':
                    switch (k) {
                        case 'id':
                        case 'Object type':
                        case 'Mime type':
                        case 'Domain':
                        case 'Domain ref':
                        //case 'File extension':
                        case 'Header size':
                        case 'Content length transmitted':
                        case 'Compression':
                        case 'Content size compressed':
                        case 'Content size uncompressed':
                        case 'Content size minified uncompressed':
                        case 'Content size minified compressed':
                        case 'Combined files':
                        case 'JS defer':
                        case 'JS async':
                        case 'JS docwrite':
                        case 'file_section':
                        case 'file_timing':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else {
                                tbl_row += '<td title=\"' + k + '\">' + v + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log(k + ": longname:"+v);
                                //console.log(k + ": shortname:"+shortname);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            break;
                        default:
                    }  // end switch all
                    break;

                case 'fonts':
                    switch (k) {
                        case 'id':
                        case 'Object type':
                        case 'Mime type':
                        case 'Domain':
                        case 'Domain ref':
                        //case 'File extension':
                        case 'Header size':
                        case 'Content length transmitted':
                        case 'Compression':
                        case 'Content size compressed':
                        case 'Content size uncompressed':
                        case 'Font name':
                        if(v == '' && filename.indexOf("fontawesome") != -1)
                        {  
                            v = "Font Awesome";
                         }
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else {
                                tbl_row += '<td title=\"' + k + '\">' + v + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            break;
                        default:
                    }  // end switch all
                    break;

                case 'img':
                    switch (k) {
                        case 'id':
                        case 'Object type':
                        case 'Mime type':
                        case 'Domain':
                        case 'Domain ref':
                        case 'File extension':
                        case 'Content length transmitted':
                        case 'Metadata bytes':
                        case 'EXIF bytes':
                        case 'APP12 bytes':
                        case 'IPTC bytes':
                        case 'XMP bytes':
                        case 'Comment bytes':
                        case 'ICC colour profile bytes':
                        case 'Image type':
                        case 'Image responsive':
                        case 'Image encoding':
                        case 'Image display size':
                        case 'Image actual size':
                        case 'Colour type':
                        case 'Colour depth':
                        case 'Interlace':
                        case 'Est. quality':
                        case 'Photoshop quality':
                        case 'Chroma subsampling':
                        case 'Animation':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else {
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        default:
                    }  // end switch img
                    break;
                case 'tpperformance':
                    switch (k) {
                        case 'id':
                        case 'Object type':
                        case 'Domain':
                        case 'JS defer':
                        case 'JS async':
                        case 'JS docwrite':
                        case 'file_section':
                        case 'file_timing':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Metadata bytes':
                            if (objtype == "Image") {
                                if (Number(v) == 0)
                                    tbl_row += "<td title=\"" + k + "\">" + "None" + "</td>";
                                else
                                    tbl_row += "<td title=\"" + k + "\">" + v + " bytes</td>";
                            }
                            else
                                tbl_row += "<td title=\"" + k + "\">" + "-" + "</td>";
                            break;
                        case 'HTTP status':
                            if (Number(v) < 300)
                                tbl_row += "<td title=\"" + k + "\">" + "OK" + "</td>";
                            else
                                tbl_row += "<td title=\"" + k + "\">" + "Error" + "</td>";
                            break;
                        case "Content size downloaded":
                            if (Number(v) < 50000)
                                tbl_row += "<td title=\"" + k + "\">" + "Yes" + "</td>";
                            else
                                tbl_row += "<td title=\"" + k + "\">" + "No" + "</td>";
                            break;
                        case 'Compression':
                            if (objtype == "HTML" || objtype == "JavaScript" || objtype == "StyleSheet" || objtype == "Data" || objtype == "Font") {
                                if (compression == 'gzip' || compression == 'br' || compression == 'deflate') {
                                    isCompressed = "Yes";
                                }
                                else {
                                    isCompressed = "No";
                                }
                                minifiedPCT = minifiedSize / bytesize * 100;
                                if (minifiedPCT <= 100 - minifiedThreshold)
                                    isMinified = "No";
                                else
                                    isMinified = "Yes";
                            }
                            else {
                                // not a text file<br>
                                isMinified = "-";
                                isCompressed = '-';
                            }
                            tbl_row += "<td title=\"" + k + "\">" + isCompressed + "</td>";
                            // also deal with minification
                            tbl_row += "<td title=\"" + "is Minified" + "\">" + isMinified + "</td>";
                            break;
                        case 'hdrs_expires':
                            today = new Date(respsonsedatetime);
                            past = new Date(v);
                            tooltip = getNiceTime(past, today, 5, 'ago')
                            if (tooltip == 'now') {
                                //console.log(objtype + '(' + respsonsedatetime + ') ' + objsource + ': ' + today + " - " + past + ': ' + tooltip);
                                tooltip = '';
                            }
                            tbl_row += "<td title=\"" + tooltip + "\">" + v + "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(shortname) + '</td>';
                            }
                            else {
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"' + k + '\"><a href="' + v + '" target="_blank">' + unescape(v) + '</td>';
                            }
                            break;
                        case 'Object file':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else
                                tbl_row += '<td title=\"' + k + '\">' + unescape(v) + '</td>'; // full column5 to be hidden
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        default:
                    }  // end switch tpperformance
                    break;
                default:
                    tbl_row += "<td>" + v + "</td>";

            } // end switch
        }) // end foreach property of object

        //console.log("building rows for" + tbltype);
        // build rows for the table only if it is for the right type
        switch (tbltype) {

            case 'subset1':
                tbl_body += "<tr>" + tbl_row + "</tr>";
                //console.log("New Object table row: " + tbl_row);
                break;
            case 'all':
            case 'subset':
                if (tbl_row.indexOf("N/A") == -1)
                    tbl_body += "<tr id =\"" + id + "\">" + tbl_row + "</tr>";
                break;
            case 'hdrs':
                if (domref != 'Embedded') // select type here
                {
                    tbl_body += "<tr id =\"" + id + "\">" + tbl_row + "</tr>";
                }
                break;

            case 'css':
                //console.log("Poss CSS table row: " + id + "; status = " + httpstatus.substr(0,3));
                if (objtype == 'StyleSheet' && httpstatus.substr(0, 3) == '200') {
                    tbl_body += "<tr id =\"" + id + "\">" + tbl_row + "</tr>";
                    //console.log("New css table body: " + tbl_body);
                }
                break;

            case 'js':
                //console.log("Poss JS table row: " + id + "; status = " + httpstatus.substr(0,3));
                if (objtype == 'JavaScript' && httpstatus.substr(0, 3) == '200') {
                    tbl_body += "<tr id =\"" + id + "\" class=\"js\">" + tbl_row + "</tr>";
                    //console.log("New js table body: " + tbl_body);
                }
                break;
            case 'fonts':
                //console.log("Poss JS table row: " + id + "; status = " + httpstatus.substr(0,3));
                if (objtype == 'Font' && (httpstatus.substr(0, 3) == '200' || httpstatus == 'Embedded')) {
                    tbl_body += "<tr id =\"" + id + "\" class=\"js\">" + tbl_row + "</tr>";
                    //console.log("New font table body: " + tbl_body);
                }
                break;
            case 'img':
                //console.log("image: " + objtype);
                //console.log("status: " + httpstatus);
                //console.log("Poss Image table row: " + id + "; status = " + httpstatus.substr(0,3));
                if (objtype == 'Image' && bytesize >= 0 && (httpstatus.substr(0, 3) == '200' || httpstatus.substr(0, 6) == "Base64" || httpstatus.substr(0, 8) == "Embedded")) {
                    tbl_body += "<tr id =\"" + id + "\" class=\"img\">" + tbl_row + "</tr>";
                    //console.log("New Image table row: " + tbl_row);
                    //console.log("New Image table body: " + tbl_body);
                }
                break;
            case 'tpperformance':
                //console.log("Poss 3p performance table row: " + id);
                if (domref == '3P' || domref == 'CDN' || domref == 'self-hosted' || (domref == 'Shard' && objsource.indexOf('metrics.') != -1)) {
                    tbl_body += "<tr id =\"" + id + "\" class=\"js\">" + tbl_row + "</tr>";
                    //console.log("New 3p performance table body: " + tbl_body);
                }
                break;
        } // end switch

    })  // for each object

    // add datatable to the defined HTML container
    //console.log(container + ": object table row: " + tbl_row);
    $(container + " thead").html(tbl_head);
    $(container + " tbody").html(tbl_body);
    //console.log(container + ": object table body " + tbl_body);
    /*
     * Insert a 'details' column to the table
     */
    var nCloneTh = document.createElement('th');
    var nCloneTd = document.createElement('td');
    nCloneTd.innerHTML = '<img class="expcon" src="/toaster/images/details_open.png">';
    nCloneTd.className = "center";
    $(container + ' thead tr').each(function () {
        this.insertBefore(nCloneTh, this.childNodes[0]);
    });
    $(container + ' tbody tr').each(function () {
        this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
    });

    //last col - repeat row expansion icon
    var nzCloneTh = document.createElement('th');
    var nzCloneTd = document.createElement('td');
    nzCloneTd.innerHTML = '<img class="expcon" src="/toaster/images/details_open.png">';
    nzCloneTd.className = "center";
    $(container + ' thead tr').each(function () {
        this.insertBefore(nzCloneTh, this.childNodes[lastcol]);
    });
    $(container + ' tbody tr').each(function () {
        this.insertBefore(nzCloneTd.cloneNode(true), this.childNodes[lastcol]);
    });

    /*
     * Initialise DataTables, with no sorting on the 'details' column
     */
    var oTable = $(container).dataTable({
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 50,
        "aoColumnDefs": [
            { "bSortable": false, "bSearchable": false, "aTargets": [0] },
            { "bSearchable": false, "bVisible": false, "aTargets": [3] },
            { "bVisible": false, "aTargets": [5] }
        ],
        dom: 'Bfrtipl',
        buttons: [
            {
                extend: 'copyHtml5',
                title: container + ' Data export'
            },
            {
                extend: 'excelHtml5',
                title: container + ' Data export'
            },
            {
                extend: 'csvHtml5',
                title: container + ' Data export'
            },
            {
                extend: 'pdfHtml5',
                title: container + ' Data export'
            }
        ],
        "processing": true,
        "aaSorting": [[sortcol, ascdesc]] // sorts by a column and direction as set by the table type
    });

    // row highlighting
    oTable.$('td').hover(function () {
        var iCol = $('td', this.parentNode).index(this) % 20;
        $('td:nth-child(' + (iCol + 1) + ')', oTable.$('tr')).addClass('highlighted');
    }, function () {
        oTable.$('td.highlighted').removeClass('highlighted');
    });


    /* Add event listener for opening and closing details
     * Note that the indicator for showing which row is open is not controlled by DataTables,
     * rather it is done here
     */
    $(container).on('click', 'tbody td img.expcon', function () {

        nTr = $(this).parents('tr')[0];
        //console.log('click ' + container+ ' = ' + nTr);
        if (oTable.fnIsOpen(nTr)) {
            /* This row is already open - close it */
            this.src = "/toaster/images/details_open.png";
            oTable.fnClose(nTr);
        }
        else {
            /* Open this row */
            this.src = "/toaster/images/details_close.png";
            oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
        }
    });
} // end datatables


function displayTableOptObj(tbltype) {
    var tbl_body = "";
    var tbl_head = "";
    var colhdrs = new Array();
    var container = '';
    var sortcol = 1;
    var ascdesc = 'asc';
    switch (tbltype) {
        case 'optJPGimg':
            colhdrs = ['ID', 'URL', 'Local File', 'Original Size', 'Original Image Info'];
            container = "#optJPGimages_table";
            sortcol = 4;
            ascdesc = 'desc';
            break;
        case 'optPNGimg':
            colhdrs = ['ID', 'URL', 'Local File', 'Original Size', 'Original Image Info'];
            container = "#optPNGimages_table";
            sortcol = 4;
            ascdesc = 'desc';
            break;
        case 'optGIFimg':
            colhdrs = ['ID', 'URL', 'Local File', 'Original Size', 'Original Image Info'];
            container = "#optGIFimages_table";
            sortcol = 4;
            ascdesc = 'desc';
            break;
        case 'optGIFanim':
            colhdrs = ['ID', 'URL', 'Local File', 'Original Size', 'Original Animation Info'];
            container = "#optGIFanimations_table";
            sortcol = 4;
            ascdesc = 'desc';
            break;
        case 'optWEBPimg':
            colhdrs = ['ID', 'URL', 'Local File', 'Original Size', 'Original Image Info'];
            container = "#optWEBPimages_table";
            sortcol = 4;
            ascdesc = 'desc';
            break;
        case 'optBMPimg':
            colhdrs = ['ID', 'URL', 'Local File', 'Original Size', 'Original Image Info'];
            container = "#optBMPimages_table";
            sortcol = 4;
            ascdesc = 'desc';
            break;
    }

    var passicon = '<img src="/toaster/images/pass.png">';
    var failicon = '<img src="/toaster/images/fail.png">';
    //console.log("building optimg tables for " + tbltype);
    // build column header row
    tbl_row = '';
    for (var i = 0; i < colhdrs.length; i++) {
        tbl_row = tbl_row + "<th>" + colhdrs[i] + "</th>";
    }
    tbl_head = "<tr>" + tbl_row + "</tr>";
    var objtype = '';
    var id = '';
    var httpstatus = '';
    var objsource = '';
    var bytesize = 0;
    var respsonsedatetime = '';
    var domref = '';
    var mimetype = '';
    var metadatabytes = '';
    var jpegEstquality = '';
    var jpegPSquality = '';
    var jpegChromaquality = '-';
    var len = 0;
    var imgtype = '';
    var encoding = '';
    var actualsize = '';
    var interlace = '';
    var colourdepth = '';
    var colourtype = '';
    // build the column headings
    $.each(NewObj, function () {
        objtype = '';
        id = '';
        httpstatus = '';
        objsource = '';
        bytesize = 0;
        respsonsedatetime = '';
        domref = '';
        mimetype = '';
        metadatabytes = '';
        jpegEstquality = '';
        jpegPSquality = '';
        jpegChromaquality = '-';
        var tbl_row = "";
        $.each(this, function (k, v) {
            if (k == 'id') {
                id = "row" + v;
                // get response date time for this object now as it is last on the array
                respsonsedatetime = NewObj[Number(v)]['response_datetime'];
                metadatabytes = NewObj[Number(v)]['Metadata bytes'];
                jpegPSquality = NewObj[Number(v)]['Photoshop quality'];
                jpegChromaquality = NewObj[Number(v)]['Chroma subsampling'];
                jpegEstquality = NewObj[Number(v)]['Est. quality'];
                len = jpegEstquality.length;
                if (jpegEstquality != 'N/A')
                    jpegEstquality = jpegEstquality.substr(0, len - 1);
                objsource = NewObj[Number(v)]['Object source'];
                objtype = NewObj[Number(v)]['Object type'];
                imgtype = NewObj[Number(v)]['Image type'];
                domref = NewObj[Number(v)]['Domain ref'];
                mimetype = NewObj[Number(v)]['Mime type'];
                httpstatus = NewObj[Number(v)]['HTTP status'];
                bytesize = NewObj[Number(v)]['Content length transmitted'];
                encoding = NewObj[Number(v)]['Image encoding'];
                actualsize = NewObj[Number(v)]['Image actual size'];
                interlace = NewObj[Number(v)]['Interlace'];
                colourdepth = parseInt(NewObj[Number(v)]['Colour depth']);
                colourtype = NewObj[Number(v)]['Colour type'];
                gifanimation = NewObj[Number(v)]['Animation'];
                pixelsize = NewObj[Number(v)]['Image actual size'];
                //console.log('colour depth = ' + colourdepth);
                //console.log("dt found: " + objsource + ': ' + respsonsedatetime);
            }
            if (v === undefined || v === null)
                v = '';

            tooltip = k;
            switch (tbltype) {
                case 'optJPGimg':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td class=\"id\"" + v + " title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Content length transmitted':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Image type':
                            tooltip = v + ' ' + encoding + ' ' + actualsize;
                            tbl_row += "<td title=\"" + tooltip + "\">" + '<img src="/toaster/images/jpg.png" height="16" width="16">';
                            // icons
                            tooltip = "Metadata bytes = " + metadatabytes;
                            if (Number(metadatabytes) >= 500)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            else
                                if (Number(metadatabytes) > 0)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';

                            tooltip = "Est. quality = " + jpegEstquality + '%';
                            if (Number(jpegEstquality) >= 90)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            else
                                if (Number(jpegEstquality) >= 80)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';

                            tooltip = "PhotoShop quality = " + jpegPSquality + '%';
                            if (jpegPSquality != '') {
                                if (Number(jpegPSquality) >= 90)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                                else
                                    if (Number(jpegPSquality) >= 80)
                                        tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                    else
                                        tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                            }
                            tooltip = "Chroma Subsampling = " + jpegChromaquality;
                            //console.log("cs tooltip: " + tooltip);
                            if(jpegChromaquality.length > 3)
                            {
                                if (jpegChromaquality.substr(0, 3) == '1x1')
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                                tooltip = interlace;
                            }
                            //console.log("cs tooltip: " + tooltip);
                            if (interlace == 'Non-Interlaced')
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                            else
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';

                            tbl_row += "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                //   tbl_row += '<td title=\"" + k + "\">'+v+'</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"" + k + "\"><a href="' + v + '" target="_blank">' + shortname + '</td>';
                            }
                            else {
                                var fn = getFileName(v);
                                tbl_row += '<td title=\"" + k + "\"><a href="' + fn + '" target="_blank">' + v + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else {
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                            }
                            //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                            break;
                        default:
                    }  // end switch img
                    break;
                case 'optPNGimg':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td class=\"id\"" + v + " title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Content length transmitted':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Image type':
                            tooltip = v + ' ' + encoding + ' ' + actualsize;
                            tbl_row += "<td title=\"" + tooltip + "\">" + '<img src="/toaster/images/png.png" height="16" width="16">';
                            // icons
                            tooltip = "Metadata bytes = " + metadatabytes;
                            if (Number(metadatabytes) >= 500)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            else
                                if (Number(metadatabytes) > 0)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                            tooltip = interlace;
                            //console.log("cs tooltip: " + tooltip);
                            if (interlace == 'Non-Interlaced')
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                            else
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                            tooltip = colourtype + " " + colourdepth + " bit";
                            if (colourdepth > 8)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                            else
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';

                            tbl_row += "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                //   tbl_row += '<td title=\"" + k + "\">'+v+'</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"" + k + "\"><a href="' + v + '" target="_blank">' + shortname + '</td>';
                            }
                            else {
                                var fn = getFileName(v);
                                tbl_row += '<td title=\"" + k + "\"><a href="' + fn + '" target="_blank">' + v + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else {
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                            }
                        //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                        default:
                    }  // end switch img
                    break;
                case 'optGIFimg':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td class=\"id\"" + v + " title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Content length transmitted':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Image type':
                            tooltip = v + ' ' + encoding + ' ' + actualsize;
                            tbl_row += "<td title=\"" + tooltip + "\">" + '<img src="/toaster/images/gif.png" height="16" width="16">';
                            // icons
                            tooltip = "Metadata bytes = " + metadatabytes;
                            if (Number(metadatabytes) >= 500)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            else
                                if (Number(metadatabytes) > 0)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                            tbl_row += "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                //   tbl_row += '<td title=\"" + k + "\">'+v+'</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"" + k + "\"><a href="' + v + '" target="_blank">' + shortname + '</td>';
                            }
                            else {
                                var fn = getFileName(v);
                                tbl_row += '<td title=\"" + k + "\"><a href="' + fn + '" target="_blank">' + v + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else {
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                            }
                        //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                        default:
                    }  // end switch img
                    break;
                case 'optGIFanim':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td class=\"id\"" + v + " title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Content length transmitted':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Image type':
                            tooltip = v + ' ' + encoding + ' ' + actualsize + ' ' + gifanimation;
                            tbl_row += "<td title=\"" + tooltip + "\">" + '<img src="/toaster/images/gif.png" height="16" width="16">';
                            // icons
                            tooltip = "Metadata bytes = " + metadatabytes;
                            if (Number(metadatabytes) >= 500)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            else
                                if (Number(metadatabytes) > 0)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                            tbl_row += "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                //   tbl_row += '<td title=\"" + k + "\">'+v+'</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"" + k + "\"><a href="' + v + '" target="_blank">' + shortname + '</td>';
                            }
                            else {
                                var fn = getFileName(v);
                                tbl_row += '<td title=\"" + k + "\"><a href="' + fn + '" target="_blank">' + v + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else {
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                            }
                        //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                        default:
                    }  // end switch img
                    break;
                case 'optWEBPimg':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Content length transmitted':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Image type':
                            tooltip = v + ' ' + encoding + ' ' + actualsize;
                            tbl_row += "<td title=\"" + tooltip + "\">" + "WEBP";
                            // icons
                            tooltip = "Metadata bytes = " + metadatabytes;
                            if (Number(metadatabytes) >= 500)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            else
                                if (Number(metadatabytes) > 0)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                            tbl_row += "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                //   tbl_row += '<td title=\"" + k + "\">'+v+'</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"" + k + "\"><a href="' + v + '" target="_blank">' + shortname + '</td>';
                            }
                            else {
                                var fn = getFileName(v);
                                tbl_row += '<td title=\"" + k + "\"><a href="' + fn + '" target="_blank">' + v + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else {
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                            }
                        //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                        default:
                    }  // end switch img
                    break;
                case 'optBMPimg':
                    switch (k) {
                        case 'id':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Content length transmitted':
                            tbl_row += "<td title=\"" + k + "\">" + v + "</td>";
                            break;
                        case 'Image type':
                            tooltip = v + ' ' + encoding + ' ' + actualsize;
                            tbl_row += "<td title=\"" + tooltip + "\">" + '<img src="/toaster/images/bmp.png" height="16" width="16">';
                            // icons
                            // its a BMP
                            tooltip = v + ' ' + encoding;
                            tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            tooltip = "Metadata bytes = " + metadatabytes;
                            if (Number(metadatabytes) >= 500)
                                tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/fail.png\" height="16" width="16">';
                            else
                                if (Number(metadatabytes) > 0)
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/warn.png\" height="16" width="16">';
                                else
                                    tbl_row += '<img title=\"' + tooltip + '\" src="/toaster/images/pass.png\" height="16" width="16">';
                            tbl_row += "</td>";
                            break;
                        case 'Object source':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                //   tbl_row += '<td title=\"" + k + "\">'+v+'</td>'; // full column3 to be hidden
                                tbl_row += '<td title=\"" + k + "\"><a href="' + v + '" target="_blank">' + shortname + '</td>';
                            }
                            else {
                                var fn = getFileName(v);
                                tbl_row += '<td title=\"" + k + "\"><a href="' + fn + '" target="_blank">' + v + '</td>';
                            }
                            break;
                        case 'Object file':
                            //case 'Object parent':
                            if (v.length > 32) {
                                shortname = getShortName(v);
                                //console.log("shortname:"+shortname);
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                                //tbl_row += '<td><a href="'+v+'" target="_blank">'+shortname +'</td>';
                            }
                            else {
                                tbl_row += '<td title=\"" + k + "\">' + v + '</td>';
                            }
                        //tbl_row += '<td><a href="'+v+'" target="_blank">'+v+'</td>';
                        default:
                    }  // end switch img
                    break;
                default:
                    tbl_row += "<td>" + v + "</td>";

            } // end switch
        }) // end foreach property of object

        // build rows for the table only if it is for the right type
        switch (tbltype) {
            case 'optJPGimg':
                if (objtype == 'Image' && actualsize != "1 x 1 px" && (mimetype == 'image/jpeg' || mimetype == 'image/jpg') && bytesize >= 0 && ((httpstatus.substr(0, 3) == '200' || httpstatus.substr(0, 6) == "Base64" || httpstatus.substr(0, 8) == "Embedded"))) {
                    tbl_body += "<tr id =\"" + id + "\" class=\"img\">" + tbl_row + "</tr>";
                    //console.log("New Image table row: " + tbl_row);
                    //console.log("New Image table body: " + tbl_body);
                }
                break;
            case 'optPNGimg':
                if (objtype == 'Image' && actualsize != "1 x 1 px" && mimetype == 'image/png' && bytesize >= 0 && ((httpstatus.substr(0, 3) == '200' || httpstatus.substr(0, 6) == "Base64" || httpstatus.substr(0, 8) == "Embedded"))) {
                    tbl_body += "<tr id =\"" + id + "\" class=\"img\">" + tbl_row + "</tr>";
                    //console.log("New Image table row: " + tbl_row);
                    //console.log("New Image table body: " + tbl_body);
                }
                break;
            case 'optGIFimg':
                if (objtype == 'Image' && actualsize != "1 x 1 px" && mimetype == 'image/gif' && (domref == 'Primary' || domref == "CDN" || domref == "Shard" || domref == "Embedded") && bytesize >= 0 && ((httpstatus.substr(0, 3) == '200' || httpstatus.substr(0, 6) == "Base64" || httpstatus.substr(0, 8) == "Embedded")) && gifanimation == '' && pixelsize.substr(0, 5) != '1 x 1') {
                    tbl_body += "<tr id =\"" + id + "\" class=\"img\">" + tbl_row + "</tr>";
                    //console.log("New Image table row: " + tbl_row);
                    //console.log("New Image table body: " + tbl_body);
                }
                break;
            case 'optGIFanim':
                if (objtype == 'Image' && actualsize != "1 x 1 px" && mimetype == 'image/gif' && (domref == 'Primary' || domref == "CDN" || domref == "Shard" || domref == "Embedded") && bytesize >= 0 && ((httpstatus.substr(0, 3) == '200' || httpstatus.substr(0, 6) == "Base64" || httpstatus.substr(0, 8) == "Embedded")) && gifanimation != '') {
                    tbl_body += "<tr id =\"" + id + "\" class=\"img\">" + tbl_row + "</tr>";
                    //console.log("New Image table row: " + tbl_row);
                    //console.log("New Image table body: " + tbl_body);
                }
                break;
            case 'optWEBPimg':
                //console.log(id + " " + bytesize  +" '" + mimetype + "' '" + httpstatus + "'");
                if (objtype == 'Image' && actualsize != "1 x 1 px" && mimetype == 'image/webp' && bytesize >= 0 && (httpstatus.substr(0, 3) == '200' || httpstatus.substr(0, 6) == "Base64" || httpstatus.substr(0, 8) == "Embedded")) {
                    tbl_body += "<tr id =\"" + id + "\" class=\"img\">" + tbl_row + "</tr>";
                    //console.log("New Image table row: " + tbl_row);
                    //console.log("New Image table body: " + tbl_body);
                }
                break;
            case 'optBMPimg':
                //console.log("image: " + objtype);
                //console.log("image url: " + objsource);
                //console.log("status: " + httpstatus);
                //console.log("Poss Image table row: " + id + "; status = " + httpstatus.substr(0,3));
                if (objtype == 'Image' && actualsize != "1 x 1 px" && mimetype == 'image/x-ms-bmp' && bytesize >= 0 && ((httpstatus.substr(0, 3) == '200' || httpstatus.substr(0, 6) == "Base64" || httpstatus.substr(0, 8) == "Embedded"))) {
                    tbl_body += "<tr id =\"" + id + "\" class=\"img\">" + tbl_row + "</tr>";
                    //console.log("New Image table row: " + tbl_row);
                    //console.log("New Image table body: " + tbl_body);
                }
                break;
        } // end switch

    })  // for each object

    // add datatable to the defined HTML container
    //console.log(container + ": object table row: " + tbl_row);
    $(container + " thead").html(tbl_head);
    $(container + " tbody").html(tbl_body);

    /*
     * Insert a 'details' expansion column to the table
     */
    var nCloneTh = document.createElement('th');
    var nCloneTd = document.createElement('td');
    nCloneTd.innerHTML = '<img class="expcon" src="/toaster/images/details_open.png">';
    nCloneTd.className = "center";
    $(container + ' thead tr').each(function () {
        this.insertBefore(nCloneTh, this.childNodes[0]);
    });
    $(container + ' tbody tr').each(function () {
        this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
    });

    // add thumbnail column
    var ntnCloneTh = document.createElement('th');
    var ntnCloneTd = document.createElement('td');
    ntnCloneTd.innerHTML = '';
    ntnCloneTd.className = "center imtn";
    ntnCloneTh.innerHTML = 'Thumbnails';
    $(container + ' thead tr').each(function () {
        this.insertBefore(ntnCloneTh, this.childNodes[colcounter]);
    });
    $(container + ' tbody tr').each(function () {
        this.insertBefore(ntnCloneTd.cloneNode(true), this.childNodes[colcounter]);
    });

    var colcounter = 7;
    // insert extra columns for different optimisations
    switch (tbltype) {
        case 'optJPGimg':
            // optimise button
            var nbCloneTh = document.createElement('th');
            var nbCloneTd = document.createElement('td');
            nbCloneTd.innerHTML = '<button class="btnoptimise">Optimise</button>';
            nbCloneTd.className = "center";
            nbCloneTh.innerHTML = 'Optimise';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nbCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nbCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // jpeg no metadata
            var nmCloneTh = document.createElement('th');
            var nmCloneTd = document.createElement('td');
            nmCloneTd.innerHTML = '';
            nmCloneTd.className = "center et-nmd";
            nmCloneTh.innerHTML = 'No MetaData';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nmCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nmCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsJPEG65PCT
            var njpgasjpg65CloneTh = document.createElement('th');
            var njpgasjpg65CloneTd = document.createElement('td');
            njpgasjpg65CloneTd.innerHTML = '';
            njpgasjpg65CloneTd.className = "center jpg-q85";
            njpgasjpg65CloneTh.innerHTML = 'JPEG quality 85%';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgasjpg65CloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgasjpg65CloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsJPEG65PCT Progressive
            var njpgasjpg65pCloneTh = document.createElement('th');
            var njpgasjpg65pCloneTd = document.createElement('td');
            njpgasjpg65pCloneTd.innerHTML = '';
            njpgasjpg65pCloneTd.className = "center jpg-q85p";
            njpgasjpg65pCloneTh.innerHTML = 'JPEG quality 85% Progressive';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgasjpg65pCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgasjpg65pCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsJPEG75PCT
            var njpgasjpg75CloneTh = document.createElement('th');
            var njpgasjpg75CloneTd = document.createElement('td');
            njpgasjpg75CloneTd.innerHTML = '';
            njpgasjpg75CloneTd.className = "center jpg-q75";
            njpgasjpg75CloneTh.innerHTML = 'JPEG quality 75%';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgasjpg75CloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgasjpg75CloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsJPEG75PCT Progressive
            var njpgasjpg75pCloneTh = document.createElement('th');
            var njpgasjpg75pCloneTd = document.createElement('td');
            njpgasjpg75pCloneTd.innerHTML = '';
            njpgasjpg75pCloneTd.className = "center jpg-q75p";
            njpgasjpg75pCloneTh.innerHTML = 'JPEG quality 75% Progressive';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgasjpg75pCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgasjpg75pCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsJPEG5PCT
            var njpgasjpg85CloneTh = document.createElement('th');
            var njpgasjpg85CloneTd = document.createElement('td');
            njpgasjpg85CloneTd.innerHTML = '';
            njpgasjpg85CloneTd.className = "center jpg-q65";
            njpgasjpg85CloneTh.innerHTML = 'JPEG quality 65%';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgasjpg85CloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgasjpg85CloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsJPEG85PCT Progressive
            var njpgasjpg85pCloneTh = document.createElement('th');
            var njpgasjpg85pCloneTd = document.createElement('td');
            njpgasjpg85pCloneTd.innerHTML = '';
            njpgasjpg85pCloneTd.className = "center jpg-q65p";
            njpgasjpg85pCloneTh.innerHTML = 'JPEG quality 65% Progressive';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgasjpg85pCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgasjpg85pCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // TinyJPG
            var ntinyjpgCloneTh = document.createElement('th');
            var ntinyjpgCloneTd = document.createElement('td');
            ntinyjpgCloneTd.innerHTML = '';
            ntinyjpgCloneTd.className = "center jpg_tinyjpg";
            ntinyjpgCloneTh.innerHTML = 'TinyJPG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ntinyjpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ntinyjpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsPNG
            var njpgaspngCloneTh = document.createElement('th');
            var njpgaspngCloneTd = document.createElement('td');
            njpgaspngCloneTd.innerHTML = '';
            njpgaspngCloneTd.className = "center png";
            njpgaspngCloneTh.innerHTML = 'PNG (unoptimised)';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgaspngCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgaspngCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsPNG, optimised by pngquant
            var njpgaspngqCloneTh = document.createElement('th');
            var njpgaspngqCloneTd = document.createElement('td');
            njpgaspngqCloneTd.innerHTML = '';
            njpgaspngqCloneTd.className = "center pngq";
            njpgaspngqCloneTh.innerHTML = 'PNG (optimised PNGQuant)';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAsWEBP
            var njpgaswebpCloneTh = document.createElement('th');
            var njpgaswebpCloneTd = document.createElement('td');
            njpgaswebpCloneTd.innerHTML = '';
            njpgaswebpCloneTd.className = "center webp";
            njpgaswebpCloneTh.innerHTML = 'WEBP';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgaswebpCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgaswebpCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++
            // SavedAsBPG
            var njpgasbpgCloneTh = document.createElement('th');
            var njpgasbpgCloneTd = document.createElement('td');
            njpgasbpgCloneTd.innerHTML = '';
            njpgasbpgCloneTd.className = "center bpg";
            njpgasbpgCloneTh.innerHTML = 'BPG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(njpgasbpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(njpgasbpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++
            var lastcol = colcounter;
            break;

        case 'optPNGimg':
            // optimise button
            var nbCloneTh = document.createElement('th');
            var nbCloneTd = document.createElement('td');
            nbCloneTd.innerHTML = '<button class="btnoptimise">Optimise</button>';
            nbCloneTd.className = "center";
            nbCloneTh.innerHTML = 'Optimise';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nbCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nbCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // no metadata
            var nmCloneTh = document.createElement('th');
            var nmCloneTd = document.createElement('td');
            nmCloneTd.innerHTML = '';
            nmCloneTd.className = "center et-nmd";
            nmCloneTh.innerHTML = 'No MetaData';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nmCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nmCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // PNGQuant
            var npngqCloneTh = document.createElement('th');
            var npngqCloneTd = document.createElement('td');
            npngqCloneTd.innerHTML = '';
            npngqCloneTd.className = "center pngq";
            npngqCloneTh.innerHTML = 'PNGQuant';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // PNGCrush
            var npngcCloneTh = document.createElement('th');
            var npngcCloneTd = document.createElement('td');
            npngcCloneTd.innerHTML = '';
            npngcCloneTd.className = "center pngc";
            npngcCloneTh.innerHTML = 'PNGCrush';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngcCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngcCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // PNGCrush brute
            var npngcbCloneTh = document.createElement('th');
            var npngcbCloneTd = document.createElement('td');
            npngcbCloneTd.innerHTML = '';
            npngcbCloneTd.className = "center pngcb";
            npngcbCloneTh.innerHTML = 'PNGCrush Brute Force';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngcbCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngcbCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // OptiPNG
            var npngoCloneTh = document.createElement('th');
            var npngoCloneTd = document.createElement('td');
            npngoCloneTd.innerHTML = '';
            npngoCloneTd.className = "center opng";
            npngoCloneTh.innerHTML = 'OptiPNG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngoCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngoCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // PNGNQ
            var npngqCloneTh = document.createElement('th');
            var npngqCloneTd = document.createElement('td');
            npngqCloneTd.innerHTML = '';
            npngqCloneTd.className = "center pngq";
            npngqCloneTh.innerHTML = 'PNGnq-s9';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // PNGout
            var npngoutCloneTh = document.createElement('th');
            var npngoutCloneTd = document.createElement('td');
            npngoutCloneTd.innerHTML = '';
            npngoutCloneTd.className = "center pngo";
            npngoutCloneTh.innerHTML = 'PNGout';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngoutCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngoutCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAsJPG
            var npngasjpgCloneTh = document.createElement('th');
            var npngasjpgCloneTd = document.createElement('td');
            npngasjpgCloneTd.innerHTML = '';
            npngasjpgCloneTd.className = "center jpg-q75";
            npngasjpgCloneTh.innerHTML = 'JPG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngasjpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngasjpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAsWEBP
            var npngaswebpCloneTh = document.createElement('th');
            var npngaswebpCloneTd = document.createElement('td');
            npngaswebpCloneTd.innerHTML = '';
            npngaswebpCloneTd.className = "center webp";
            npngaswebpCloneTh.innerHTML = 'WEBP';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngaswebpCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngaswebpCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++
            // SavedAsBPG
            var npngasbpgCloneTh = document.createElement('th');
            var npngasbpgCloneTd = document.createElement('td');
            npngasbpgCloneTd.innerHTML = '';
            npngasbpgCloneTd.className = "center bpg";
            npngasbpgCloneTh.innerHTML = 'BPG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(npngasbpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(npngasbpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++

            var lastcol = colcounter;
            break;
        case 'optGIFimg':
            // optimise button
            var nbCloneTh = document.createElement('th');
            var nbCloneTd = document.createElement('td');
            nbCloneTd.innerHTML = '<button class="btnoptimise">Optimise</button>';
            nbCloneTd.className = "center";
            nbCloneTh.innerHTML = 'Optimise';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nbCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nbCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // no metadata
            var nmCloneTh = document.createElement('th');
            var nmCloneTd = document.createElement('td');
            nmCloneTd.innerHTML = '';
            nmCloneTd.className = "center et-nmd";
            nmCloneTh.innerHTML = 'No MetaData';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nmCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nmCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsPNG, optimised by pngquant
            var ngifaspngqCloneTh = document.createElement('th');
            var ngifaspngqCloneTd = document.createElement('td');
            ngifaspngqCloneTd.innerHTML = '';
            ngifaspngqCloneTd.className = "center pngq";
            ngifaspngqCloneTh.innerHTML = 'PNG (optimised PNGQuant)';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAsJPG
            var ngifasjpgCloneTh = document.createElement('th');
            var ngifasjpgCloneTd = document.createElement('td');
            ngifasjpgCloneTd.innerHTML = '';
            ngifasjpgCloneTd.className = "center jpg-q75";
            ngifasjpgCloneTh.innerHTML = 'JPG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifasjpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifasjpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAsWEBP
            var ngifaswebpCloneTh = document.createElement('th');
            var ngifaswebpCloneTd = document.createElement('td');
            ngifaswebpCloneTd.innerHTML = '';
            ngifaswebpCloneTd.className = "center webp";
            ngifaswebpCloneTh.innerHTML = 'WEBP';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaswebpCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaswebpCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++
            // SavedAsBPG
            var ngifasbpgCloneTh = document.createElement('th');
            var ngifasbpgCloneTd = document.createElement('td');
            ngifasbpgCloneTd.innerHTML = '';
            ngifasbpgCloneTd.className = "center bpg";
            ngifasbpgCloneTh.innerHTML = 'BPG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifasbpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifasbpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++
            var lastcol = colcounter;
            break;

        case 'optWEBPimg':
            // optimise button
            var nbCloneTh = document.createElement('th');
            var nbCloneTd = document.createElement('td');
            nbCloneTd.innerHTML = '<button class="btnoptimise">Optimise</button>';
            nbCloneTd.className = "center gif-webp";
            nbCloneTh.innerHTML = 'Optimise';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nbCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nbCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // no metadata
            var nmCloneTh = document.createElement('th');
            var nmCloneTd = document.createElement('td');
            nmCloneTd.innerHTML = '';
            nmCloneTd.className = "center";
            nmCloneTh.innerHTML = 'No MetaData';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nmCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nmCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            var lastcol = colcounter;
            break;

        case 'optBMPimg':
            // optimise button
            var nbCloneTh = document.createElement('th');
            var nbCloneTd = document.createElement('td');
            nbCloneTd.innerHTML = '<button class="btnoptimise">Optimise</button>';
            nbCloneTd.className = "center";
            nbCloneTh.innerHTML = 'Optimise';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nbCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nbCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // no metadata
            var nmCloneTh = document.createElement('th');
            var nmCloneTd = document.createElement('td');
            nmCloneTd.innerHTML = '';
            nmCloneTd.className = "center bmp-et-nmd";
            nmCloneTh.innerHTML = 'No MetaData';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nmCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nmCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            var lastcol = colcounter;
            break;

        case 'optGIFanim':
            // optimise button
            var nbCloneTh = document.createElement('th');
            var nbCloneTd = document.createElement('td');
            nbCloneTd.innerHTML = '<button class="btnoptimise">Optimise</button>';
            nbCloneTd.className = "center";
            nbCloneTh.innerHTML = 'Optimise';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nbCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nbCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // no metadata
            var nmCloneTh = document.createElement('th');
            var nmCloneTd = document.createElement('td');
            nmCloneTd.innerHTML = '';
            nmCloneTd.className = "center et-nmd";
            nmCloneTh.innerHTML = 'No MetaData';
            $(container + ' thead tr').each(function () {
                this.insertBefore(nmCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(nmCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAs Optimsed - Gifscicle 1
            var ngifaspngqCloneTh = document.createElement('th');
            var ngifaspngqCloneTd = document.createElement('td');
            ngifaspngqCloneTd.innerHTML = '';
            ngifaspngqCloneTd.className = "center gifsicleO1";
            ngifaspngqCloneTh.innerHTML = 'Gifsicle O1';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAs Optimsed - Gifscicle 2
            var ngifaspngqCloneTh = document.createElement('th');
            var ngifaspngqCloneTd = document.createElement('td');
            ngifaspngqCloneTd.innerHTML = '';
            ngifaspngqCloneTd.className = "center gifsicleO2";
            ngifaspngqCloneTh.innerHTML = 'Gifsicle O2';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAs Optimsed - Gifscicle 3
            var ngifaspngqCloneTh = document.createElement('th');
            var ngifaspngqCloneTd = document.createElement('td');
            ngifaspngqCloneTd.innerHTML = '';
            ngifaspngqCloneTd.className = "center gifsicleO3";
            ngifaspngqCloneTh.innerHTML = 'Gifsicle O3';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAs APNG
            var ngifaspngqCloneTh = document.createElement('th');
            var ngifaspngqCloneTd = document.createElement('td');
            ngifaspngqCloneTd.innerHTML = '';
            ngifaspngqCloneTd.className = "center apng";
            ngifaspngqCloneTh.innerHTML = 'APNG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAs APNG optimsed
            var ngifasjpgCloneTh = document.createElement('th');
            var ngifasjpgCloneTd = document.createElement('td');
            ngifasjpgCloneTd.innerHTML = '';
            ngifasjpgCloneTd.className = "center apngq";
            ngifasjpgCloneTh.innerHTML = 'APNG Optimised';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifasjpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifasjpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAs WEBP
            var ngifaspngqCloneTh = document.createElement('th');
            var ngifaspngqCloneTd = document.createElement('td');
            ngifaspngqCloneTd.innerHTML = '';
            ngifaspngqCloneTd.className = "center awebp";
            ngifaspngqCloneTh.innerHTML = 'WEBP';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;
            // SavedAs WEBP quality 80
            var ngifaspngqCloneTh = document.createElement('th');
            var ngifaspngqCloneTd = document.createElement('td');
            ngifaspngqCloneTd.innerHTML = '';
            ngifaspngqCloneTd.className = "center awebp80";
            ngifaspngqCloneTh.innerHTML = 'WEBP Optimised 80%';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifaspngqCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifaspngqCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++;

            // SavedAsBPG
            var ngifasbpgCloneTh = document.createElement('th');
            var ngifasbpgCloneTd = document.createElement('td');
            ngifasbpgCloneTd.innerHTML = '';
            ngifasbpgCloneTd.className = "center abpg";
            ngifasbpgCloneTh.innerHTML = 'aBPG';
            $(container + ' thead tr').each(function () {
                this.insertBefore(ngifasbpgCloneTh, this.childNodes[colcounter]);
            });
            $(container + ' tbody tr').each(function () {
                this.insertBefore(ngifasbpgCloneTd.cloneNode(true), this.childNodes[colcounter]);
            });
            colcounter++
            var lastcol = colcounter;
            break;
    }
    //last col - repeat row expansion icon
    var nzCloneTh = document.createElement('th');
    var nzCloneTd = document.createElement('td');
    nzCloneTd.innerHTML = '<img class="expcon" src="/toaster/images/details_open.png">';
    nzCloneTd.className = "center";
    $(container + ' thead tr').each(function () {
        this.insertBefore(nzCloneTh, this.childNodes[lastcol]);
    });
    $(container + ' tbody tr').each(function () {
        this.insertBefore(nzCloneTd.cloneNode(true), this.childNodes[lastcol]);
    });

    /*
     * Initialise DataTables, with no sorting on the 'details' column
     */
    var oTable = $(container).dataTable({
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 50,
        "aoColumnDefs": [
            { "bSortable": false, "bSearchable": false, "aTargets": [0, lastcol] },
            { "bVisible": false, "aTargets": [3] } //local file name
        ],
        "processing": true,
        "aaSorting": [[sortcol, ascdesc]], // sorts by a column and direction as set by the table type
        dom: 'Bfrtipl',
        buttons: [
            {
                extend: 'copyHtml5',
                title: 'Image Data export'
            },
            {
                extend: 'excelHtml5',
                title: 'Image Data export'
            },
            {
                extend: 'csvHtml5',
                title: 'Image Data export'
            },
            {
                extend: 'pdfHtml5',
                title: 'Image Data export'
            }
        ],
    });

    // row highlighting
    oTable.$('td').hover(function () {
        var iCol = $('td', this.parentNode).index(this) % 20;
        $('td:nth-child(' + (iCol + 1) + ')', oTable.$('tr')).addClass('highlighted');
    }, function () {
        oTable.$('td.highlighted').removeClass('highlighted');
    });


    /* Add event listener for opening and closing details
     * Note that the indicator for showing which row is open is not controlled by DataTables,
     * rather it is done here
     */
    $(container).on('click', 'tbody td img.expcon', function () {

        nTr = $(this).parents('tr')[0];
        //console.log('click ' + container+ ' = ' + nTr);
        if (oTable.fnIsOpen(nTr)) {
            /* This row is already open - close it */
            this.src = "/toaster/images/details_open.png";
            oTable.fnClose(nTr);
        }
        else {
            /* Open this row */
            this.src = "/toaster/images/details_close.png";
            oTable.fnOpen(nTr, fnFormatOptImgDetails(oTable, nTr), 'details');
        }
    });
    // init table button for the generated datatable
    initImageOptimisation(container);
    // init thumbnails
    addThumbnails(container);
} // end datatables - image optimisation

function initImageOptimisation(container) {
    $(container + ' .btnoptimise').click(function () {
//      alert( 'optimise called' ); // works
        var $row = $(this).parents('tr');
        $tds = $row.find("td");             // Finds all children <td> elements
        var arr = [];
        i = 0;
//console.log("img opt started ");
        $.each($tds, function () {
            arr[i++] = $(this).text();
            // Prints out the text within the <td>
        });
        var id = arr[1];
        var respsonsedatetime = NewObj[Number(id)]['response_datetime'];
        var fullpath = NewObj[Number(id)]['Object source'];
        var localpath = NewObj[Number(id)]['Object file'];
        var mt = NewObj[Number(id)]['Mime type'];
        var anim = NewObj[Number(id)]['Animation'];
        var animflag = false;
        if (anim != '')
            animflag = true;
        var tja = $('#tinyjpgapikey').val();
        if (tja == '')
            tja = "-1";
        //alert("Image to optimise: "+arr[1] + " " + localpath);
        var fDT = $.formatDateTime('dd/mm/yy g:ii a', new Date());
        var TableData = new Array();
        TableData.push(
            {
                "ObjNo": id
                , "url": fullpath
                , "localfile": localpath
                , "mimetype": mt
                , "savepath": savedir
                , "animflag": animflag
            });

        var d = JSON.stringify(TableData);
//console.log(d);
        //console.log(location.protocol + '//' + location.host);
        $opturl = "/toaster/optimise_images.php?" + toasterid;
        // if(location.host == "www.webpagetoaster.com");
        //     $opturl = "https://www.webpagetoaster.com/toaster/optimise_images.php"
        $.ajax({
            url: $opturl ,
            beforeSend: function () {
                $('#tab_imageoptimisation').addClass('wait');
            },
            type: 'POST',
            data: { 'ids': d },
            dataType: 'json',
            success: function (json) {
                //alert(json.size);
//console.log("Opt Image AJAX request was successful");

                // work through JSON array and update for each
                //console.log($row.find("td.jpg-et-nmd").text());
                //    //$row.find("td.jpg-et-nmd").text(data.size);
                //     $row.find("td.et-nmd").html('<span title="'+ data.tool + ": " + data.operation +' ('+ fDT +')' +'">'+data.size+'</span>');
                $.each(json, function (key, data) {
                    //console.log(key,data)
                    $.each(data, function (index, data) {
                        //console.log(index, data.tool, data.operation, data.object, data.size)
                        tooltip = '<span title="' + data.tool + ": " + data.operation + ' (' + data.settings + ')' + '">' + data.size + '</span>';
                        switch (data.id) {
                            case 'no_metadata':
                                $row.find("td.et-nmd").html(tooltip);
                                break;
                            case 'q65':
                                $row.find("td.jpg-q65").html(tooltip);
                                break;
                            case 'q65P':
                                $row.find("td.jpg-q65p").html(tooltip);
                                break;
                            case 'q75':
                                $row.find("td.jpg-q75").html(tooltip);
                                break;
                            case 'q75P':
                                $row.find("td.jpg-q75p").html(tooltip);
                                break;
                            case 'q85':
                                $row.find("td.jpg-q85").html(tooltip);
                                break;
                            case 'q85P':
                                $row.find("td.jpg-q85p").html(tooltip);
                                break;
                            case 'TINYJPG':
                                $row.find("td.jpg_tinyjpg").html(tooltip);
                                break;
                            case 'jpegTran':
                                $row.find("td.jpg_tran").html(tooltip);
                                break;
                            case 'jpegTranP':
                                $row.find("td.jpg_tranP").html(tooltip);
                                break;
                            case 'WEBP':
                                $row.find("td.webp").html(tooltip);
                                break;
                            case 'BPG':
                                $row.find("td.bpg").html(tooltip);
                                break;
                            case 'PNG':
                                $row.find("td.png").html(tooltip);
                                break;
                            case 'PNGQUANT':
                                $row.find("td.pngq").html(tooltip);
                                break;
                            case 'PNGCRUSH':
                                $row.find("td.pngc").html(tooltip);
                                break;
                            case 'PNGCRUSHbrute':
                                $row.find("td.pngcb").html(tooltip);
                                break;
                            case 'OPTIPNG':
                                $row.find("td.opng").html(tooltip);
                                break;
                            case 'PNGOUT':
                                $row.find("td.pngo").html(tooltip);
                                break;
                            case 'gifsicleO1':
                                $row.find("td.gifsicleO1").html(tooltip);
                                break;
                            case 'gifsicleO2':
                                $row.find("td.gifsicleO2").html(tooltip);
                                break;
                            case 'gifsicleO3':
                                $row.find("td.gifsicleO3").html(tooltip);
                                break;
                            case 'gif2apng':
                                $row.find("td.apng").html(tooltip);
                                break;
                            case 'gif2apngq':
                                $row.find("td.apngq").html(tooltip);
                                break;
                            case 'gif2webp':
                                $row.find("td.awebp").html(tooltip);
                                break;
                            case 'gif2webp80':
                                $row.find("td.awebp80").html(tooltip);
                                break;
                            default:
                                break;
                        } // end switch
                    })
                });

                $('#tab_imageoptimisation').removeClass('wait');
            },
            error: function (response) {
console.log("Opt Image AJAX request was a failure");
//console.log(response.responseText);
                $.each(json, function (key, data) {
//console.log(key)
                $.each(data, function (index, data) {
//console.log('index', data)
                 })
                });
                $('#tab_imageoptimisation').removeClass('wait');
            }
        });
    });
} // end function initImageOptimisation

function addThumbnails(container) {
    // check for thumnbnails and add if found
    var objid = '';
    //console.log('looking for thumbnails');
    $.each(NewObj, function () {
        objtype = '';
        id = '';
        httpstatus = '';
        objsource = '';
        imgdimensions = '';
        $.each(this, function (k, v) {
            if (k == 'id') {
                id = v;
                objsource = NewObj[Number(v)]['Object source'];
                objfile = NewObj[Number(v)]['Object file'];
                objtype = NewObj[Number(v)]['Object type'];
                imgdimensions = NewObj[Number(v)]['Image actual size'];
                if (objtype == 'Image' && objfile != undefined) {
                    var filename = objfile.replace(/^.*[\\\/]/, '')
                    var name_with_ext = objfile.split('\\').pop().split('/').pop();
                    var name_without_ext = name_with_ext.substring(name_with_ext.lastIndexOf("/") + 1, name_with_ext.lastIndexOf("."));
                    var lc = jssavedir.slice(-1);
                    if (lc != "/")
                        jssavedir = jssavedir + '/';
                    //console.log('retrieving thumbnail: '+ objfile + ': ' + name_without_ext);
                    //console.log('linking to thumbnail: '+ jssavedir + '_Thumbnails/' + name_without_ext + '.gif');
                    //var rowindex = $('#row'+id).index();
                    var i = '<img title="' + imgdimensions + '" src="' + jssavedir + '_Thumbnails/' + name_without_ext + '.gif"></img>';
                    //console.log(i);
                    $('#row' + id).find("td.imtn").html(i);

                }
            }
        });
    });
}

/**
 * Function to print date diffs.
 *
 * @param {Date} fromDate: The valid start date
 * @param {Date} toDate: The end date. Can be null (if so the function uses "now").
 * @param {Number} levels: The number of details you want to get out (1="in 2 Months",2="in 2 Months, 20 Days",...)
 * @param {Boolean} prefix: adds "in" or "ago" to the return string
 * @return {String} Difference between the two dates.
 */
function getNiceTime(fromDate, toDate, levels, prefix) {
    var lang = {
        "date.past": "{0} ago",
        "date.future": "in {0}",
        "date.now": "now",
        "date.year": "{0} year",
        "date.years": "{0} years",
        "date.years.prefixed": "{0} years",
        "date.month": "{0} month",
        "date.months": "{0} months",
        "date.months.prefixed": "{0} months",
        "date.day": "{0} day",
        "date.days": "{0} days",
        "date.days.prefixed": "{0} days",
        "date.hour": "{0} hour",
        "date.hours": "{0} hours",
        "date.hours.prefixed": "{0} hours",
        "date.minute": "{0} minute",
        "date.minutes": "{0} minutes",
        "date.minutes.prefixed": "{0} minutes",
        "date.second": "{0} second",
        "date.seconds": "{0} seconds",
        "date.seconds.prefixed": "{0} seconds",
    },
        langFn = function (id, params) {
            var returnValue = lang[id] || "";
            if (params) {
                for (var i = 0; i < params.length; i++) {
                    returnValue = returnValue.replace("{" + i + "}", params[i]);
                }
            }
            return returnValue;
        },
        toDate = toDate ? toDate : new Date(),
        diff = fromDate - toDate,
        past = diff < 0 ? true : false,
        diff = diff < 0 ? diff * -1 : diff,
        date = new Date(new Date(1970, 0, 1, 0).getTime() + diff),
        returnString = '',
        count = 0,
        years = (date.getFullYear() - 1970);
    if (years > 0) {
        var langSingle = "date.year" + (prefix ? "" : ""),
            langMultiple = "date.years" + (prefix ? ".prefixed" : "");
        returnString += (count > 0 ? ', ' : '') + (years > 1 ? langFn(langMultiple, [years]) : langFn(langSingle, [years]));
        count++;
    }
    var months = date.getMonth();
    if (count < levels && months > 0) {
        var langSingle = "date.month" + (prefix ? "" : ""),
            langMultiple = "date.months" + (prefix ? ".prefixed" : "");
        returnString += (count > 0 ? ', ' : '') + (months > 1 ? langFn(langMultiple, [months]) : langFn(langSingle, [months]));
        count++;
    } else {
        if (count > 0)
            count = 99;
    }
    var days = date.getDate() - 1;
    if (count < levels && days > 0) {
        var langSingle = "date.day" + (prefix ? "" : ""),
            langMultiple = "date.days" + (prefix ? ".prefixed" : "");
        returnString += (count > 0 ? ', ' : '') + (days > 1 ? langFn(langMultiple, [days]) : langFn(langSingle, [days]));
        count++;
    } else {
        if (count > 0)
            count = 99;
    }
    var hours = date.getHours();
    if (count < levels && hours > 0) {
        var langSingle = "date.hour" + (prefix ? "" : ""),
            langMultiple = "date.hours" + (prefix ? ".prefixed" : "");
        returnString += (count > 0 ? ', ' : '') + (hours > 1 ? langFn(langMultiple, [hours]) : langFn(langSingle, [hours]));
        count++;
    } else {
        if (count > 0)
            count = 99;
    }
    var minutes = date.getMinutes();
    if (count < levels && minutes > 0) {
        var langSingle = "date.minute" + (prefix ? "" : ""),
            langMultiple = "date.minutes" + (prefix ? ".prefixed" : "");
        returnString += (count > 0 ? ', ' : '') + (minutes > 1 ? langFn(langMultiple, [minutes]) : langFn(langSingle, [minutes]));
        count++;
    } else {
        if (count > 0)
            count = 99;
    }
    var seconds = date.getSeconds();
    if (count < levels && seconds > 0) {
        var langSingle = "date.second" + (prefix ? "" : ""),
            langMultiple = "date.seconds" + (prefix ? ".prefixed" : "");
        returnString += (count > 0 ? ', ' : '') + (seconds > 1 ? langFn(langMultiple, [seconds]) : langFn(langSingle, [seconds]));
        count++;
    } else {
        if (count > 0)
            count = 99;
    }
    if (prefix) {
        if (returnString == "") {
            returnString = langFn("date.now");
        } else if (past)
            returnString = langFn("date.past", [returnString]);
        else
            returnString = langFn("date.future", [returnString]);
    }
    return returnString;
}

function displayTableErrors() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "File Name" + "</th>" + "<th>" + "Error" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(ErrorList, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            tbl_row += "<td>" + k + "</td>";
            tbl_row += "<td>" + v + "</td>";
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("Error table row: " + tbl_row);
    })
    $("#errors_table tbody").html(tbl_body);

    if (ErrorList.length == 0) {
        // remove tab and content
        document.getElementById("tabErrors").remove();
        document.getElementById("tab_errors").remove();
    }
}

function displayTable3PObjects(browserenginever) {
    // first, write out cookie table
    $('#txtTPCookies').html(cookietext);
    var tbl_head = "";
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Group" + "</th><th>" + "Category" + "</th>" + "<th>" + "Domain Name" + "</th>" + "<th>" + "Provider" + "</th>" + "<th>" + "Product" + "</th>" + "<th>" + "Site Description" + "</th>";
    tbl_head += "<tr>" + tbl_row + "</tr>";
    $.each(DomainsList, function () {
        var tbl_row = "";
        //console.log(this);
        var domainName = this['Domain Name'];
        var domainRef = this['Domain Type'];
        var sitedesc = this['Site Description'];
        if (sitedesc === undefined || sitedesc == '')
            sitedesc = 'Other';
        var product = this['Product'];
        if (product === undefined || product == null)
            product = 'Other';
        var group = this['Group'];
        if (group === undefined || group == null)
            group = 'Other';
        var sitecat = this['Category'];
        if (sitecat === undefined || sitecat == '' || sitecat == null)
            sitecat = 'Other';
        var sitecompany = this['Company'];
        if (!sitecompany)
            sitecompany = 'Other';
        else
            sitecompany = sitecompany.replace('&amp;', '&');
        //console.log(domainName + " = " + product + ": group/cat: '" + group + "'/'" + sitecat +"'");

        if (domainRef == '3P' || domainRef == 'CDN' || domainRef == 'self-hosted' || (domainRef == 'Shard' && domainName.indexOf('metrics.') != -1)) {
            tbl_row += "<td>" + group + "</td><td>" + sitecat + "</td><td>" + domainName + "</td><td>" + sitecompany + "</td><td>" + product + "</td><td>" + sitedesc + "</td>";
            tbl_body += "<tr>" + tbl_row + "</tr>";
            //console.log("3p table row: " + tbl_row);
        }
    });
    //populate table
    $("#objects3p_table thead").html(tbl_head);
    $("#objects3p_table tbody").html(tbl_body);
    //datatable initialisation
    var oTable = $("#objects3p_table").dataTable({
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 50,
        "processing": true,
        "order": [[1, "desc"]],
        dom: 'Bfrtipl',
        buttons: [
            {
                extend: 'copyHtml5',
                title: '3p Data export'
            },
            {
                extend: 'excelHtml5',
                title: '3p Data export'
            },
            {
                extend: 'csvHtml5',
                title: '3p Data export'
            },
            {
                extend: 'pdfHtml5',
                title: '3p Data export'
            }
        ],
    });
    $('#ShowNew3pDomains').click(function () {
        //console.log("Show new domains");
        //loop through table and display domains that have no group
        var cells = [];
        var rows = oTable.fnGetNodes();
        for (var i = 0; i < rows.length; i++) {
            // Get HTML of 3rd column (for example)
            var group = $(rows[i]).find("td:eq(0)").html();
            if (group == "")
                cells.push($(rows[i]).find("td:eq(2)").html());
        }
        //console.log(cells);
        alert(cells);
    });
    // Loops through the table returning each row
    //        $.each(oTable.fnGetNodes(), function(index, value) {
    // Fields called as follows
    //        var group = value).val();
    //console.log(index + " " + value);
    //            // and parse the row:
    //            var nTds=$('td', value);
    //
    //            // then list elements of the row by:
    //            var group = $(nTds[2]).text;
    //            var domain = $(nTds[3]).text;
    //
    //console.log(index + " " + group + " = " + domain);
    //    }); // end function to view images

    // new pie charts
    var new3pArrayProvider = new Array();
    var new3pArrayCategory = new Array();
    var new3pArrayGroup = new Array();
    // create dataset for pie chart - all 3p domains
    var len = DomainsList.length;
    $.each(DomainsList, function (noofitem) {
        var domainName = this['Domain Name'];
        var value = '';
        var cnt = 0;
        var indx = 0;
        var dtype = this['Domain Type']
        if (dtype == '3P' || dtype == 'CDN' || dtype == 'self-hosted' || (dtype == 'Shard' && domainName.indexOf('metrics.') != -1)) {
            noofUniqueThirdParties++; // accumulate number of unique third parties - domains
            //console.log(this['Company'] + " " + this['Category'] + " " + this['Count']);
            cnt = this['Count'];
            // add to provider array
            value = this['Company'];
            if (!value)
                value = "Other";
            else
                value = value.replace('&amp;', '&');
            indx = -1;
            for (var i = 0; i < new3pArrayProvider.length; i++) {
                if (new3pArrayProvider[i][0] == value) {
                    indx = i;
                    break;
                }
            }
            if (indx == -1) {
                arr = new Array();
                arr[0] = value;
                arr[1] = cnt;
                new3pArrayProvider.push(arr);
                //console.log(value + " " + indx + " new: " + arr[0]  + " " + arr[1]);
            }
            else {
                new3pArrayProvider[indx][1] = new3pArrayProvider[indx][1] + cnt;
                //console.log(indx + " ex: " + new3pArrayProvider[indx][0]  + " " + new3pArrayProvider[indx][1]);
            }
            // add to category array
            value = this['Category'];
            if (!value)
                value = "Other";
            else
                value = value.replace('&amp;', '&');
            indx = -1;
            for (var i = 0; i < new3pArrayCategory.length; i++) {
                if (new3pArrayCategory[i][0] == value) {
                    indx = i;
                    break;
                }
            }
            if (indx == -1) {
                arr = new Array();
                arr[0] = value;
                arr[1] = cnt;
                new3pArrayCategory.push(arr);
            }
            else {
                new3pArrayCategory[indx][1] = new3pArrayCategory[indx][1] + cnt;
            }
            // add to group array
            value = this['Group'];
            if (value == '' || value === undefined || value == null)
                value = "Other";
            indx = -1;
            for (var i = 0; i < new3pArrayGroup.length; i++) {
                if (new3pArrayGroup[i].name == value) {
                    indx = i;
                    break;
                }
            }
            if (indx == -1) {
                arr = new Array();
                var seriesColor = '';
                switch (value) {
                    case "Advertising":
                        seriesColor = '#C5190C';
                        break;
                    case "Analytics":
                        seriesColor = '#D79115';
                        break;
                    case "Content Provision":
                        seriesColor = '#DA98B3';
                        break;
                    case "Dynamic Content":
                        seriesColor = '#853E6F';
                        break;
                    case "Financial Services":
                        seriesColor = '#316E1C';
                        break;
                    case "Fraud and Security":
                        seriesColor = '#935AE6';
                        break;
                    case "Hosted Libraries":
                        seriesColor = '#9DB181';
                        break;
                    case "Hosted Media":
                        seriesColor = '#91410F';
                        break;
                    case "Social Media":
                        seriesColor = '#F37157';
                        break;
                    case "Tag Management":
                        seriesColor = '#11B9F0';
                        break;
                    case "User Interaction":
                        seriesColor = '#275676';
                        break;
                    case "Other":
                        seriesColor = '#EED3A3';
                        break;
                } // end switch
                //console.log("pie: ",value, seriesColor)
                // arr[0] = {name: value};
                // arr[1] = {y: cnt};
                // arr[2] = {color: seriesColor};
                arr = { name: value, y: cnt, color: seriesColor };
                new3pArrayGroup.push(arr);
            }
            else {
                new3pArrayGroup[indx].y = new3pArrayGroup[indx].y + cnt;
            }

        }
    }) // end for each domainslist
//console.log(new3pArrayGroup);


    plotChartPie3PDomains(new3pArrayProvider);
    plotChartPie3P2Domains(new3pArrayCategory);
    plotChartPie3P3Domains(new3pArrayGroup);
    // third party parameters table
    // work through object array and find third parties, extract get/post parms
    var tbl_head = '';
    var tbl_body = "";


    //console.log(PostData); // postdata from PhantomJS
 //   console.log("HAR post data", HARReqPostData); // post data from HAR file

    //console.log("checking parameters");
    tbl_row = "<th>" + "ID" + "</th><th>" + "URL" + "</th><th>" + "Method" + "</th><th>" + "Parameters" + "</th>";
    tbl_head += "<tr>" + tbl_row + "</tr>";
    $.each(NewObj, function () {
        //console.log(this);
        var id = this['id'];
        var url = '';
        try {
            url = decodeURIComponent(this['Object source']);
        }
        catch (e) {
            url = unescape(this['Object source']);
        }
        var objSrcURL = getShortName(url);
        var domainRef = this['Domain ref'];
        var domainName = this['Domain'];
        //console.log(id + " " + objSrcURL + " " + domainRef);
        var parmsGET = '';
        var parmsPOST = '';
        var parmsPOSTed = '';
        var parms = '';
        var method = '';
        var tbl_row = "";
        // retrieve GET parms from querystring
        var queryString = url.substring(url.indexOf('?') + 1);
        if (queryString != url) {
            parmsGET = queryString.replace(/&amp;/g, "<br/>");
            parmsGET = parmsGET.replace(/&/g, "<br/>");
            parmsGET = parmsGET.replace(/%3F/g, "<br/>");
        }
        
        if(HARReqPostData)
        {
            method = "POST";
            $.each( HARReqPostData, function( key, value ) {
 //               console.log( key + ": " + value["url"] );
                var pdurl = value["url"];
                var pdobj = value["postData"];

  //              console.log("posdata obj",obj);
                var pd = pdobj.text
//                console.log("check postdata har",url,pd.url, pd)
                if(pdurl == url)
                {
//console.log("HAR post data", pd); // post data from HAR file

                    var textpostparms = '';
                    var pdjson = JSON.parse(pd);
  //                  console.log(pdjson);
                    $.each($.parseJSON(pd), function(key,obj) {
//console.log(key,obj)
                        textpostparms = key + "=" + obj + "<br/>";
                        parms += textpostparms;    
                    });
                }

                tbl_row = "";
                //console.log(this);
                //console.log(domainName + " = " + product + ": group/cat: '" + group + "'/'" + sitecat +"'");
                if ((domainRef == '3P' || domainRef == 'CDN' || domainRef == 'self-hosted' || (domainRef == 'Shard' && domainName.indexOf('metrics.') != -1)) && parms != '')
                {
                    tbl_row += "<td>" + id + "</td><td>" + url + "</td><td>" + method + "</td><td>" + parms + "</td>";
                    tbl_body += "<tr>" + tbl_row + "</tr>";
//console.log("3p postparms table row: " + tbl_row);
                }
                parms = '';
            }); 
        }

        // for all browser engines
//console.log("parms",browserenginever, parmsGET);
        url = url.substring(0, url.indexOf('?'));  // url without querystring
        if (browserenginever == 'Webkit (PhantomJS v2.1.1)') {
            // retrieve POST parms from postdata
            parmsPOSTed = checkPostDataForURL(url + "/")
            parmsPOST += parmsPOSTed.replace(/&/g, "<br/>");
            parmsPOST += parmsPOST.replace(/%3F/g, "<br/>");
        }
        if (parmsGET != '') {
            parms = parmsGET;
            method = "GET";
//console.log(url + ": getparms: " + parms);
        }
        else {
            parms += parmsPOST;
            method = "POST";
//console.log(url + ": postparms:  " + parms);   
        }     
     

        tbl_row = "";
        //console.log(this);
 //console.log(domainName,domainRef, url,parms);
        if ((domainRef == '3P' || domainRef == 'CDN' || domainRef == 'self-hosted' || (domainRef == 'Shard' && domainName.indexOf('metrics.') == -1)) && parms != '')
        {
//console.log("adding parms to table row");
            tbl_row += "<td>" + id + "</td><td>" + url + "</td><td>" + method + "</td><td>" + parms + "</td>";
            tbl_body += "<tr>" + tbl_row + "</tr>";
        }
//console.log("3p parms table row: " + tbl_row);
    }); // end for each newobj

    //populate table
    // console.log(tbl_head);
    // console.log(tbl_body);
    $("#TPParameters_table thead").html(tbl_head);
    $("#TPParameters_table tbody").html(tbl_body);
    //datatable initialisation
    var pTable = $("#TPParameters_table").dataTable({
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 10,
        "processing": true,
        "order": [[1, "desc"]],
        dom: 'Bfrtipl',
        buttons: [
            {
                extend: 'copyHtml5',
                title: '3p Data export'
            },
            {
                extend: 'excelHtml5',
                title: '3p Data export'
            },
            {
                extend: 'csvHtml5',
                title: '3p Data export'
            },
            {
                extend: 'pdfHtml5',
                title: '3p Data export'
            }
        ],
    });
}
function checkPostDataForURL(url) {
    //console.log("checking postdata for url:" + url);
    var res = '';
    $.each(JSON.parse(PostData), function (key, value) {
        //console.log(value);
        if (value["URL"] == url) {
            //console.log("found postdata for url:" + url + " " + value["PostData"]);
            res = value["PostData"];
        }
    });
    return res;
}
function unique(arr) {
    var hash = {}, result = [];
    for (var i = 0; i < arr.length; i++)
        if (!(arr[i] in hash)) { //it works with objects! in FF, at least
            hash[arr[i]] = true;
            result.push(arr[i]);
        }
    return result;
}

function displayTableLinks() {
    var tbl_body = "";
    var cslistoflinks = '';
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Link" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(LinkList, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            tbl_row += "<td>" + v + "</td>";
            cslistoflinks += v + ",";
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("ilnks table row: " + tbl_row);
    })
    $("#links_table tbody").html(tbl_body);
    cslistoflinks = cslistoflinks.substr(0, cslistoflinks.length - 1);
    //console.log("links csv = " +cslistoflinks);
    $("#linkscs").html("<pre>" + cslistoflinks + "<pre/>");

    if (LinkList.length == 0) {
        // remove tab and content
        document.getElementById("tablinks").remove();
        document.getElementById("tab_links").remove();
    }
}
function displayTableTimings() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Timing" + "</th>" + "<th>" + "Time (secs)" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(TimesList, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            tbl_row += "<td>" + k + "</td>";
            tbl_row += "<td>" + v + "</td>";
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("timings table row: " + tbl_row);
    })
    $("#times_table tbody").html(tbl_body);
}

function displayTableRootRedirs() {
    $noofitems = RootRedirs.length;
    if ($noofitems == 0) {
        document.getElementById("hdgrootredirs_table").remove();
        document.getElementById("rootredirs_table").remove();
    }
    else {
        var tbl_body = "";
        //var tob, tzb, tsb, tsp = 0;
        tbl_row = "<th>" + "Count" + "</th>" + "<th>" + "From URL" + "</th>" + "<th>" + "To URL" + "</th>" + "<th>" + "Redirection Method" + "</th>";
        tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
        $.each(RootRedirs, function () {
            var tbl_row = "";
            $.each(this, function (k, v) {
                //tbl_row += "<td>"+k+"</td>";
                tbl_row += "<td>" + v + "</td>";
            })
            tbl_body += "<tr>" + tbl_row + "</tr>";
            //console.log("timings table row: " + tbl_row);
        })
        $("#rootredirs_table tbody").html(tbl_body);
    }
}

function displayTable3PTagManagers() {
    $noofitems = TagManagers.length;
    if ($noofitems == 0) {
        document.getElementById("hdg3Ptagmanagers_table").remove();
        document.getElementById("3Ptagmanagers_table").remove();
    }
    else {
        var tbl_body = "";
        var vendorArray = [];
        var vendorfound = true;
        //var tob, tzb, tsb, tsp = 0;
        tbl_row = "<th>" + "Tag Manager" + "</th>" + "<th>" + "Vendor" + "</th>" + "<th>" + "Source URL" + "</th>";
        tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
        $.each(TagManagers, function () {
            var tbl_row = "";
            vendorfound = true;
            $.each(this, function (k, v) {
//console.log(k,v);
                if (vendorArray.indexOf(v) == -1 && k == 'Tagman') {
                    vendorArray.push(v);
                    //tbl_row += "<td>"+k+"</td>";
                    tbl_row += "<td>" + v + "</td>";
                    vendorfound = false;
                }
                else
                  //  if (vendorfound == false && k != 'Tagman') // comment out to see multiple versions
                   {
                        tbl_row += "<td>" + v + "</td>";
                    }
            })
            if (tbl_row != '') {
                tbl_body += "<tr>" + tbl_row + "</tr>";
                //console.log("tag mgrs table row: " + tbl_row);
            }
        })
        $("#3Ptagmanagers_table tbody").html(tbl_body);
    }
}
function displayTable3PChain(chartOpt) {
    if (typeof ThirdPartyChain == "undefined" || !(ThirdPartyChain instanceof Array)) {
        return;
    }
    var arrayGTMCalls = new Array();
    var gtmCount = 0;
    $noofitems = ThirdPartyChain.length;
    //console.log("third party chain length: " + $noofitems);
    if ($noofitems == 0) {
        document.getElementById("hdgTPChain_table").remove();
        document.getElementById("txtTPChain_table").remove();
        document.getElementById("TPChain_table").remove();
        document.getElementById("hdgTPChain_chart").remove();
        document.getElementById("TPChart_div").remove();
    }
    else {
        var tbl_row = "";
        var tbl_body = "";
        //var tob, tzb, tsb, tsp = 0;
        tbl_row = "<th>" + "Object ID" + "</th>" + "<th>" + "Object Name" + "</th>" + "<th>" + "Object Type" + "</th>" + "<th>" + "Match Level" + "</th>" + "<th>" + "Match" + "</th>" + "<th>" + "Source Object ID" + "</th>" + "<th>" + "Source Object Name" + "</th>" + "<th>" + "Source Object Type" + "</th>" + "<th>" + "Line No." + "</th>" + "</th>" + "<th>" + "status" + "</th>";
        var tbl_head = "<tr>" + tbl_row + "</tr>";
        var count = 0;
        $.each(ThirdPartyChain, function () {
            count++;
            tbl_row = "";
//console.log(this);
            $.each(this, function (k, v) {
                //tbl_row += "<td>"+k+"</td>";
                tbl_row += "<td>" + v + "</td>";
            })
            if (tbl_row != '') {
                tbl_body += "<tr>" + tbl_row + "</tr>";
                //console.log("3P chain table row: " + tbl_row);
            }

            // check for googletagmanager references
            var hostURL = '';
            var matchlevel = 99;
            var parentURL = '';
            var gtmid = '';
            var parentgtmid = '';
            var hostDomain = '';
            var parentDomain = '';
            var arr = {}; // {} will create an object
            $.each(this, function (k, v) {
                //tbl_row += "<td>"+k+"</td>";
                //console.log(k,v);
                switch (k)
                {
                    case "Object URL":
                        hostURL = v;
                        var idpos = v.indexOf("?id");
                        gtmid = v.substring(idpos+4);
                        hostDomain = extractHostname(hostURL);
                        break;
                    case "Match Level":
                        matchlevel = Number(v);
                        break;
                    case "Source URL":
                        parentURL = v;
                        var sourceidpos = v.indexOf("?id");
                        parentgtmid = v.substring(sourceidpos+4);
                        parentDomain = extractHostname(parentURL);
                        break;
                }
            })
            // check for googletagmanager in object URL - what calls it
            if(hostURL.indexOf('googletagmanager') != -1 && matchlevel < 3)
            {
//console.log("gtm found " + gtmid + "; level " + matchlevel + "; parent = " + parentURL);
                            // add to array for GTM calls
                gtmCount++;
            }
            // check for googletagmanager in source URL what  it calls
            if(parentURL.indexOf('googletagmanager') != -1 && matchlevel > 3)
            {
//console.log(parentgtmid + " gtm calls " + hostDomain + " " + matchlevel);
                // add to array for GTM calls if not present
                data = {"GTMID":parentgtmid,"domain":hostDomain};

                var found = false;
                for(var i=0;i<arrayGTMCalls.length;i++)
                {
                    if(arrayGTMCalls[i].GTMID==parentgtmid && arrayGTMCalls[i].domain == hostDomain) 
                        found = true;
                }
                if(found == false)
                    arrayGTMCalls.push(data);
            }
        }) // end each row in third party chain

        // gtm calls
console.log("Google Tag Manager analysis...");
console.log(gtmCount + " GTM tags found");
        if(gtmCount > 0)
        {
            console.log(arrayGTMCalls);




        }


        //populate call chain table
        $("#TPChain_table thead").html(tbl_head);
        $("#TPChain_table tbody").html(tbl_body);
        var oTable = $("#TPChain_table").dataTable({
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "iDisplayLength": 25,
            "processing": true,
            "order": [[0, "asc"]],
            dom: 'Bfrtipl',
            buttons: [
                {
                    extend: 'copyHtml5',
                    title: '3p chain'
                },
                {
                    extend: 'excelHtml5',
                    title: '3p chain'
                },
                {
                    extend: 'csvHtml5',
                    title: '3p chain'
                },
                {
                    extend: 'pdfHtml5',
                    title: '3p chain'
                }
            ],
        });
    } // end for each
    //console.log("chart opt = " + chartOpt);
} // end function
function displayTable3PContent()
{
    // create a table of company/product by mimetype
    var totaldomainrefs = 0;
    var domainStr = 0;
    var iV = 0;
    var sT = '';
//console.log("preparing domains 3p content table using DomainStats3PList");
//console.log(DomainStats3PList);
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Company Product" + "</th>" + "<th>" + "JavaScript" + "</th>" + "<th>" + "StyleSheet" + "</th>" + "<th>" + "HTML" + "</th>" + "<th>" + "Font" + "</th>" + "<th>" + "Image" + "</th>"+ "<th>" + "Data" + "</th>"+ "<th>" + "Other" + "</th>";
    $("#TPContent_table thead").html("<tr class=\"header\">" + tbl_row + "</tr>");
    if(!DomainStats3PList)
        return false;
    $.each(DomainStats3PList, function () {
        var tbl_row = "";
        var company = '';
        var product = '';
        var companyproduct = '';
        var domtype = '';
        var strdomains = '';
        $.each(this, function (k, v) {
//console.log(k,v);

            switch(k)
            {
                case "Company":
                    company = v;
                    break;
                case "Product":
                    product = v;
                    break;
                case "Domain Type":
                    domtype = v;
                    break;
                case "Domain Name":
                    strdomains = v;
                    break;
                default:
            }            
        })
        if(domtype != "Primary" && domtype != "Shard")
        {
            if(product.indexOf(company) == 0)
                companyproduct = product;
            else
                companyproduct = company + " " + product;
//console.log(domtype, companyproduct, strdomains);
            // get objects on these domains, for each one add to array by mime type
            var arrTotals = TotalContentThirdParties(strdomains);
//console.log(arrTotals);
            // create row of cells by mimetype
            tbl_row += "<td>" + companyproduct + "</td>";
            tbl_row += "<td>" + arrTotals.totalJS.toLocaleString() + "</td>";
            tbl_row += "<td>" + arrTotals.totalCSS.toLocaleString() + "</td>";
            tbl_row += "<td>" + arrTotals.totalHTML.toLocaleString() + "</td>";
            tbl_row += "<td>" + arrTotals.totalFont.toLocaleString() + "</td>";
            tbl_row += "<td>" + arrTotals.totalImage.toLocaleString() + "</td>";
            tbl_row += "<td>" + arrTotals.totalData.toLocaleString() + "</td>";
            tbl_row += "<td>" + arrTotals.totalOther.toLocaleString() + "</td>";
            // add to row
            tbl_body += "<tr>" + tbl_row + "</tr>";
        }
    })
    //console.log("total domain references: " + sT );
    // add to page
    $("#TPContent_table tbody").html(tbl_body);

    var oTable = $("#TPContent_table").dataTable({
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 25,
        "processing": true,
        "order": [[0, "asc"]],
        dom: 'Bfrtipl',
        buttons: [
            {
                extend: 'copyHtml5',
                title: '3p content'
            },
            {
                extend: 'excelHtml5',
                title: '3p content'
            },
            {
                extend: 'csvHtml5',
                title: '3p content'
            },
            {
                extend: 'pdfHtml5',
                title: '3p content'
            }
        ],
    });
}
function createTPChart(chartOpt) {
    if (typeof ThirdPartyChain == "undefined" || !(ThirdPartyChain instanceof Array)) {
        return;
    }
    var count = 0;
    // pick up strays - those with no parent
    //tpchart_data.push(["Teradata FLXone", "", tooltip]);
    //tpchart_data.push(["Optomaton Volvelle", "", tooltip]);
    //tpchart_data.push(["Ve Interactive Ve", "Optomaton Volvelle",""]);
    $.each(ThirdPartyChain, function () {
        count++;
        // console.log(this);
        createTPChartRowData(this, chartOpt);
    }) // end each row in third party chain
    //console.log("third party chart data);
    //console.log(tpchart_data,true);
    //   drawTPChart();
}
function createTPChartRowData(thisr, chartOpt) {
    // deal with data for hierarchy chart
    //console.log("Match: " + thisr["Match Level"] + "; id: " + thisr["Object ID"]+ "; match desc: " + thisr["Match"]  + "; url: " + thisr["Object URL"] + "; type:  " + thisr["Object Type"] + ": " + " -> " + thisr["Source ID"] + " " + thisr["Source URL"]);
    // get domain of URL
    var hostname = $('<a>').prop('href', thisr["Object URL"]).prop('hostname');
    var parent_hostname = $('<a>').prop('href', thisr["Source URL"]).prop('hostname');
    var hostid = thisr["Object ID"];
    var parentid = thisr["Source ID"];
    var siteurl = $('<a>').prop('href', SiteTitle).prop('hostname');
    var hostProduct = '';
    var hostCompany = '';
    var parentProduct = '';
    var parentCompany = ''
    var hostCoProd = '';
    var parentCoProd = '';
    var group = '';
    var sitecat = '';
    var groupColour = '';
    var ml = thisr["Match Level"];
    //if(hostname == parent_hostname) // compare domain names
    //{
    //     parent_hostname = ''; // mark this as top level
    //}
    // awlays add - even for a match against itself
    {
        //lookup domain in domains list and get company and product for host and product
        $.each(DomainsList, function () {
            //console.log(this);
            var domainName = this['Domain Name'];
            // lookup host company product
            if (hostname == domainName) {
                hostProduct = this['Product'];
                if (hostProduct === undefined || hostProduct == null || hostProduct == '') {
                    switch (chartOpt) {
                        case 1, 2:
                            hostProduct = hostname;
                            break;
                        case 3, 4:
                            hostProduct = hostname;
                            break;
                    }
                }
                // var group = this['Group'];
                // if (group === undefined || group == null)
                //     group = 'Other';
                hostCompany = this['Company'];
                //console.log("hostCompany: " + hostCompany);
                if (hostCompany === undefined || hostCompany == null || hostCompany == '') {
                }
                else
                    hostCompany = hostCompany.replace('&amp;', '&');
                group = this['Group'];
                if (group === undefined || group == null)
                    group = 'Other';
                sitecat = this['Category'];
                if (sitecat === undefined || sitecat == '' || sitecat == null)
                    sitecat = 'Other';
                // var sitecat = this['Category'];
                // if (sitecat === undefined || sitecat == '' || sitecat == null)
                //     sitecat = 'Other';
                //console.log(domainName + " = " + product + ": group/cat: '" + group + "'/'" + sitecat +"'");
            }
            // lookup parent company product
            if (parent_hostname == domainName) {
                parentProduct = this['Product'];
                if (parentProduct === undefined || parentProduct == null || parentProduct == '')
                    parentProduct = parent_hostname;
                // var group = this['Group'];
                // if (group === undefined || group == null)
                //     group = 'Other';
                parentCompany = this['Company'];
                if (parentCompany === undefined || parentCompany == null || parentCompany == '') {
                }
                else
                    parentCompany = parentCompany.replace('&amp;', '&');
                // var sitecat = this['Category'];
                // if (sitecat === undefined || sitecat == '' || sitecat == null)
                //     sitecat = 'Other';
                //console.log(domainName + " = " + product + ": group/cat: '" + group + "'/'" + sitecat +"'");
            }
        });
        // experiment
        if (hostname.indexOf("demdex") > 0) {
//console.log("demdex info: " + hostid + " " + hostname + " " + hostProduct + " = " + parentid + " " + parent_hostname + " " + parentProduct);
        }
        if (hostProduct.indexOf(hostCompany) == -1)
            hostCoProd = hostCompany + " " + hostProduct;
        else
            hostCoProd = hostProduct;
        if (hostCoProd == '')
            hostCoProd = hostname;
        if (parentProduct.indexOf(parentCompany) == -1)
            parentCoProd = parentCompany + " " + parentProduct;
        else
            parentCoProd = parentProduct;
        if (parentCoProd == '')
            parentCoProd = parent_hostname;
        //check for circurlar reference in array
        //  var circfound = false;
        //  $.each(tpchart_data, function(key, value) {
        // //    console.log(value)
        //     switch (chartOpt)
        //     {
        //         case 1,2:
        //             if(value[1] == hostCoProd && value[0] == parentCoProd || value[0] == hostCoProd && value[1] == parentCoProd)
        //                 circfound = true;
        //             break;
        //         case 3,4:
        //             if(value[1] == hostCoProd && value[0] == parentCoProd)
        //                 circfound = true;
        //             break;
        //     }
        // });
        // if(circfound == true)
        //     console.log("circular ref found for: " + hostCoProd + " " + parentCoProd);
        //check for duplicate in array and add - domain names
        var found = false;
        $.each(tpchart_data, function (key, value) {
            //    console.log(value)
            switch (chartOpt) {
                case 1, 2:
                    if (value[0] == hostCoProd && value[1] == parentCoProd || value[1] == hostCoProd && value[0] == parentCoProd)
                        found = true;
                    break;
                case 3, 4:
                    if (value[0] == hostCoProd && value[1] == parentCoProd)
                        found = true;
                    break;
            }
        });
        if (found == false && hostCoProd != parentCoProd) {
            // if(hostname == "x.bidswitch.net")
            //console.log("new: " + hostCoProd + " -> " +  parentCoProd + " (" + hostname + " -> " +  parent_hostname + ")");
            var tooltip = group + " - " + sitecat;
            var nodeData = hostCoProd;
            var groupColours = getGroupColours(group);
            groupColour = groupColours[0];
            //nodeData = {v: hostCoProd, f:'<div style="color: white; background-color: '+ groupColour + '">' + hostCoProd + '</div>'};
            if (parentCoProd == "localhost") {
                //console.log(hostCoProd + " found " + parentCoProd);
                parentCoProd = "";
            }
            // // check if an entry exists for the parent but without a parent of its own, and remove it
            var len = tpchart_data.length - 1;
            for (var i = len; i > 0; i--) {
                var v = tpchart_data[i];
                //console.log("ex: " + i + " " + v[0]['v'] + " parent:'" + v[1] + "'");
                if (v[0]['v'] == parentCoProd && v[1] == '') {
                    //console.log(i + ": top entry detected: " + v[0]['v'] + " parent:" + v[1]);
                    tpchart_data.splice(i, 1);
                    len = tpchart_data.length;
                }
            }
            switch (chartOpt) {
                case 1:
                    nodeData = hostCoProd;
                    tpchart_data.push([nodeData, parentCoProd, tooltip]);
                    break;
                case 2:
                    nodeData = { v: hostCoProd, f: hostCoProd + '<div style="font-size: 70%; color: white; background-color: ' + groupColour + '"><i>' + group + '</i></div>' };
                    //console.log(hostCoProd + " : '" + parentCoProd + "'" + ' ' + groupColour + '" ' + group);
                    tpchart_data.push([nodeData, parentCoProd, tooltip]);
                    break;
                case 3:
                    // individual file nodes - not working
                    // tpchart_data.push([{v:thisr["Object ID"].toString(),f:hostCoProd + '<div style="background-color: '+ groupColour + '">' + group + '</div>'}, thisr["Source ID"].toString(), ""]);
                    nodeData = { v: hostid.toString(), f: hostCoProd + '<div style="background-color: ' + groupColour + '">' + group + '</div>' };
                    //console.log(hostid.toString() + ": " + hostCoProd + " : '" + parentCoProd + "'");
                    tpchart_data.push([nodeData, parentid.toString(), tooltip]);
                    break;
                case 4:
                    // all domains - not working
                    nodeData = { v: hostname, f: hostname + "<br/>" + hostCoProd + '<div style="background-color: ' + groupColour + '">' + group + '</div>' };
                    tpchart_data.push([nodeData, parent_hostname, tooltip]);
                    break;
            } // end switch chart option   
        }
        else {
            // if(hostCoProd == parentCoProd)
            //     console.log("bypass: " + hostCoProd + " -> " +  parentCoProd + " (" + hostname + " -> " +  parent_hostname + ")");
            // else
            //     console.log("found: " + hostCoProd + " -> " +  parentCoProd + " (" + hostname + " -> " +  parent_hostname + ")");
        }
    }
} // end createTPChartRowData
function drawTPChart() {
    //   tpchart_data.push([2,"",""]);
    //   tpchart_data.push([42,2,""]);
    //   tpchart_data.push([23,2,""]);
    //console.log(tpchart_data);
    // display chart
    //console.log("drawing tpchart");
    //console.log(tpchart_data);
    tphdata = new google.visualization.DataTable();
    tphdata.addColumn('string', 'Name');
    tphdata.addColumn('string', 'Parent');
    tphdata.addColumn('string', 'ToolTip');
    // data format for org chart
    // [
    //     [{v:'Mike', f:'Mike<div style="color:red; font-style:italic">President</div>'},
    //     '', 'The President'],
    //     [{v:'Jim', f:'Jim<div style="color:red; font-style:italic">Vice President</div>'},
    //     'Mike', 'VP'],
    //     ['Alice', 'Mike', ''],
    //     ['Bob', 'Jim', 'Bob Sponge'],
    //     ['Carol', 'Bob', '']
    // ]
    // For each orgchart box, provide the name, manager, and tooltip to show.
    tphdata.addRows(tpchart_data);
    tphoptions = {
        chartArea: { width: '100%', height: '100%' },
        forceIFrame: 'false',
        is3D: 'true',
        pieSliceText: 'value',
        sliceVisibilityThreshold: 1 / 20, // Only > 5% will be shown.
        titlePosition: 'none'
    };
    // Create the chart.
    gchart = new google.visualization.OrgChart(document.getElementById('TPChart_div'));
    // Draw the chart, setting the allowHtml option to true for the tooltips.
    drawGchart(tphdata, tphoptions, "Large");
    google.visualization.events.addListener(gchart, 'collapse', function (e) {
        console.log("chart collapse triggered");
    });
}
function drawGchart(data, options, nodesize) {
    var options = {
        allowHtml: true,
        size: nodesize,
        nodeClass: 'myNodeClass',
        width: '1920',
        height: '900',
        allowCollapse: true
    };
    gchart.draw(data, options);
}
function getNodedata_thirdpartynetwork(mode) {
    // work through domains list
    var hostProduct = '';
    var hostCompany = '';
    var hostCoProd = '';
    var domainIndex = 0;
    var group = '';
    var sitecat = '';
    //lookup domain in domains list and get company and product for host and product
    $.each(DomainsList, function () {
        //console.log(this);
        var domainName = this['Domain Name'];
        var domainType = this['Domain Type'];
        var domainBytes = parseInt(this['TotBytes']);
        var domainOffset = parseInt(this['Offset']);
        // lookup host company product
        hostProduct = this['Product'];
        if (hostProduct === undefined || hostProduct == null || hostProduct == '') {
            hostProduct = domainName;
        }
        // var group = this['Group'];
        // if (group === undefined || group == null)
        //     group = 'Other';
        hostCompany = this['Company'];
        //console.log("hostCompany: " + hostCompany);
        if (hostCompany === undefined || hostCompany == null || hostCompany == '') {
        }
        else
            hostCompany = hostCompany.replace('&amp;', '&');
        // consolidate company product names
        if (hostProduct.indexOf(hostCompany) == -1)
            hostCoProd = hostCompany + " " + hostProduct;
        else
            hostCoProd = hostProduct;
        if (hostCoProd == '')
            hostCoProd = hostname;
        group = this['Group'];
        if (group === undefined || group == null)
            group = 'Other';
        sitecat = this['Category'];
        if (sitecat === undefined || sitecat == '' || sitecat == null)
            sitecat = 'Other';
        var tooltip = '';
        if (group != sitecat)
            tooltip = group + " - " + sitecat;
        else
            tooltip = group;
        tooltip += "<br/>" + hostCoProd;
        var groupColours = getGroupColours(group);
        var groupColour = groupColours[0];
        var fontStyle = groupColours[1];
        //console.log(groupColours);
        if ((domainType == "Primary" || domainType == "Shard")) {
            groupColour = '#b0d7ee';
            if (nodeshape == 'box')
                fontStyle = '14px arial black';
            else
                fontStyle = '14px arial silver';
            //console.log("overriding colours for primary domain");
        }
        //console.log(mode,nodecolouring,browserengineversion);
        if (mode == "D" && nodecolouring == "times" && browserengineversion == "WebpageTest") {
            // override third party group colouring with timings from WebPageTest at domain level
            //console.log("ReqMap Timing override: " + domainName + ": render start: " +  renderStartMS + "; domload: " + DOMCompleteMS + "; Onload: " + onLoadMS + "; total: " + docTime + " - " + "; offset: " + domainOffset);
            //timing colors
            if (domainOffset == 99999 || isNaN(domainOffset)) {
                if (bgcol == 'd') {
                    fontStyle = '14px arial white';
                    groupColour = '#FFFFFF';
                }
                else {
                    if (nodeshape == "dot") 
                        fontStyle = '14px arial #AAAAAA';
                    else
                        fontStyle = '14px arial yellow';
                    groupColour = '#AAAAAA';
                }
                //console.log("ReqMap Timing override: " + domainName + ": no offset defined - white");
            }
            else
                if (domainOffset < renderStartMS) {
                    // before render start = green
                    if (bgcol == 'd') {
                        fontStyle = '14px arial white';
                        groupColour = '#008800';
                    }
                    else {
                        if (nodeshape == "dot") 
                            fontStyle = '14px arial #008800';
                        else
                            fontStyle = '14px arial yellow';
                        groupColour = '#008800';
                    }
                    //console.log("ReqMap Timing override: " + domainName + ": render start: " +  renderStartMS + "; offset: " + domainOffset + " - green");
                }
                else
                    if (domainOffset < DOMCompleteMS) {
                        // between render and dom load = blue
                        if (bgcol == 'd') {
                            fontStyle = '14px arial white';
                            groupColour = '#000088';
                        }
                        else {
                            if (nodeshape == "dot") 
                                fontStyle = '14px arial #000088';
                            else
                                fontStyle = '14px arial yellow';
                            groupColour = '#000088';
                        }
                        //console.log("ReqMap Timing override: " + domainName + " domload: " + DOMCompleteMS + " offset: " + domainOffset + " - blue");
                    }
                    else
                        if (domainOffset < onLoadMS) {
                            // between dom load and onLoad = red
                            if (bgcol == 'd') {
                                fontStyle = '14px arial white';
                                groupColour = '#BB0000';
                            }
                            else {
                                if (nodeshape == "dot") 
                                    fontStyle = '14px arial #AA0000';
                                else
                                    fontStyle = '14px arial yellow';
                                groupColour = '#CC0000';
                            }
                            //console.log("ReqMap Timing override: " + domainName + " Onload: " + onLoadMS + "; offset: " + domainOffset + " - red");
                        }
                        else {
                            // after Onload = black
                            if (bgcol == 'd') {
                                fontStyle = '14px arial white';
                                groupColour = '#DBDADA';
                            }
                            else {
                                if (nodeshape == "dot") 
                                    fontStyle = '14px arial black';
                                else
                                    fontStyle = '14px arial yellow';
                                groupColour = '#000000';
                            }
                            //console.log("ReqMap Timing override: " + domainName + "; total: " + docTime + " - " + "; offset: " + domainOffset + " - black");
                        }
        }
        //console.log(domainName + " = " + hostCoProd + ": group/cat: '" + group + "'/'" + sitecat +"' " +  domainType );
        // check if dmainName exists
        var domainFound = false;
        var dkey = -1;
        for (k in thirdpartynetworknodes_data) {
            if ((mode == "D" && thirdpartynetworknodes_data[k]['label'] == domainName) || (mode == "CP" && thirdpartynetworknodes_data[k]['label'] == hostCoProd)) {
                domainFound = true;
                dkey = k;
            }
        }
    
         // add domain into list if not already added
        if (domainFound == false) {
            if (mode == "D")
                thirdpartynetworknodes_data.push({ id: domainIndex, label: domainName, color: groupColour, value: domainBytes, title: tooltip, font: fontStyle });
            else
                thirdpartynetworknodes_data.push({ id: domainIndex, label: hostCoProd, color: groupColour, value: domainBytes, title: tooltip, font: fontStyle });
        }
        else {
            if (mode != "D" && dkey != -1) {
                thirdpartynetworknodes_data[dkey]['value'] = thirdpartynetworknodes_data[dkey]['value'] + domainBytes;
                //console.log("CP","adding bytes",hostCoProd,dkey,domainBytes);
            }
        }
        domainIndex++;
    });
    //console.log(thirdpartynetworknodes_data);
    return thirdpartynetworknodes_data;
}
function getGroupColours(group) {
    var fontStyle = '14px arial silver';
    var groupColour = '';
    switch (group) {
        case "Advertising":
            groupColour = '#C5190C';
            fontStyle = '14px arial #FFFACD';
            break;
        case "Analytics":
            groupColour = '#D79115';
            fontStyle = '14px arial #000000';
            break;
        case "Content Provision":
            groupColour = '#DA98B3';
            fontStyle = '14px arial #00202A';
            break;
        case "Dynamic Content":
            groupColour = '#853E6F';
            fontStyle = '14px arial #FFFACD';
            break;
        case "Financial Services":
            groupColour = '#316E1C';
            fontStyle = '14px arial #FFFFFF';
            break;
        case "Fraud & Security":
            groupColour = '#935AE6';
            fontStyle = '14px arial #FFFFFF';
            break;
        case "Hosted Libraries":
            groupColour = '#9DB181';
            fontStyle = '14px arial #000036';
            break;
        case "Hosted Media":
            groupColour = '#91410F';
            fontStyle = '14px arial #FFFACD';
            break;
        case "Social Media":
            groupColour = '#F37157';
            fontStyle = '14px arial #000000';
            break;
        case "Tag Management":
            groupColour = '#11B9F0';
            fontStyle = '14px arial #000000';
            break;
        case "User Interaction":
            groupColour = '#275676';
            fontStyle = '14px arial #E0FFFF';
            break;
        case "Other":
            groupColour = '#EED3A3';
            fontStyle = '14px arial #002A00';
            break;
        default:
            groupColour = '#b0d7ee';
            fontStyle = '14px arial red';
            break;
    }
    var myArray = new Array(1);
    myArray[0] = groupColour;
    if (nodeshape == 'box')
        myArray[1] = fontStyle;
    else {
        if (bgcol == 'd')
            myArray[1] = '14px arial white'; // dark background
        else
            myArray[1] = '14px arial black'; // light background
    }
    return myArray;
}
function extractHostname(url) {
    var hostname;
    //find & remove protocol (http, ftp, etc.) and get hostname
    if (url.indexOf("://") > -1) {
        hostname = url.split('/')[2];
    }
    else {
        hostname = url.split('/')[0];
    }
    //find & remove port number
    hostname = hostname.split(':')[0];
    //find & remove "?"
    hostname = hostname.split('?')[0];
    return hostname;
}
function getLinksdata_thirdpartynetwork() {
    var groupColour = '';
    // work through third party call chain, identify domain and create a link
    $.each(ThirdPartyChain, function () {
        // /console.log(this);
        var hostid = this["Object ID"];
        var hostURL = this["Object URL"];
        var parentid = this["Source ID"];
        var parentURL = this["Source URL"];
        var matchlevel = this["Match Level"];

        // get domains for host and parent
        var hostDomain = extractHostname(hostURL);
        var parentDomain = extractHostname(parentURL);

        //console.log(hostURL, hostDomain);
        // lookup domain in domain array
        var domainFound = false;
        var hostKey = -1;
        var parentKey = -1;
        for (k in thirdpartynetworknodes_data) {
//console.log(k, thirdpartynetworknodes_data[k]['label'])
            if (thirdpartynetworknodes_data[k]['label'] == hostDomain) {
                hostKey = thirdpartynetworknodes_data[k]['id'];
                groupColour = thirdpartynetworknodes_data[k]['colour'];
            }
            if (thirdpartynetworknodes_data[k]['label'] == parentDomain)
                parentKey = thirdpartynetworknodes_data[k]['id'];
        }
        var linkFound = false;
        for (k in thirdpartynetworklinks_data) {
            if (thirdpartynetworklinks_data[k]['id'] == parentKey + "-" + hostKey)
                linkFound = true;
        }
        //console.log("link from " + hostDomain + " to " + parentDomain + "; from " + parentKey+ " to " + hostKey);
        // add domain into list if not already added
        if (linkFound == false) {
            //console.log("adding link from " + hostDomain + " to " + parentDomain + "; from " + parentKey+ " to " + hostKey);
            thirdpartynetworklinks_data.push({ id: parentKey + "-" + hostKey, to: hostKey, from: parentKey, color: groupColour });
        }
    }) // end each row in third party chain
    //console.log(thirdpartynetworklinks_data);
    return thirdpartynetworklinks_data;
}
function getLinksdata_thirdpartynetwork_CP() {
    // work through third party call chain, identify domain and create a link
    $.each(ThirdPartyChain, function () {
        //console.log(this);
        var hostid = this["Object ID"];
        var hostURL = this["Object URL"];
        var parentid = this["Source ID"];
        var parentURL = this["Source URL"];
        // get domains for host and parent
        var hostDomain = extractHostname(hostURL);
        var parentDomain = extractHostname(parentURL);
        //console.log(hostURL, hostDomain);
        // lookup domain in domain array
        var domainFound = false;
        var hostKey = -1;
        var parentKey = -1;
        var hostCoProd = '';
        var parentCoProd = '';
        var parentCompany = '';
        var hostCompany = '';
        var parentProduct = '';
        var hostProduct = '';
        var group = '';
        var groupColor = '';
        $.each(DomainsList, function () {
            //console.log(this);
            var domainName = this['Domain Name'];
            var domainType = this['Domain Type'];
            if (hostDomain == domainName) {
                // lookup host company product
                hostProduct = this['Product'];
                if (hostProduct === undefined || hostProduct == null || hostProduct == '') {
                    hostProduct = domainName;
                }
                // var group = this['Group'];
                // if (group === undefined || group == null)
                //     group = 'Other';
                hostCompany = this['Company'];
                //console.log("hostCompany: " + hostCompany);
                if (hostCompany === undefined || hostCompany == null || hostCompany == '') {
                }
                else
                    hostCompany = hostCompany.replace('&amp;', '&');
                // consolidate company product names
                if (hostProduct.indexOf(hostCompany) == -1)
                    hostCoProd = hostCompany + " " + hostProduct;
                else
                    hostCoProd = hostProduct;
                if (hostCoProd == '')
                    hostCoProd = hostDomain;
            }
            if (parentDomain == domainName) {
                // lookup host company product
                parentProduct = this['Product'];
                if (parentProduct === undefined || parentProduct == null || parentProduct == '') {
                    parentProduct = domainName;
                }
                // var group = this['Group'];
                // if (group === undefined || group == null)
                //     group = 'Other';
                parentCompany = this['Company'];
                //console.log("hostCompany: " + hostCompany);
                if (parentCompany === undefined || parentCompany == null || parentCompany == '') {
                }
                else
                    parentCompany = parentCompany.replace('&amp;', '&');
                // consolidate company product names
                if (parentProduct.indexOf(parentCompany) == -1)
                    parentCoProd = parentCompany + " " + parentProduct;
                else
                    parentCoProd = parentProduct;
                if (parentCoProd == '')
                    parentCoProd = parentDomain;
                group = this['Group'];
                if (group === undefined || group == null)
                    group = 'Other';
            }
        });
        for (k in thirdpartynetworknodes_data) {
            //console.log(k, thirdpartynetworknodes_data[k]['label'])
            if (thirdpartynetworknodes_data[k]['label'] == hostCoProd) {
                hostKey = thirdpartynetworknodes_data[k]['id'];
            }
            if (thirdpartynetworknodes_data[k]['label'] == parentCoProd) {
                parentKey = thirdpartynetworknodes_data[k]['id'];
            }
        }
        var groupColours = getGroupColours(group);
        groupColour = groupColours[0];
        var linkFound = false;
        for (k in thirdpartynetworklinks_data) {
            if (thirdpartynetworklinks_data[k]['id'] == parentKey + "-" + hostKey)
                linkFound = true;
        }
        //console.log("checking link from " + hostCoProd + " to " + parentCoProd + "; from " + hostKey+ " to " + parentKey);
        // add domain into list if not already added
        if (linkFound == false) {
            //console.log("adding link from " + hostCoProd + " to " + parentCoProd + "; from " + hostKey+ " to " + parentKey);
            thirdpartynetworklinks_data.push({ id: parentKey + "-" + hostKey, to: hostKey, from: parentKey, color: groupColour });
        }
    }) // end each row in third party chain
    //console.log(thirdpartynetworklinks_data);
    return thirdpartynetworklinks_data;
}
function visjs_thirdpartynetwork(mode) {
    var nodes = '';
    var edges = '';
    if (mode == "D") {
        // create an array with nodes, based on domains
        nodes = new vis.DataSet(getNodedata_thirdpartynetwork("D"));
        // create an array with edges, based on third part call chain
        edges = new vis.DataSet(getLinksdata_thirdpartynetwork());
    }
    else {
        // create an array with nodes, based on domains
        nodes = new vis.DataSet(getNodedata_thirdpartynetwork("CP"));
        // create an array with edges, based on third part call chain
        edges = new vis.DataSet(getLinksdata_thirdpartynetwork_CP());
    }
    // set layout options
    var netval = $("input[type='radio'][name='netlayout']:checked").val();
    console.log("generating third party network " + mode + " " + nodecolouring + " " + netval);
    // console.log(thirdpartynetworknodes_data);
    // console.log(thirdpartynetworklinks_data);
    var options = '';
    switch (netval) {
        case "N":
            layout = {
                randomSeed: undefined,
                improvedLayout: true,
                hierarchical: {
                    enabled: false,
                }
            }
            break;
        case "UD":
            layout = {
                randomSeed: undefined,
                improvedLayout: true,
                hierarchical: {
                    enabled: true,
                    levelSeparation: 150,
                    nodeSpacing: 100,
                    treeSpacing: 200,
                    blockShifting: true,
                    edgeMinimization: true,
                    parentCentralization: true,
                    direction: 'UD',        // UD, DU, LR, RL
                    sortMethod: 'directed'   // hubsize, directed
                }
            }
            break;
        case "LR":
            layout = {
                randomSeed: undefined,
                improvedLayout: true,
                hierarchical: {
                    enabled: true,
                    levelSeparation: 150,
                    nodeSpacing: 100,
                    treeSpacing: 200,
                    blockShifting: true,
                    edgeMinimization: true,
                    parentCentralization: true,
                    direction: 'LR',        // UD, DU, LR, RL
                    sortMethod: 'directed'   // hubsize, directed
                }
            }
            break;
    } // end switch
    // create a network
    container = document.getElementById('TPnetwork');
    //console.log("nodeshape = " + nodeshape);
    // provide the data in the vis format
    data = {
        nodes: nodes,
        edges: edges
    };
    var options = {
        nodes: {
            shape: nodeshape,
            scaling: {
                customScalingFunction: function (min, max, total, value) {
                    return value / total;
                },
                min: 5,
                max: 100,
                label: {
                    min: 8,
                    max: 20
                }
            }
        },
        edges: {
            arrows: 'to'
        },
        physics: {
            "enabled": true,
            stabilization: {
                enabled: true,
                iterations: 10, // maximum number of iteration to stabilize
                updateInterval: 2,
                onlyDynamicEdges: false,
                fit: true
            },
        },
        interaction: {
            dragNodes: true,
            dragView: true
        },
        layout: layout,
    };
    // initialize your network!
    network = new vis.Network(container, data, options);
    network.fit();
}//
function displayTableGZIPFiles() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Domain Type" + "</th>" + "<th>" + "Object Type" + "</th>" + "<th>" + "URL" + "</th>" + "<th>" + "Original Bytes" + "</th>" + "<th>" + "GZIP Bytes" + "</th>" + "<th>" + "Saving Bytes" + "</th>" + "<th>" + "Saving PCT" + "</th>";
    tbl_body += "<tr>" + tbl_row + "</tr>";
    $.each(GzipStats, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            if (k > 3)
                tbl_row += "<td>" + v + "</td>";
            else
                tbl_row += "<td class=\"limit\">" + v + "</td>";
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("table row: " + tbl_row);
    })
    $.each(GzipTotals, function () {
        var tbl_ftrow = "";
        // 3 blank cells before totals
        tbl_ftrow += "<td></td>";
        tbl_ftrow += "<td></td>";
        $.each(this, function (k, v) {
            tbl_ftrow += "<td>" + v + "</td>";
        })
        tbl_body += "<tr>" + tbl_ftrow + "</tr>";
        //console.log("gzip table row: " + tbl_ftrow);
    })
    $("#gzip_table tbody").html(tbl_body);

}

function displayTableDomains() {
    var totaldomainrefs = 0;
    var domainpct = 0;
    var iV = 0;
    var sT = '';
    //console.log("domains table 1");
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Domain Name" + "</th>" + "<th>" + "Count" + "</th>" + "<th>" + "Type" + "</th>" + "<th>" + "Network" + "</th>" + "<th>" + "Total Bytes" + "</th>";
    $("#domains_table thead").html(tbl_row);
    $.each(DomainsList, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            if (k == 'Domain Name' || k == 'Count' || k == 'Domain Type' || k == 'Network' || k == 'TotBytes') // exclude locations
            {
                tbl_row += "<td>" + v + "</td>";
                if (k == "Count") {
                    iV = Number(v);
                    //console.log("domain row value: " + v);
                    totaldomainrefs += iV;
                    //alert(k + " " + v);
                }
            }
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("domain row: " + tbl_row);
        sT = totaldomainrefs.toString();
    })
    $("#domains_table tbody").html(tbl_body);
    console.log("total domain references: " + sT);
    $("#domains_table tbody").html(tbl_body);
    //convert to datatable
    // var oTable = $("#domains_table").dataTable( {
    //     "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    //     "iDisplayLength": 25,
    //     "processing": true,
    //     "aaSorting": [[1, "desc"]], // sorts by a column and direction as set by the table type
    //     dom: 'Bfrtipl',
    //     buttons: [
    //         {
    //             extend: 'copyHtml5',
    //             title: 'Domains'
    //         },
    //         {
    //             extend: 'excelHtml5',
    //             title: 'Domains'
    //         },
    //         {
    //             extend: 'csvHtml5',
    //             title: 'Domains'
    //         },
    //         {
    //             extend: 'pdfHtml5',
    //             title: 'Domains'
    //         }
    //     ],
    //     colReorder: true,
    // });

    //console.log("domains 2");
    // create dataset for pie chart - all domains
    var dataset = "[";
    var len = DomainsList.length;
    $.each(DomainsList, function (noofitem) {
        var data_row = "[";
        $.each(this, function (k, v) {
            if (k == 'Domain Name') {
                data_row = '["' + v + '",';
            }
            if (k == 'Count') {
                domainpct = v; //parseFloat(parseInt(v) / totaldomainrefs * 100).toFixed(1);
                data_row += domainpct.toString() + ']';

                if (noofitem < len - 1) {
                    data_row += ",";
                }
            }
        })
        dataset += data_row;
        //console.log("domain data row: " + data_row);
    })
    dataset = dataset + "]";
    //console.log("domains 3");
    // create dataset for pie chart - types of domain
    var datasettypes = "[";
    var len = DomainsList.length;
    var countPrimary = 0;
    var countShard = 0;
    var countCDN = 0;
    var count3P = 0;
    var type = '';
    var count = 0;
    var network = '';
    $.each(DomainsList, function (index) {

        var data_row = "[";
        type = this['Domain Type'];
        count = this['Count'];
        network = this['Network'];
        //console.log	("e domain: " + index + ": " + type + " - " + count+ " - " + network);
        //console.log(type + ': ' + count);
        switch (type) {
            case 'Primary':
                //if(network != '')
                //    countCDN = countCDN + count;
                //else
                countPrimary = countPrimary + count;
                break;
            case 'Shard':
                //if(network != '')
                //    countCDN = countCDN + count;
                //else
                countShard = countShard + count;
                break;
            case '3P':
                count3P = count3P + count;
                break;
            case 'CDN':
                countCDN = countCDN + count;
                break;
        }

        //console.log("domain data row: " + data_row);
    }) // end for each item in array
    //console.log("primary domain count: " + countPrimary);
    data_row = '["Primary",';
    domainpct = parseFloat(parseInt(countPrimary) / totaldomainrefs * 100).toFixed(1);
    data_row += domainpct.toString() + '],';
    datasettypes += data_row;
    data_row = '["Shard",';
    domainpct = parseFloat(parseInt(countShard) / totaldomainrefs * 100).toFixed(1);
    data_row += domainpct.toString() + '],';
    datasettypes += data_row;
    data_row = '["CDN",';
    domainpct = parseFloat(parseInt(countCDN) / totaldomainrefs * 100).toFixed(1);
    data_row += domainpct.toString() + '],';
    datasettypes += data_row;
    data_row = '["Third Parties",';
    domainpct = parseFloat(parseInt(count3P) / totaldomainrefs * 100).toFixed(1);
    data_row += domainpct.toString() + ']';
    datasettypes += data_row;
    datasettypes = datasettypes + "]";

    //console.log("domain data: " + datasettypes);
    var ds = jQuery.parseJSON(dataset);
    var dst = jQuery.parseJSON(datasettypes);
    plotChartPieDomains(ds);
    plotChartPieDomainTypes(dst);
    //plotChartPyramidDomains(ds);
}
function displayTableDomains3P() {
    if (!DomainStats3PList) {
        $('#TPDomains_table thead').html("<tr class=\"header\">No Domains</tr>");
        return;
    }
    var totaldomainrefs = 0;
    var domainStr = 0;
    var iV = 0;
    var sT = '';
    //console.log("domains 3p table 1");
//console.log(DomainStats3PList);
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "</th>" + "<th>" + "Domain Name(s)" + "</th>" + "<th>" + "Request Count" + "</th>" + "<th>" + "Network" + "</th>" + "<th>" + "Company" + "</th>" + "<th>" + "Product(s)" + "</th>" + "<th>" + "Location" + "</th><th>" + "Total Bytes" + "</th>";
    $("#TPDomains_table thead").html("<tr class=\"header\">" + tbl_row + "</tr>");
    $.each(DomainStats3PList, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            if (k == 'Domain Name' || k == 'Count' || k == 'Network' || k == 'TotBytes' || k == 'Company' || k == 'Product' || k == 'Edge Loc') // exclude locations
            {
                if (k == "TotBytes") {
                    tbl_row += "<td>" + parseInt(v).toLocaleString() + "</td>";
                    //console.log("totbytes=" + v + " = " +  parseInt(v).toLocaleString());
                }
                else
                    tbl_row += "<td>" + v + "</td>";
                if (k == 'Count') {
                    iV = Number(v);
                    //console.log("domain row value: " + v);
                    totaldomainrefs += iV;
                    //alert(k + " " + v);
                }
            }
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("domain row: " + tbl_row);
        sT = totaldomainrefs.toString();
    })
    //console.log("total domain references: " + sT );
    // add to page
    $("#TPDomains_table tbody").html(tbl_body);
    // convert to datatable
    var oTable = $("#TPDomains_table").dataTable({
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 25,
        "processing": true,
        "aaSorting": [[6, "desc"]], // sorts by a column and direction as set by the table type
        dom: 'Bfrtipl',
        buttons: [
            {
                extend: 'copyHtml5',
                title: 'Domains'
            },
            {
                extend: 'excelHtml5',
                title: 'Domains'
            },
            {
                extend: 'csvHtml5',
                title: 'Domains'
            },
            {
                extend: 'pdfHtml5',
                title: 'Domains'
            }
        ],
        colReorder: true,
    });
    // reorder
    oTable.fnColReorder(3, 0);//move the company name to the first column
    oTable.fnColReorder(4, 1);//move the products column to the second column
    oTable.fnColReorder(6, 2);//move the total bytes column to the third column
    oTable.fnAdjustColumnSizing();//a good idea to make sure there will be no displaying issues
    /*
 
      * Insert a 'details' expansion column to the table
 
      */
    var nCloneTh = document.createElement('th');
    var nCloneTd = document.createElement('td');
    nCloneTd.innerHTML = '<img class="expcon" src="/toaster/images/details_open.png">';
    nCloneTd.className = "center";
    $("#TPDomains_table" + ' thead tr').each(function () {
        this.insertBefore(nCloneTh, this.childNodes[0]);
    });
    $("#TPDomains_table" + ' tbody tr').each(function () {
        this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
    });
    var lastcol = 8;
    //last col - repeat row expansion icon
    var nzCloneTh = document.createElement('th');
    var nzCloneTd = document.createElement('td');
    nzCloneTd.innerHTML = '<img class="expcon" src="/toaster/images/details_open.png">';
    nzCloneTd.className = "center";
    $("#TPDomains_table" + ' thead tr').each(function () {
        this.insertBefore(nzCloneTh, this.childNodes[lastcol]);
    });
    $("#TPDomains_table" + ' tbody tr').each(function () {
        this.insertBefore(nzCloneTd.cloneNode(true), this.childNodes[lastcol]);
    });

    /* Add event listener for opening and closing details
  
       * Note that the indicator for showing which row is open is not controlled by DataTables,
  
       * rather it is done here
  
       */
    $('#TPDomains_table').on('click', 'tbody td img.expcon', function () {
        nTr = $(this).parents('tr')[0];
        //console.log('click ' + 'third parties table' + ' = ' + nTr);
        if (oTable.fnIsOpen(nTr)) {
            /* This row is already open - close it */
            this.src = "/toaster/images/details_open.png";
            oTable.fnClose(nTr);
        }
        else {
            /* Open this row */
            this.src = "/toaster/images/details_close.png";
            var row = $(this).closest('tr'),
                data = oTable._(row),
                domains = data[0][3];
            //console.log("domains = " + domains);
            oTable.fnOpen(nTr, fnFormatThirdParties(domains), 'details');
        }
    });
    //console.log("domains 2");
    // create dataset for pie chart - 3p domains - by requests
    var dataset1 = "[";
    var len = DomainStats3PList.length;
    $.each(DomainStats3PList, function (noofitem) {
        var data_row = "[";
        var data_rowpt2 = "";
        $.each(this, function (k, v) {
            if (k == 'Company') {
                if (!v)
                    v = 'Other';
                else
                    v = v.replace('&amp;', '&');
                data_row = '["' + v + '",';
            }
            //console.log(k);
            if (k == 'Count') {
                data_rowpt2 += v + ']';

                if (noofitem < len - 1) {
                    data_rowpt2 += ",";
                }
            }
        })
        dataset1 += data_row + data_rowpt2;
        //console.log("3p domain data row: " + data_row + data_rowpt2);
    })
    dataset1 = dataset1 + "]";
    //console.log("domains 6");
    // create dataset for pie chart - all domains by bytes
    var dataset2 = "[";
    var len = DomainStats3PList.length;
    $.each(DomainStats3PList, function (noofitem) {
        data_row = "[";
        $.each(this, function (k, v) {
            if (k == 'Company') {
                if (!v)
                    v = 'Other';
                else
                    v = v.replace('&amp;', '&');
                data_row = '["' + v + '",';
            }
            if (k == 'TotBytes') {
                domainStr = v; //parseFloat(parseInt(v) / totaldomainrefs * 100).toFixed(1);
                data_row += domainStr.toString() + ']';

                if (noofitem < len - 1) {
                    data_row += ",";
                }
            }
        })
        dataset2 += data_row;
        //console.log("domain data row: " + data_row);
    })
    dataset2 = dataset2 + "]";
    //console.log("domain data: " + datasettypes);
    //console.log("domains 3p plotting - 1");
    //console.log(dataset1);
    var ds = jQuery.parseJSON(dataset1);
    //console.log("domains 3p plotting - 2");
    //console.log(dataset2);
    var dst = jQuery.parseJSON(dataset2);
    plotChartPieDomains3P(ds);
    //console.log("domains 3p plotted 1");
    plotChartPieDomains3P2(dst);
    //console.log("domains 3p plotted 2");
    //plotChartPyramidDomains(ds);
}

function displayTableDomainsLocations() {
    var totaldomainrefs = 0;
    var domainpct = 0;
    var iV = 0;
    var sT = '';
    var tbl_head = "";
    var tbl_body = "";
    //console.log("domains table loc 4");
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Domain Name" + "</th>" + "<th>" + "Type" + "</th>" + "<th>" + "Network" + "</th>" + "<th>" + "Service" + "</th>" + "<th>" + "Origin Location" + "</th>" + "<th>" + "(Edge) Server Name" + "</th>" + "<th>" + "(Edge) Server Location" + "</th>" + "<th>" + "(Edge) Server IP" + "</th>" + "<th>" + "Latitude" + "</th>" + "<th>" + "Longitude" + "</th>" + "<th>" + "Distance (Miles)" + "</th>" + "<th>" + "Method" + "</th>";
    tbl_head = "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(DomainsList, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            if (k != 'Site Description' && k != 'Category' && k != 'Company' && k != 'Count' && k != 'Product' && k != 'Group' && k != 'TotBytes' && k != 'Offset') {
                if (v == "3P")
                    v = "Third Party";
                tbl_row += "<td>" + v + "</td>";
            }
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("domain row: " + tbl_row);
    })
    $("#domloc_table thead").html(tbl_head);
    $("#domloc_table tbody").html(tbl_body);

    //datatable initialisation
    var oTable = $("#domloc_table").dataTable({
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 25,
        "processing": true,
        "aaSorting": [[1, "asc"]], // sorts by a column and direction as set by the table type
        dom: 'Bfrtipl',
        buttons: [
            {
                extend: 'copyHtml5',
                title: 'Domains Locations'
            },
            {
                extend: 'excelHtml5',
                title: 'Domains Locations'
            },
            {
                extend: 'csvHtml5',
                title: 'Domains Locations'
            },
            {
                extend: 'pdfHtml5',
                title: 'Domains Locations'
            }
        ],
    });
}


function displayTableReverseIP() {
    var totaldomainrefs = 0;
    var domainpct = 0;
    var iV = 0;
    var sT = '';
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Domain" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(reverseipresults, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            //console.log("k: " + k + "; v = " + v);
            if (k == 'domain_count') {
                tbl_row = "<td>Total: " + v + "</td>";
                tbl_body += "<tr>" + tbl_row + "</tr>";
            }
            if (k == 'domains') {
                $.each(this, function (dk, dv) {
                    //console.log("k: " + k + "; v = " + v);
                    tbl_row = "<td>" + dv + "</td>";
                    tbl_body += "<tr>" + tbl_row + "</tr>";
                })
                //for (var i in doms) {
                //	tbl_row += "<td>"+doms[i]+"</td>";
                //}
            }
        });
        //console.log("domain row: " + tbl_row);
    })
    $("#site_table tbody").html(tbl_body);
}

function plotChartPieDomains(data) {
    $(function () {
        $('#container_dompie2').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Domain Shares by Domain<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y:.0f}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Domain share',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function plotChartPieDomains3P(data) {
    //console.log(data);
    $(function () {
        $('#container_3Pdompie').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Third Party Company Shares by Domain Requests<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y:.0f}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Domain share',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function plotChartPieDomains3P2(data) {
    $(function () {
        $('#container_3Pdompie2').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Third Party Company Shares by Number of Bytes<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y:.0f}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Domain share',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function plotChartPieDomainTypes(data) {
    $(function () {
        $('#container_dompie').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Domain Shares by Domain Type<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Domain share',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function plotChartPie3PDomains(data) {
    $(function () {
        $('#container_3ppie').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Third Party Requests by Company<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    size: 250,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y:.0f}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '3rd Party Provider share',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function plotChartPie3P2Domains(data) {
    $(function () {
        $('#container_3ppie2').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Third Party Requests by Category<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    size: 250,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y:.0f}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '3rd Party share',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function plotChartPie3P3Domains(data) {
    var chartDG = '';
    $(function () {
        chartDG = $('#container_3ppie3').highcharts({
            //Highcharts.chart('#container_3ppie3', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Third Party Requests by Group<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    size: 250,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y:.0f}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '3rd Party share',
                data: data,
                    point: {
                        events: {
                            click: function (event) {
                                console.log(
                                    this.name + ' clicked\n' +
                                    'segment: ' + this.x + " - " + this.y);

                                    // create pie chart dynamically for selected group
                                    createGroupCategoryPieChart(this.name);
                            }
                        }
                    }
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function createGroupCategoryPieChart(groupName)
{
    // append a div to place pie chart if not existing
    // destroy chart if present
    var $myDiv = $('#container_3ppie4');
    if ($myDiv.length)
    {
        // div exists
        $( "#container_3ppie4" ).remove();
    }

    // get data for group categories
    var new3pArrayGroupCategory = new Array();
    var new3pArrayGroupCategory = new Array();
    // work through third party domains, select those in group
    var len = DomainsList.length;
    $.each(DomainsList, function (noofitem) {
        var domainName = this['Domain Name'];
        var value = '';
        var cnt = 0;
        var indx = 0;
        var dtype = this['Domain Type']
        if (dtype == '3P' || dtype == 'CDN' || dtype == 'self-hosted' || (dtype == 'Shard' && domainName.indexOf('metrics.') != -1)) {
          //  noofUniqueThirdParties++; // accumulate number of unique third parties - domains
    //console.log(this['Company'] + " " + this['Category'] + " " + this['Count']);
            cnt = this['Count'];

            $cat =  this['Category'];

            // find group array
            value = this['Group'];
            if (value == '' || value === undefined || value == null)
            {
                value = "Other";
            }

            indx = -1;
            for (var i = 0; i < new3pArrayGroupCategory.length; i++) {
                if (new3pArrayGroupCategory[i][0] == $cat) {
                    indx = i;
                    break;
                }
            }
            // check group is the one we want
            if(value == groupName)
            {
                if (indx == -1) {
                    arr = new Array();
                    arr[0] = $cat;
                    arr[1] = cnt;
                    new3pArrayGroupCategory.push(arr);
                }
                else {
                    new3pArrayGroupCategory[indx][1] = new3pArrayGroupCategory[indx][1] + cnt;
                }
                
            } // end for this group
        }
    }) // end for each domainslist

    data = new3pArrayGroupCategory;

    // create new pie4 div after pie3
    $( '<div id="container_3ppie4">Test</div>' ).appendTo( "#HC3p" );

    // create pie chart
    var chartDG = '';
    $(function () {
        chartDG = $('#container_3ppie4').highcharts({
            //Highcharts.chart('#container_3ppie3', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Third Party ' + groupName + ' Group Requests by Category<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    size: 250,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y:.0f}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '3rd Party share',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });;

}
function plotChartWFDomains(data) {
    $(function () {
        $('#container').highcharts({
            chart: {
                type: 'waterfall'
            },
            title: {
                text: 'Highcharts Waterfall'
            },
            xAxis: {
                type: 'category'
            },
            yAxis: {
                title: {
                    text: ''
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: '<b>${point.y:,.2f}</b>'
            },
            series: [{
                upColor: Highcharts.getOptions().colors[2],
                color: Highcharts.getOptions().colors[3],
                data: data,
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        return Highcharts.numberFormat(this.y / 1000, 0, ',') + 'k';
                    },
                    style: {
                        color: '#FFFFFF',
                        fontWeight: 'bold',
                        textShadow: '0px 0px 3px black'
                    }
                },
                pointPadding: 0
            }]
        });
    });

}
function plotChartPyramidDomains(data) {
    $(function () {
        $('#container_dompyramid').highcharts({
            chart: {
                type: 'pyramid',
                marginRight: 100
            },
            title: {
                text: 'Domain Shares',
                x: -50
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b> ({point.y:,.0f}%)',
                        color: 'black',
                        softConnector: true
                    }
                }
            },
            legend: {
                enabled: false
            },
            series: [{
                name: 'Unique Domains',
                data: data
            }]
        });
    });
}
function plotChartPieOrderingHdrsb4(data) {
    $(function () {
        // Radialize the colors
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {
                    cx: 0.5,
                    cy: 0.3,
                    r: 0.7
                },
                stops: [
                    [0, color],
                    [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
                ]
            };
        });
        $('#hdrsb4_container').highcharts({
            colors: ['#FF0000', '#00FF00', '#FACC2E'],
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'CSS & JS placed in the HEAD<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Header Files',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}

function plotChartPieOrderingBodyb4(data) {
    $(function () {
        $('#bodyb4_container').highcharts({
            colors: ['#00FF00', '#FF0000'],
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'CSS & JS placed in the BODY<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Body Files',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function plotChartPieOrderingHdrs(data) {
    $(function () {
        $('#hdrs_container').highcharts({
            colors: ['#FF0000', '#00FF00', '#FACC2E'],
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'CSS & JS placed in the HEAD<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Header Files',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}

function plotChartPieOrderingBody(data) {
    $(function () {
        $('#body_container').highcharts({
            colors: ['#00FF00', '#FF0000'],
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'CSS & JS placed in the BODY<br/>' + SiteTitle
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    startAngle: 90,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.y}'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Body Files',
                data: data
            }],
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
        });
    });
}
function isHeaderATextFile(mimetype) {
    var boolTextType = false;
    switch (mimetype) {
        case "text/html":
            boolTextType = true;
            break;
        case "text/plain":
            boolTextType = true;
            break;
        case "text/css":
            boolTextType = true;
            break;
        case "application/javascript":
            boolTextType = true;
            break;
        case "application/x-javascript":
            boolTextType = true;
            break;
        case "text/javascript":
            boolTextType = true;
            break;
        case "text/x-js":
            boolTextType = true;
            break;
        case "text/xml":
            boolTextType = true;
            break;
        case "application/xml":
            boolTextType = true;
            break;
        case "application/json":
            boolTextType = true;
            break;
        case "image/svg+xml":
            boolTextType = true;
            break;
        case "font/woff":
        case "application/font-woff":
            boolTextType = true;
            break;
        default:
            boolTextType = false;
    }
    return boolTextType;
}

function analyseHeader(objID, headerStr, mimetype, IsRoot) {
    //console.log("arg objID = "+objID);
    //console.log("arg mimetype = "+mimetype);
    //console.log("arg headerStr = "+headerStr);
    // initialisations
    var hOut = '';
    var dateanalysis = 'General Settings:<ul><li class=\"success\">Server Time is synchronised</li></ul>';
    var ceanalysis = '';
    if (isHeaderATextFile(mimetype) == true) {
        ceanalysis = 'Content Size:<ul><li class=\"danger\">HTTP Compression (GZIP) is NOT enabled for text object</li></ul>';
    }
    else {
        ceanalysis = 'Content Size:<ul><li class=\"success\">HTTP Compression (GZIP) is NOT enabled for non-text object</li></ul>';
    }
    var ccanalysis = '';
    var clanalysis = '';
    var conanalysis = '';
    var expanalysis = '';
    var str = '';
    var etagstr = '';
    var hPragma = '';
    var hDate = '';
    var hContentEncoding = '';
    var hConnection = '';
    var hExpires = '';
    var hCacheControl = '';
    var hContentType = '';
    var hEtags = '';
    var respsonsedatetime = NewObj[Number(objID)]['response_datetime'];
    mimetype = NewObj[Number(objID)]['Mime type'];
    var status = ''; NewObj[Number(objID)]['HTTP status'];
    fsize = NewObj[Number(objID)]['Content size uncompressed'];
    etag = '';
    var boolFoundContentType = false;
    var boolFoundVary = false;
    //hOut += '<tr><td class=\"success\">+&#x2713;</td><td>A cache may not keep a cached copy of the object' +hdrfieldvalue[0] +": " + val + ')</td>';//
    //hOut += '<tr><td class=\"danger\">+&#x2716;</td><td>Invalid field specified (' + val + ')</td>';//
    //split arg headers
    headerStr = headerStr.replaceAll("<br />", "<br/>");
    headerfields = headerStr.split("<br/>");
    //console.log("REPLACED headerStr = "+headerStr);
    //console.log("noof headers="+headerfields.length);
    //console.log("headera: '"+headerfields+"'");
    for (var i = 0; i < headerfields.length; i++) {
        hdrfieldvalue = headerfields[i].split(": ");
        //document.write(headerfields[i] + "<br>");
        //document.write(hdrfieldvalue[0] + "<br>");
        //console.log("header: '"+ hdrfieldvalue[0]+"' = '"+ val+"'");
        var hdrstr = hdrfieldvalue[0].trim();
        var val = '';
        if (hdrfieldvalue[1] !== undefined)
            val = hdrfieldvalue[1].trim();
        else {
            //get status code and desc
            var httphdr = hdrstr.substr(0, 4);
            if (httphdr.toLowerCase() == 'http') {
                status = hdrstr.substr(9, 3);
                //console.log ("HTTP status code = " + status);
                sc = status;
            }
        }
        switch (hdrstr.toLowerCase()) {
            case "date":
                var daterqMS = new Date();
                daterqMS = Date.parse(val);
                var datenowMS = new Date(respsonsedatetime);
                var timediffMS = datenowMS - daterqMS;
                var timediffS = timediffMS / 1000;
                var timespan = convertSeconds(timediffS);
                if (timediffS > 5) // number of seconds to be considered out of accuracy
                {
                    hDate = '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Server time is NOT synchronised: time difference: ' + timediffS + ' seconds (' + timespan + ')</td></tr>';//
                }
                else {
                    hDate = '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>Server time is synchronised</td></tr>';//
                }
                break;
            case "server":
                server = val;
                //document.write("Server analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break;
            case "accept-ranges":
                //document.write("Accept-Ranges analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break;
            case "age":
                //document.write("Age analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break;
            case "content-length":
                //document.write("Content-Length analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                //document.write(val + " - " + contentlength + "<br>");
                //if (parseInt(val,10) == parseInt(contentlength,10))
                //{
                //	clanalysis = "<ul><li class=\"success\">Content length is correct</li></ul>";
                //}
                //else
                //{
                //	clanalysis = "<ul><li class=\"danger\">Content length is NOT correct</li></ul>";
                //}
                break;
            case "cteonnt-length":
                //document.write("Content-Length analysis" + "<br>");
                //document.write(val + "<br>");
                //document.write("Cteonnt-Length: " + val + " - Reverse Proxy modification detected<br>");
                break;
            case "etag":
                etag = val;
                //document.write("ETag analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break
            case "content-type":
                boolFoundContentType = true;
                //document.write("Content-Type analysis" + "<br>");
                //document.write(val + "<br>");
                switch (val) {
                    case "image/jpeg":
                        //document.write("JPEG image: " + val + "<br>");
                        break;
                    case "image/png":
                        //document.write("PNG image: " + val + "<br>");
                        break;
                    default:
                }

                break;
            case "vary":
                boolFoundVary = true;
                //document.write("Vary analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break;
            case "content-encoding":
                //document.write("Content-Encoding analysis" + "<br>");
                //document.write(val + "<br>");
                //console.log("checking content encoding: '" + val + "'");
                if (val == 'gzip') {
                    //console.log("checking content encoding gzip found: " + val);
                    var res = isHeaderATextFile(mimetype);
                    //console.log("checking content encoding for " + mimetype + " result: " + res);
                    if (res === true) {
                        //console.log("checking content encoding text: " + val);
                        hContentEncoding = '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>HTTP Compression (' + val + ') is enabled</td></tr>';//
                        //console.log("updating ceanalysis: " + ceanalysis);
                    }
                    else {
                        //console.log("checking content encoding not text: " + val);
                        hContentEncoding = '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>HTTP Compression (' + val + ') is enabled for non-text object</td></tr>';//
                    }
                }
                else {
                    //console.log("checking content encoding not recognised: " + val);
                }
                break;
            case "last-modified":
                lmd = val;
                //document.write("Last-Modified Date analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break;
            case "connection":
                //document.write("Connection analysis" + "<br>");
                //document.write(val + "<br>");
                if (val.toLowerCase() == 'keep-alive') {
                    hConnection = '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>Connections are kept-alive</td></tr>';//
                }
                if (val.toLowerCase() == 'close') {
                    hConnection = '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Connections are NOT kept-alive</td></tr>';//
                }
                break;
            case "keep-alive":
                //document.write("Keep-Alive analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break;
            case "cache-control":
                //analyse cache-control headers
                var cc = val.split(", ");
                var ccl = cc.length;
                //document.write("noof cc fields: " + ccl.toString() + "<br>");
                //document.write(cc[0] + "<br>");
                for (var j = 0; j < cc.length; j++) {
                    var ccd = cc[j].split("=");
                    switch (ccd[0]) {
                        case "public":
                            hCacheControl += '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>The object is cacheable by any cache (' + ccd[0] + ')</td></tr>';//
                            if (typeof (ccd[1]) != 'undefined') {
                                hCacheControl += '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Invalid field for directive ' + ccd[0] + '</td></tr>';//
                            }
                            break;
                        case "private":
                            hCacheControl += '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>The object is cacheable by any cache (' + ccd[0] + ')</td></tr>';//
                            if (typeof (ccd[1]) != 'undefined') {
                                hCacheControl += '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Invalid field for directive ' + ccd[0] + '</td></tr>';//
                            }
                            break;
                        case "max-age":
                            var timespan = convertSeconds(ccd[1]);
                            hCacheControl += '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>The object should be considered stale after a period of ' + ccd[1] + ' seconds (' + timespan + ') from the time of retrieval</td></tr>';//
                            break;
                        case "no-cache":
                            if (typeof (ccd[1]) != 'undefined') {
                                hCacheControl += '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>A cache may keep a cached copy of the object, except for the field(s) specified: ' + ccd[0] + ': ' + ccd[1] + ', but must always revalidate it before sending it back to the client</td></tr>';//
                            }
                            else {
                                hCacheControl += '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>A cache may keep a cached copy of the object but must always revalidate it before sending it back to the client</td></tr>';//
                            }
                            break;
                        case "no-store":
                            hCacheControl += '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>This response cannot be stored by a cache (' + ccd[0] + ')</td></tr>';//
                            if (typeof (ccd[1]) != 'undefined') {
                                hCacheControl += '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Invalid field for directive ' + ccd[0] + '</td></tr>';//
                            }
                            break;
                        case "must-revalidate":
                            hCacheControl += '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>The cache must verify the status of stale objects</td></tr>';//
                            if (typeof (ccd[1]) != 'undefined') {
                                hCacheControl += '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Invalid field for directive ' + ccd[0] + '</td></tr>';//
                            }
                            break;
                        default:
                    }
                }
                break;
            case "expires":
                //document.write("Expires analysis" + "<br>");
                //document.write(val + "<br>");
                var daterqMS = new Date();
                daterqMS = Date.parse(val);
                var datenowMS = Date.parse(respsonsedatetime);
                var timediffMS = '';
                if (daterqMS > datenowMS) {
                    timediffMS = daterqMS - datenowMS;
                    var timediffS = timediffMS / 1000;
                    var timespan = convertSeconds(timediffS);
                }
                if (val == "-1") {
                    hExpires = '<tr class=\"warning\"><td>' + hdrstr + '</td><td>!</td><td>Object has an invalid expiry date (' + val + ') and will be interpreted as a date in the past; this object will not be cached</td></tr>';//
                }
                else {
                    // is a date
                    if (datenowMS >= daterqMS) {
                        hExpires = '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>Object is set to expire in the past; this object will not be cached</td></tr>';//
                    }
                    else {
                        hExpires = '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>Object is set to expire in the future: ' + timediffS + ' seconds (' + timespan + ')</td></tr>';//
                    }
                }
                break;
            case "pragma":
                //document.write("Pragma analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                if (val.toLowerCase() === 'no-cache') {
                    hPragma = '<tr class=\"success\"><td>' + hdrstr + '</td><td class="pass">&#x2714;</td><td>A cache may not keep a cached copy of the object' + hdrfieldvalue[0] + ": " + val + ')</td></tr>';//
                }
                else {
                    hPragma = '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Invalid field specified (' + val + ')</td></tr>';//
                }
                // pragma analysis not added to display - old
                break;
            case "Content-Language":
                //document.write("Content-Language analysis" + "<br>");
                //document.write(val + "<br>");
                //execute code block 2
                break;
            default:
            //code to be executed if n is different from any case
            //document.write("unknown field: " + headerfields[i] + "<br>");
        }
    }
    if (etag != '') {
        var etaganalysis = [];
        etaganalysis = validateEtag(server, etag, fsize, lmd);
        if (etaganalysis[0] == false) {
            etagstr = 'ETag';
            hEtags = '<tr class=\"danger\"><td>' + etagstr + '</td><td class="fail">&#x2716;</td><td>ETag is invalid</td></tr>';
        }
        else {
            etagstr = 'ETag';
            hEtags = '<tr class=\"success\"><td>' + etagstr + '</td><td class="pass">&#x2714;</td><td>ETag is valid</td></tr>';
        }
    }
    else {
        hEtags = '';
    }
    // checks for missing headers
    //console.log('status = ' + status);
    if (boolFoundContentType == false && status == 200) {
        hdrstr = 'Content Type';
        hContentType = '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail" class="fail">&#x2716;</td><td>Header is missing</td></tr>';
    }
    if (boolFoundVary == false && status == 304) {
        hdrstr = 'Vary';
        hContentType = '<tr class=\"danger\"><td>' + hdrstr + '</td><td class="fail">&#x2716;</td><td>Header is missing</td></tr>';
    }
    // checks for mulitple headers

    // Output header analysis
    if (headerfields.length > 1) {
        // 1st table, 2 column
        hOut = '<table class="table" border="0">';
        hOut += hDate + hContentEncoding + hContentType + hCacheControl + hExpires + hConnection + hEtags; // hPragma
        hOut += '</table><br/>';
    }
    else {
        hOut = '';
    }
    return (hOut);
}
String.prototype.replaceAll = function (token, newToken, ignoreCase) {
    var _token;
    var str = this + "";
    var i = -1;
    if (typeof token === "string") {
        if (ignoreCase) {
            _token = token.toLowerCase();
            while ((
                i = str.toLowerCase().indexOf(
                    token, i >= 0 ? i + newToken.length : 0
                )) !== -1
            ) {
                str = str.substring(0, i) +
                    newToken +
                    str.substring(i + token.length);
            }
        } else {
            return this.split(token).join(newToken);
        }
    }
    return str;
}
function convertSeconds(seconds) {
    var sec_num = parseInt(seconds, 10); // don't forget the second param
    var numdays = Math.floor(sec_num / 86400);
    var numhours = Math.floor((sec_num % 86400) / 3600);
    var numminutes = Math.floor(((sec_num % 86400) % 3600) / 60);
    var numseconds = ((seconds % 86400) % 3600) % 60;
    if (numhours < 10) { numhours = "0" + numhours; }
    if (numminutes < 10) { numminutes = "0" + numminutes; }
    if (numseconds < 10) { numseconds = "0" + numseconds; }
    return numdays + " days " + numhours + " hours " + numminutes + " minutes " + numseconds + " seconds";
}
function validateEtag(server, etag, fsize, lmd) {
    var returnedArray = [];
    var value1 = '';
    var value2 = '';
    var validinvalid = true;
    var etagparts = etag.split("-");
    var cntetagparts = etagparts.length;
    if (etag.substr(0, 2) == 'W/') {
        value1 = 'Weak ETag Validator';
    }
    else {
        value1 = 'Strong ETag Validator';
    }
    //APACHE
    var epochlmd = Date.parse(lmd);
    //console.log("apache lmd =" +lmd);
    //console.log("apache epoch lmd = " + epochlmd);
    var apachehexepochlmd = epochlmd.toString(16);
    //console.log("apache hex epoch lmd = " + apachehexepochlmd);
    var epochlmdstr = epochlmd.toString();
    var apacheepochlmd = padDigits(epochlmdstr, 16);
    //console.log("apache epoch lmd padded = " + apacheepochlmd);
    //console.log("apache hex etag = " + etagparts[1]);
    var lmdnumber = parseInt(etagparts[1], 16);
    //console.log("apache epoch etag date = " + lmdnumber);
    // Apache format: FileETag INode MTime Size - any of these are optional
    var hexString = ''
    if(fsize)
        fsize.toString(16);
    else
        fsize = 0;
    //console.log("apache hex fsize = " + hexString);
    var secondsdiff = lmdnumber - Number(apacheepochlmd);
    //console.log("secondsdiff = " + secondsdiff);
    if (secondsdiff > 1000000)
        validinvalid = false;
    //if(hexString != etagparts[0])
    //	 validinvalid = false;

    //NGINX

    //IIS

    if (etag == '""')
        validinvalid = false;





    returnedArray.push(validinvalid);
    returnedArray.push(value1);
    returnedArray.push(value2);
    return returnedArray;
}
function padDigits(number, digits) {
    return number + Array(Math.max(digits - String(number).length + 1, 0)).join(0);
}


function DisplayCacheHeaderDates() {
    // CacheAnalysis
    // data by URL in JSON format
    //console.log(JSON.stringify(CacheAnalysis));

    var htmlcolumncount = 0;
    //1  work out how many columns are required
    var expperiods = [];
    $.each(CacheAnalysis, function (row, rowdata) {
        //o = this['Object source'];
        //if(o == objSrcURL)
        //i = Number(this['id']);
        //console.log("obj key: " + row);

        $.each(rowdata, function (key, val) {
            //console.log(": " + key + " = " + val);
            if (key == "DiffDays") {
                // save expiry period to new array
                expperiods[expperiods.length] = val;
                //console.log("adding exp period: " + val);
            } // end if
        }); // end for each value object in a row
    });	// end for each row object
    // remove duplicates
    expperiods = removeDuplicates(expperiods);
    // sort array
    expperiods.sort(function (a, b) { return a - b });
    //console.log("list of exp periods");
    for (var i = 0; i < expperiods.length; i++) {
        //console.log("exp period: " + expperiods[i]);
    }
    //console.log('noof exp periods: ' + expperiods.length)
    var e = CacheAnalysis.length;
    var arrExpiryDays = new Array(e);
    for (i = 0; i < e; i++)
        arrExpiryDays[i] = new Array(e);

    $.each(CacheAnalysis, function () {
        var o = this['ObjectURL'];
        var filename = getFileName(o);
        var m = this['MimeType'];
        var b = this['BytesTransmitted'];
        var lmd = this['LastModDate'];
        var lmdiff = this['LastModDateDiffDays'];
        var exd = this['ExpiresDate'];
        var exdiff = this['DiffDays'];
        var cl = 'pass';
        var c = expperiods.indexOf(exdiff);
        //console.log(o + ' to be added to ' + c);
        var x = 0;
        // add values to an array based on columns and the next free row
        for (x = 0; x < e; x++) {
            //console.log('checking ' + arrExpiryDays[c][x]);
            if (arrExpiryDays[c][x] === undefined) {
                if (Math.abs(lmdiff) < Math.abs(exdiff)) {
                    cl = 'pass';
                }
                else {
                    cl = 'fail';
                }
                arrExpiryDays[c][x] = '<td class=\"' + cl + '\">File: ' + filename + '</br>Mime Type: ' + m + '<br/>' + b + '  bytes<br/>Days since modified: ' + lmdiff + '<br/>' + '</td>';
                //console.log('adding x =  ' + x + arrExpiryDays[c][x]);
                break;
            }
            else {
                //console.log('already have c = ' + c + '; x = ' + x + arrExpiryDays[c][x]);
            }
        }

    });	// end for each row object

    // copy data to a table
    var y = 0;
    var trow = '';
    var tbl_body = '';
    var tbl_row = ''
    for (x = 0; x < expperiods.length; x++) {
        tbl_row = tbl_row + '<th>' + expperiods[x] + ' days to Expiry</th>';  // change this for the number of columns from expperiods array
    }
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    //console.log("cache table output by row");
    // output to html row
    for (y = 0; y < e; y++) {
        for (x = 0; x < e; x++) {
            if (arrExpiryDays[x][y] !== undefined) {
                trow = trow + arrExpiryDays[x][y];
            }
            else {
                if (x < expperiods.length) {
                    trow = trow + '<td class=\"none\">' + '' + '</td>';
                }
            }

        } // end inner loop x
        //console.log("row x = " + x + "; " +trow);
        tbl_body = tbl_body + '<tr>' + trow + '</tr>';
        trow = '';
    } // end outer loop y
    $("#cacheExpLmd_table tbody").html(tbl_body);
    //console.log("cache table output");
    //console.log(tbl_body);

}

function removeDuplicates(target_array) {
    target_array.sort();
    var i = 0;
    while (i < target_array.length) {
        if (target_array[i] === target_array[i + 1]) {
            target_array.splice(i + 1, 1);
        }
        else {
            i += 1;
        }
    }
    return target_array;
}

function displayTableCacheAnalysis() {
    var tbl_body = "";
    //var tob, tzb, tsb, tsp = 0;
    tbl_row = "<th>" + "Filename" + "</th>" + "<th>" + "Mime Type" + "</th>" + "<th>" + "Bytes Transmitted" + "</th>" + "<th>" + "Last Mod Date" + "</th>" + "<th>" + "Last Mod Days" + "</th>" + "<th>" + "Expires Date" + "</th>" + "<th>" + "Exp Days" + "</th>" + "<th>" + "MaxAge Days" + "</th>" + "<th>" + "Cache Method" + "</th>" + "<th>" + "Cache Days" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    $.each(CacheAnalysis, function () {
        var tbl_row = "";
        $.each(this, function (k, v) {
            if (k == "ObjectURL") {
                var fn = getFileName(v);
                tbl_row += "<td>" + fn + "</td>";
            }
            else {
                tbl_row += "<td>" + v + "</td>";
            }
        })
        tbl_body += "<tr>" + tbl_row + "</tr>";
        //console.log("Error table row: " + tbl_row);
    })
    $("#cache_tables tbody").html(tbl_body);

    if (CacheAnalysis.length == 0) {
        // remove tab and content
        document.getElementById("tabcacheexp").remove();
        document.getElementById("tab_cacheanalysis").remove();
    }
}
function displayChartCacheAnalysis() {
    $(function () {
        $('#cacheAnalysisContainer').highcharts({
            chart: {
                type: 'bar',
                height: 800
            },
            title: {
                text: 'Cache Analysis Report'
            },
            legend: {
                enabled: false
            },
            subtitle: {
                text: 'Based on Object'
            },
            xAxis: [{
                categories: CacheAnalysisBarStackChart.categories,
                reversed: false,
                labels: { step: 1 }
            }, { // mirror axis on right side
                opposite: true,
                reversed: false,
                categories: CacheAnalysisBarStackChart.categoriesMimeType,
                linkedTo: 0,
                labels: { step: 1 }
            }],
            yAxis: {
                title: {
                    text: null
                },
                labels: {
                    formatter: function () {
                        return this.value + ' D';
                    }
                },
                min: CacheAnalysisBarStackChart.xAxisMin,
                max: CacheAnalysisBarStackChart.xAxisMax
            },
            plotOptions: {
                series: { stacking: 'normal' }
            },
            tooltip: {
                formatter: function () {
                    return '<b>' + this.series.name + '</b><br/>URL: <b>' + this.point.category + '</b><br/>' +
                        'Days: <b>' + this.point.y + '</b>';
                }
            },
            credits: {
                text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
                href: analysisURL
            },
            series: [{
                name: 'Modified days before',
                data: CacheAnalysisBarStackChart.pastValues
            }, {
                name: 'Will expire in number of days',
                data: CacheAnalysisBarStackChart.exipryValues
            }]
        });
    });
}

function formatXml(xml) {
    var formatted = '';
    var reg = /(>)(<)(\/*)/g;     ///////////////////////////////////////////////////////////////////
    xml = xml.replace(reg, '$1\r\n$2$3');
    var pad = 0;
    jQuery.each(xml.split('\r\n'), function (index, node) {
        var indent = 0;
        if (node.match(/.+<\/\w[^>]*>$/)) {
            indent = 0;
        } else if (node.match(/^<\/\w/)) {
            if (pad != 0) {
                pad -= 1;
            }
        } else if (node.match(/^<\w[^>]*[^\/]>.*$/)) {
            indent = 1;
        } else {
            indent = 0;
        }
        var padding = '';
        for (var i = 0; i < pad; i++) {
            padding += '  ';
        }
        formatted += padding + node + '\r\n';
        pad += indent;
    });
    return formatted;
}
function gen_thumbnails() {
    $('#genthumbnails').click(function () {
        //alert('generating thumbnails');
        var TableData = new Array();
        //alert( oTable.rows('.selected').data().length +' row(s) selected' );
        $.each(NewObj, function () {
            var tbl_row = "";
            $.each(this, function (k, v) {
                if (k == 'id') {
                    // get response date time for this object now as it is last on the array
                    var respsonsedatetime = NewObj[Number(v)]['response_datetime'];
                    var id = NewObj[Number(v)]['id'];
                    var objsource = NewObj[Number(v)]['Object source'];
                    var objfile = NewObj[Number(v)]['Object file'];
                    var mimetype = NewObj[Number(v)]['Mime type'];
                    var objtype = NewObj[Number(v)]['Object type'];
                    //console.log("object found: " + id +": " + objsource + ': ' + respsonsedatetime);
                    if (objtype == 'Image') {
                        TableData.push(
                            {
                                "ObjNo": id
                                , "url": objsource
                                , "localfile": objfile
                                , "mimetype": mimetype
                                , "savepath": savedir
                            });
                    }
                    return false;
                }
            }); // end for each attribute
        }); // end for each object
        var d = JSON.stringify(TableData);
        //console.log(d);

        $.ajax({
            url: '/toaster/gen_thumbnails.php',
            beforeSend: function () {
                $('#tab_imageoptimisation').addClass('wait');
            },
            type: 'POST',
            data: { 'ids': d },
            dataType: 'json',
            success: function (response) {
                //console.log("AJAX request was successful:" + response.responseText);
                $('#tab_imageoptimisation').removeClass('wait');
                addThumbnails("#optJPGimages_table");
                addThumbnails("#optPNGimages_table");
                addThumbnails("#optGIFimages_table");
                addThumbnails("#optWEBPimages_table");
                addThumbnails("#optBMPimages_table");
                addThumbnails("#optGIDanimations_table");
            },
            error: function (response) {
                //console.log("AJAX request was a failure: " + response.responseText);
                $('#tab_imageoptimisation').removeClass('wait');
            }
        });

    });
}

function displayTableCssSelectors() {
    var tbl_row = "";
    var tblall_row = "";
    var filecounter = 0;
    var fileselectorcounterUsed = 0;
    var fileselectorcounterUnused = 0;
    var lastfile = '';
    var tbl_body = '';
    var tblall_body = '';
    var fileselectorTotal = 0;
    var fileselectorPct = 0;
    var pf = '';
    tbl_row = "<th>" + "Stylesheet" + "</th>" + "<th>" + "No. of Used Selectors" + "</th>" + "<th>" + "No. of Unused Selectors" + "</th>" + "<th>" + "Percentage of Selectors Used" + "</th>";
    tbl_body += "<tr class=\"header\">" + tbl_row + "</tr>";
    tblall_row = "<th>" + "Stylesheet" + "</th>" + "<th>" + "Selector Type" + "</th>" + "<th>" + "Selector Name" + "</th>" + "<th>" + "Used?" + "</th>";
    tblall_body += "<tr class=\"header\">" + tblall_row + "</tr>";
    tblall_row = '';
//console.log(CSSselectors);
    var count = Object.keys(CSSselectors).length
//console.log("size", count);
    if(count < 90)
        return false;
    $.each(CSSselectors, function () {
        // for each selector
        $.each(this, function (k, v) {
            //console.log(k + ' = ' + v);
            //"CSS filename" => $cssfile,
            //"Selector type" => $type,
            //"Selector name" => $selector,
            //"Used in HTML" => $usedInHTML
            if (k == "CSS filename") {
                // check for new file
                if (v != lastfile) {
                    // new css file in list
                    //console.log('new css file found: ' + v);
                    // close row for previous
                    if (lastfile != '') {
                        fileselectorTotal = fileselectorcounterUsed + fileselectorcounterUnused;
                        fileselectorPct = Math.floor(fileselectorcounterUsed / fileselectorTotal * 100);
                        tbl_row = '<td class=\"\">' + lastfile + '</td>' + '<td class=\"pass\">' + fileselectorcounterUsed + '</td>' + '<td class=\"fail\">' + fileselectorcounterUnused + '</td>' + '<td class=\"\">' + fileselectorPct + '%</td>';
                        tbl_body += "<tr>" + tbl_row + "</tr>";
                        //console.log('adding row for ' + v);
                        lastfile = v;
                        fileselectorcounterUsed = 0;
                        fileselectorcounterUnused = 0;
                    }
                    lastfile = v;
                }
            }
            // table all
            tblall_row += '<td class=\"\">' + v + '</td>';

            if (k == "Used in HTML") {
                if (v == 'yes') {
                    fileselectorcounterUsed += 1;
                    pf = 'success';
                }
                else {
                    fileselectorcounterUnused += 1;
                    pf = 'danger';
                }
                tblall_body += "<tr class=\"" + pf + "\">>" + tblall_row + "</tr>";
                tblall_row = '';
            }

        });

    });
    //console.log('end of css selector processing');
    fileselectorTotal = fileselectorcounterUsed + fileselectorcounterUnused;
    fileselectorPct = Math.floor(fileselectorcounterUsed / fileselectorTotal * 100);
    tbl_row = '<td class=\"\">' + lastfile + '</td>' + '<td class=\"pass\">' + fileselectorcounterUsed + '</td>' + '<td class=\"fail\">' + fileselectorcounterUnused + '</td>' + '<td class=\"\">' + fileselectorPct + '%</td>';
    tbl_body += "<tr>" + tbl_row + "</tr>";
    //console.log('css selector usage body: ' + tbl_body);
    $("#cssusage_table tbody").html(tbl_body);
    $("#cssusageall_table tbody").html(tblall_body);
}

function displayFonts() {
    var chars = "";
    for (var i = 32; i <= 127; i++) {
        chars += String.fromCharCode(i);
    }

    //  var fonthtml = fontdef1 + fontdef2 + fontfamily1 + fontfamily2 + fonthtml1 + fonthtml2;
    //  $("#fon").html(fonthtml);
    var allfonthtml = '';
    var allfontstyles = '';
    var fonthtml = '';
    var fontdef = '';
    var fontfamily = '';
    var fonthtml = '';
    var objid = '';
    var noofFonts = 0;
    var fontname = '';

    $.each(NewObj, function () {
        objtype = '';
        id = '';
        httpstatus = '';
        objsource = '';
        imgdimensions = '';
        $.each(this, function (k, v) {
            if (k == 'id') {
                id = v;
                objsource = NewObj[Number(v)]['Object source'];
                objfile = NewObj[Number(v)]['Object file'];
                objtype = NewObj[Number(v)]['Object type'];
                fontname = NewObj[Number(v)]['Font name'];
                if (objtype == 'Font') {
                    noofFonts += 1;
                    var filename = objfile.replace(/^.*[\\\/]/, '')
                    var name_with_ext = objfile.split('\\').pop().split('/').pop();
                    var name_without_ext = name_with_ext.substring(name_with_ext.lastIndexOf("/") + 1, name_with_ext.lastIndexOf("."));
                    var lc = jssavedir.slice(-1);
                    var objLocFileurl = objfile;
                    var windir = objLocFileurl.substr(1, 1);
                    if (windir == ':') {
                        objLocFileurl = objLocFileurl.substr(2); // strip the windows drive from the beginning
                        objLocFileCnv = objLocFileurl.replace(/[/\\*]/g, '\/');
                        //console.log("WIN objLocFile="+ objLocFile +"; objLocFileCnv="+ objLocFileCnv);
                    }
                    else { // linux
                        if(objLocFile.indexOf("/usr/share") !== -1)
                        { // local
                        objLocFileurl = objLocFile.substr(10); // strip the /usr/share from the front
                        objLocFileCnv = objLocFileurl.replace(/[/\\*]/g, '\/');
                        }
                        else // webpagetoaster.com
                            objLocFileCnv = objLocFile;
                    }

                    //console.log("objLocFileconv= " +objLocFileCnv);
                    objLocFileCnv.trim();
                    if (lc != "/")
                        jssavedir = jssavedir + '/';

                    // don't add known iconfonts to the view fonts list
                    if(filename.indexOf("icon") != -1 || filename.indexOf("fontawesome") != -1)
                    {  
                        var ext = filename.substr(filename.lastIndexOf('.') + 1);
                        switch (ext)
                        {
                            case "woff":
                                var jsondata = getWoffFontDetails(objLocFileCnv);
//console.log(jsondata);
                                fontname = jsondata.fontname;
                                NewObj[Number(v)]['Font name'] = fontname;
                                break;
                        }
                     }
                     else
                     {
//console.log("font: " + name_with_ext + " " + fontname);
                         if(fontname == '')
                            fontname = name_with_ext;
                        else
                        fontname = fontname + " (" + name_with_ext + ")";
                        fontdef = '<style type="text/css">@font-face {font-family: "' + name_with_ext + '";src: local("' + name_with_ext + '"), url("' + objLocFileCnv + '") format("opentype");}';
                        fontfamily = '.f' + id + ' {font-family: "' + name_with_ext   + '";font-size: 36px;}</style>';
                        if(filename.indexOf("icon") != -1 || filename.indexOf("fontawesome") != -1)
                            fonthtml = fontname + '<br/><p class="f' + id + '">' + chars + '</p><hr>';
                        else
                            fonthtml = '<p class="f' + id + '">' + fontname + '<br/>' + chars + '</p><hr>';
                        allfonthtml += fonthtml;
                        allfontstyles += fontdef + fontfamily;
                     }
                } // end font
            }// end if id
        });
    }); // end for each object

    // add to font tab
    $("#fontdefs").html(allfontstyles);

    $('#ViewFonts').click(function () {
        boolShowFonts = false;
        $("#fontdisplay").html(allfonthtml);
    }); // end function to view images

    $('#HideFonts').click(function () {
        boolShowFonts = false;
        $("#fontdisplay").html('');
    }); // end function to view images

    // hide tab if there are no custom fomts
    if (noofFonts == 0) {
        // remove tab and content
        document.getElementById("tabFonts").remove();
        document.getElementById("tab_fonts").remove();
    }
} // end function displayFonts
function displayMaturityScore(browserenginever) {
    var metric1Total = 0;
    var metric2Total = 0;
    var metric3Total = 0;
    var metric4Total = 0;
    var metric5Total = 0;
    var metric6Total = 0;
    var metric7Total = 0;
    var metric8Total = 0;
    var metric9Total = 0;
    var metric10Total = 0;
    var metric11Total = 0;
    var cacheCount = 0;
    var cacheDaysTotal = 0;
    var metric1Score = 0;
    var metric2Score = 0;
    var metric3Score = 0;
    var metric4Score = 0;
    var metric5Score = 0;
    var metric6Score = 0;
    var metric7Score = 0;
    var metric8Score = 0;
    var metric9Score = 0;
    var metric10Score = 0;
    var metric11Score = 0;
    // vary weightings by desktop or mobile - mobile js, fonts higher weight
    var metric1Weight = 1; // mobile 1.2
    var metric2Weight = 1;
    var metric3Weight = 1;
    var metric4Weight = 1;
    var metric5Weight = 1; // mobile 1.2
    var metric6Weight = 1; // mobile 1.2
    var metric7Weight = 1;
    var metric8Weight = 1;  // mobile 1.2
    var metric9Weight = 1;  // mobile 1.2
    var metric10Weight = 1;
    var metric11Weight = 1;
    var maturityIndex = 0;
    var siteurl = '';
    $.each(NewObj, function () {
        objtype = '';
        id = '';
        httpstatus = '';
        objsource = '';
        var offsetDuration = 0;
        var ttfbMS = 0;
        var downloadDuration = 0;
        var allMS = 0;

        $.each(this, function (k, v) {
            if (k == 'id') {
                id = v;
                objsource = NewObj[Number(v)]['Object source'];
                objfile = NewObj[Number(v)]['Object file'];
                objtype = NewObj[Number(v)]['Object type'];
                var domref = NewObj[Number(v)]['Domain ref'];
                var http_status = NewObj[Number(v)]['HTTP status'];
                offsetDuration = NewObj[Number(v)]['offsetDuration'];
                ttfbMS = NewObj[Number(v)]['ttfbMS'];
                downloadDuration = NewObj[Number(v)]['downloadDuration'];
                allMS = NewObj[Number(v)]['allMS'];
                allStartMS = NewObj[Number(v)]['allStartMS'];
                allEndMS = NewObj[Number(v)]['allEndMS'];
                cacheSeconds = NewObj[Number(v)]['cacheSeconds'];
                //console.log(objsource + ": timings: offset=" + offsetDuration + "; allMS=" + allMS);
                // metric 1 = total JS Bytes, metric6 = javascript  efore render start
                if (objtype == "JavaScript") {
                    if (parseInt(NewObj[Number(v)]['Content length transmitted']))
                        metric1Total += parseInt(NewObj[Number(v)]['Content length transmitted']);
                    //console.log(objsource + "; js size = " + NewObj[Number(v)]['Content size uncompressed'] + "; total = " + metric1Total);
                    if (allEndMS < renderStartMS) {
                        //console.log("m1 add: " + objsource + "; allendMS: " + allEndMS + " (rs = " + renderStartMS + ")");
                        metric6Total++;
                    }
                    else {
                        //console.log("m1 miss " + objsource + "; allendMS: " + allEndMS + " (rs = " + renderStartMS + ")");
                    }
                }
                // metric 2 = total CSS bytes, metric3 = css count
                if (objtype == "StyleSheet") {
                    if (parseInt(NewObj[Number(v)]['Content length transmitted']))
                        metric2Total += NewObj[Number(v)]['Content length transmitted'];
                    metric3Total++;
                }
                // metric 4- image bytes
                if (objtype == "Image" && NewObj[Number(v)]['Content length transmitted'] > 50) {
                    if (parseInt(NewObj[Number(v)]['Content length transmitted']))
                        metric4Total += NewObj[Number(v)]['Content length transmitted'];
                }
                //compare metrics to renderStartMS
                //metric5 = third party before render start, 
                if (domref == "3P") {
                    if (allEndMS < renderStartMS) {
                        metric5Total++;
                    }
                }
                // //metric11 = median cache lifetime
                // if(domref == "Primary")
                // {
                //     if( cacheSeconds > 0)
                //     {
                //         cacheCount++;
                //         cacheDaysTotal += cacheSeconds / 86400;
                //     }
                // }
                // metric8 = webfont count, metric 9 = webfont bytes before DOM Complete
                if (objtype == "Font") {
                    metric8Total++;
                    if (allEndMS < DOMCompleteMS) {
                        metric9Total++;
                    }
                }
                //console.log("obj: " + objsource + "; time: '" + allMS + "'");
                //metric 10 = html time to complete
                if (objtype == "HTML" && (domref == "Primary" || domref == "Shard" || domref == "CDN") && http_status == 200) {
                    //console.log("HTML: " + objsource + "; time: '" + allMS + "'");
                    // only get first HTML
                    if (metric10Total == 0) {
                        if (allMS) {
                            metric10Total = allMS;
                            siteurl = objsource;
                        }
                    }
                    else {
                        // ignore
                    }
                }
            }
        }); // end each value within the object
    }); // end each object
    // third party counts
    //metric 7 = 3P count
    metric7Total = noofUniqueThirdParties; // accumulated in displayTableDomains3P()
    // convert cache values
    // if(cacheDaysTotal> 0)
    //     metric11Total = cacheDaysTotal / cacheCount;
    // else
    //     metric11Total = 0;
    // display raw values in debug
    $("#MatScoreDebug").append(("Maturity Values") + "<br/>");
    $("#MatScoreDebug").append(("Metric 1: JS Bytes: " + metric1Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 2: CSS Bytes: " + metric2Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 3: CSS Count: " + metric3Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 4: Image Bytes: " + metric4Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 5: 3P Count < RS: " + metric5Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 6: JS Count < RS: " + metric6Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 7: 3P Count: " + metric7Total.toString() + "<br/>"));
    //$( "#MatScoreDebug" ).append( ( "Metric 7: Med Cache: " + metric7Total.toString()+ "<br/>" ) );
    $("#MatScoreDebug").append(("Metric 8: Font Count: " + metric8Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 9: Font Bytes < DC: " + metric9Total.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 10: HTML Time: " + metric10Total.toString() + "<br/>"));
    // calculate metric scores based upon weightings
    metric1Score = CalculateScore(metric1Total, 100000, 200000, 500000, true, metric1Weight); // js bytes
    metric2Score = CalculateScore(metric2Total, 20000, 100000, 250000, true, metric2Weight); // css bytes
    metric3Score = CalculateScore(metric3Total, 2, 4, 8, true, metric3Weight); // css count
    metric4Score = CalculateScore(metric4Total, 250000, 750000, 1500000, true, metric4Weight); // images
    metric5Score = CalculateScore(metric5Total, 0, 3, 10, true, metric5Weight); // 3p < render
    metric6Score = CalculateScore(metric6Total, 0, 3, 10, true, metric6Weight);  // js < render
    metric7Score = CalculateScore(metric7Total, 5, 10, 25, true, metric7Weight); // 3p
    //metric7Score = CalculateScore(metric7Total,7,4,1,false,metric7Weight); // cache excluded
    metric8Score = CalculateScore(metric8Total, 1, 2, 4, true, metric8Weight);  // fonts
    metric9Score = CalculateScore(metric9Total, 0, 50000, 150000, true, metric9Weight); // font bytes < domcomplete 
    metric10Score = CalculateScore(metric10Total, 100, 500, 1000, true, metric10Weight); // html time ms
    $("#MatScoreDebug").append(("<br/>" + "Maturity Scores") + "<br/>");
    $("#MatScoreDebug").append(("Metric 1: JS Bytes: " + metric1Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 2: CSS Bytes: " + metric2Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 3: CSS Count: " + metric3Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 4: Image Bytes: " + metric4Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 5: 3P Count < RS: " + metric5Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 6: JS Count < RS: " + metric6Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 7: 3P Count: " + metric7Score.toString() + "<br/>"));
    // $( "#MatScoreDebug" ).append( ( "Metric 7: Med Cache: " + metric7Score.toString()+ "<br/>" ) );
    $("#MatScoreDebug").append(("Metric 8: Font Count: " + metric8Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 9: Font Bytes < DC: " + metric9Score.toString() + "<br/>"));
    $("#MatScoreDebug").append(("Metric 10: HTML Time: " + metric10Score.toString() + "<br/>"));
    var totalItemMaturityScore = metric1Score + metric2Score + metric3Score + metric4Score + metric5Score + metric6Score + metric7Score + metric8Score + metric9Score + metric10Score;
    var maxTotalMaturityScore = 2 * (metric1Weight + metric2Weight + metric3Weight + metric4Weight + metric5Weight + metric6Weight + metric7Weight + metric8Weight + metric9Weight + metric10Weight);
    maturityIndex = 100 * (totalItemMaturityScore / maxTotalMaturityScore);
    maturityIndex = maturityIndex.toFixed(0);
    $("#MatScoreDebug").append(("<br/>" + "totalItemMaturityScore: " + totalItemMaturityScore.toString() + "<br/>"));
    $("#MatScoreDebug").append(("maxTotalMaturityScore: " + maxTotalMaturityScore.toString() + "<br/>"));
    //$( "#MatScoreDebug" ).append( ( "<br/>" + "Maturity Index: " + maturityIndex.toString()+ "<br/>" ) );
    // display
    var spanstat = '';
    var spantype = '';
    var spantxt = '';
    var listyle = 'info'; // default blue info colour
    spanstat = '<span class=\"msstatlarge\">' + maturityIndex + '</span>';
    spantype = '<span class=\"fltype\">' + "" + '</span>';
    spantxt = '<span class=\"fltxt\">' + "Maturity Score" + '</span>';
    var list_body = '<ul id=\"matscorelist\">';
    list_body += '<li class="statslist' + listyle + '">' + spanstat + spantype + spantxt + '</li>';
    list_body += '</ul>';
    $("#MatScore").html(list_body);
    //console.log("matscore posting url: " + siteurl);
    // send result to database - webpagetest only
    if (browserenginever == "WebpageTest") {
        var myData = { "url": encodeURI(siteurl), "score": maturityIndex };
        //  //call your .php script in the background, 
        //  //when it returns it will call the success function if the request was successful or 
        //  //the error one if there was an issue (like a 404, 500 or any other error status)
        $.ajax({
            url: "http://localhost/toaster/xhr_PostMaturityScore.php",
            type: "POST",
            data: myData,
            success: function (data, status, xhr) {
                //if success then just output the text to the status div then clear the form inputs to prepare for new data
                // $("#status_text").html(data);
                // $('#ID').val();
                console.log("score posted for " + siteurl);
            }
        });
    } // end post score for webpagetest tests
}  // end function displayMaturityScore
function CalculateScore(value, goodThreshold, badThreshold, awfulThreshold, isLowBetter, weight) {
    var t = 0;
    var p = 0;
    var s = 0;
    if (isLowBetter) {
        if (value <= goodThreshold) {
            // good
            return (2 * weight);
        }
        else {
            if (value <= badThreshold) {
                // between good and bad
                t = (badThreshold - goodThreshold);
                p = value - goodThreshold;
                s = p / t;
                return (1 + s);
            }
            else {
                if (value <= awfulThreshold) {
                    // between bad and awful
                    t = (awfulThreshold - badThreshold);
                    p = value - badThreshold;
                    s = p / t;
                    return (s);
                }
                else {
                    // awful
                    return (0);
                }
            }
        }
    }
    else {
        if (value >= goodThreshold) {
            // good
            return (2 * weight);
        }
        else {
            if (value >= badThreshold) {
                // between good and bad
                t = (badThreshold - goodThreshold);
                p = value - goodThreshold;
                s = p / t;
                return (1 + s);
            }
            else {
                if (value >= awfulThreshold) {
                    // between bad and awful
                    t = (badThreshold - awfulThreshold);
                    p = value - badThreshold;
                    s = p / t;
                    return (s);
                }
                else {
                    // awful
                    return (0);
                }
            }
        }
    }
} // end function CalculateScore
function download(text, name, type) {
    var a = document.createElement("a");
    var file = new Blob([text], { type: type });
    a.href = URL.createObjectURL(file);
    a.download = name;
    a.click();
}
function exportNetwork() {
    clearOutputArea();
    nodes = getNodesForExport();
    //      nodes.forEach(addConnections);
    //console.log(nodes);
    var netval = $("input[type='radio'][name='netlevel']:checked").val();
    var output = { "nodes": nodes, "info": { "url": SiteTitle, "title": PageTitle, "level": netval } }
    // pretty print node data
    var exportValue = JSON.stringify(output, undefined, 2);
    var fn = $("#export_fn").val();
    if (fn != '')
        download(exportValue, fn + '_data.json', 'text/plain');
    //   exportArea.value = exportValue;
    //console.log(thirdpartynetworknodes_data);
    resizeExportArea();
}
function getNodesForExport() {
    // work through node array
    var exportNodes = [];
    console.log("exporting " + thirdpartynetworknodes_data.length + " node data ");
    var found = false;
    var nodePositions = network.getPositions();
    console.log(nodePositions);
    // create lookup ref
    var ref = [];
    for (k in thirdpartynetworknodes_data) {
        var id = thirdpartynetworknodes_data[k]['id'];
        //console.log("node: " + k + " = id " + id);
        ref.push({ "node": k, "id": id });
    }
    // console.log("node refs:");
    // console.log(ref);
//console.log(thirdpartynetworklinks_data.length + "links:");
//console.log(thirdpartynetworklinks_data);
    var expcount = 0;
    // add nodes upfront
    for (k = 0; k < thirdpartynetworknodes_data.length; k++) {
        var label = thirdpartynetworknodes_data[k]['label'];
        var color = thirdpartynetworknodes_data[k]['color'];
        var id = thirdpartynetworknodes_data[k]['id'];
        var title = thirdpartynetworknodes_data[k]['title'];
        var font = thirdpartynetworknodes_data[k]['font'];
        var value = thirdpartynetworknodes_data[k]['value'];
        // get position of node
        var x = nodePositions[id]["x"];
        var y = nodePositions[id]["y"];
        // add node
        exportNodes.push({ "id": k, "x": x, "y": y, "color": color, "font": font, "label": label, "title": title, "value": value });
    }
    // go through nodes again to find links
    for (k = 0; k < thirdpartynetworknodes_data.length; k++) {
        var label = thirdpartynetworknodes_data[k]['label'];
        var id = thirdpartynetworknodes_data[k]['id'];
        console.log("node " + k + " - processing links for " + label);
        // get list of connections for this node
        var fromto = [];
        var fromtostr = '';
        var llabel = '';
        var lfrom = '';
        var lto = '';
        var foundFrom = -1;
        for (l = 0; l < thirdpartynetworklinks_data.length; l++) {
            lfrom = thirdpartynetworklinks_data[l]['from'];
            lto = thirdpartynetworklinks_data[l]['to'];
            //console.log("checking links for node " + k + " from " + lfrom + " to " + lto);
            // deal with from links
            foundFrom = -1;
            for (e = 0; e < ref.length; e++) {
                if (ref[e]['id'] == lfrom)
                    foundFrom = ref[e]['node'];
            }
            //console.log("from node " + lfrom + " is new " + foundFrom);
            if (foundFrom == k) {
                //console.log("found link for node " + foundFrom);
                //console.log(thirdpartynetworklinks_data[l]);
                //get new to node
                var foundTo = -1;
                for (f = 0; f < ref.length; f++) {
                    if (ref[f]['id'] == lto)
                        foundTo = ref[f]['node'];
                }
                if (foundFrom != -1 && foundTo != -1) {
                    fromto.push(foundTo);
                    llabel = thirdpartynetworknodes_data[foundTo]['label'];
                    console.log("adding data: from " + k + " " + label + " to " + foundTo + " " + llabel);
                    expcount++;
                }
                else {
                    if (foundTo == -1)
                        console.log("not found " + lto + " " + foundTo);
                    if (foundFrom == -1)
                        console.log("not found " + lfrom + " " + foundFrom)
                }
                //console.log("to node " + lto + " is new " + foundTo);
                // get label for to node
            }
        } // end loop through links array
        // fromtostr = JSON.stringify(fromto);
        // add to array
        exportNodes[k]['connections'] = fromto.sort();
    } // end for
    console.log(exportNodes);
    console.log(expcount + " links made");
    return (objectToArray(exportNodes));
}
function importNetwork() {
    var inputValue = exportArea.value;
    var inputData = JSON.parse(inputValue);
    var data = {
        nodes: getNodeData(inputData),
        edges: getEdgeData(inputData)
    }
    var layout = {
        randomSeed: undefined,
        improvedLayout: true,
        hierarchical: {
            enabled: true,
            levelSeparation: 150,
            nodeSpacing: 100,
            treeSpacing: 200,
            blockShifting: true,
            edgeMinimization: true,
            parentCentralization: true,
            direction: 'LR',        // UD, DU, LR, RL
            sortMethod: 'directed'   // hubsize, directed
        }
    }
    var options = {
        nodes: {
            shape: nodeshape,
            scaling: {
                customScalingFunction: function (min, max, total, value) {
                    return value / total;
                },
                min: 5,
                max: 100,
                label: {
                    min: 8,
                    max: 20
                }
            }
        },
        edges: {
            arrows: 'to'
        },
        physics: {
            "enabled": true,
            stabilization: {
                enabled: true,
                iterations: 10, // maximum number of iteration to stabilize
                updateInterval: 2,
                onlyDynamicEdges: false,
                fit: true
            },
        },
        interaction: {
            dragNodes: true,
            dragView: true
        },
        layout: layout,
    };
    network = new vis.Network(container, data, options);
    resizeExportArea();
}
function getNodeData(data) {
    var networkNodes = [];
    data.forEach(function (elem, index, array) {
        networkNodes.push({ id: elem.id, label: elem.label, x: elem.x, y: elem.y, color: elem.color, font: elem.font, title: elem.title });
    });
    return new vis.DataSet(networkNodes);
}
function getNodeById(data, id) {
    for (var n = 0; n < data.length; n++) {
        if (data[n].id == id) {  // double equals since id can be numeric or string
            return data[n];
        }
    };
    throw 'Can not find id \'' + id + '\' in data';
}
function getEdgeData(data) {
    var networkEdges = [];
    data.forEach(function (node) {
        // add the connection
        node.connections.forEach(function (connId, cIndex, conns) {
            networkEdges.push({ from: node.id, to: connId });
            let cNode = getNodeById(data, connId);
            var elementConnections = cNode.connections;
            // remove the connection from the other node to prevent duplicate connections
            var duplicateIndex = elementConnections.findIndex(function (connection) {
                return connection == node.id; // double equals since id can be numeric or string
            });
            if (duplicateIndex != -1) {
                elementConnections.splice(duplicateIndex, 1);
            };
        });
    });
    return new vis.DataSet(networkEdges);
}
function objectToArray(obj) {
    return Object.keys(obj).map(function (key) {
        obj[key].id = key;
        return obj[key];
    });
}
function resizeExportArea() {
    //   exportArea.style.height = (1 + exportArea.scrollHeight) + "px";
}
function loadJSON(path, success, error) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                success(JSON.parse(xhr.responseText));
            }
            else {
                error(xhr);
            }
        }
    };
    xhr.open('GET', path, true);
    xhr.send();
}
function clearOutputArea() {
    //exportArea.value = "";
}
function addConnections(elem, index) {
    // need to replace this with a tree of the network, then get child direct children of the element
    elem.connections = network.getConnectedNodes(index);
}
function destroyNetwork() {
    network.destroy();
}
