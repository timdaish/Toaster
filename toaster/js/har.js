/**
 * Toaster Web Performance HAR Viewer
 * Requires: jQuery 1.8+, jQueryUI, Highcharts 3+
 * 
 * Version 3
 *  2.2: with third party colouring 
 *  2.3 tag waterfall chart added
 *  3.0 file renamed from ncc_har.js to har.js to reflect unbundling from company code
 *  * based upon code developed by Gareth Hughes http://github.com/brassic-lint/ncc-harviewer
 * 
 */
var HARpages = '';
var tagwfmode = 1;
var renderstarttime = 0;
var domloadtime = 0;
var fullyloadedtime = 0;
var onloadtime = 0;
var rstitle = "Render Start";
var analysisOwner = '';
var analysisSite = '';
var analysisURL = '';
var analysisDisplayDate = new Date();
var analysisYear = analysisDisplayDate.getFullYear();
function loadConfigFile(configFile) {
    $.getJSON('config.json', function(data) {
//console.log("owner",data.owner);
        analysisOwner = data.owner;
        analysisSite = data.site;
        analysisURL = data.url;
    });
  }
function SanitiseTimings(input){
	
	if (input < 0) {
		output = 0; 
	} else {
		output = input;
	}

	//convert ms to s
	output = output / 1000;
	output = Math.round(output * 1000) / 1000;
	return output;
}

function SanitiseSize(input){
	var output = 0;
	if (input < 0) { 
		output = 0; 
	} else {
		output = input;
	}

	return output;
}

function getViewportWidth() {
	var viewPortWidth;
	
	// the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	if (typeof window.innerWidth !== 'undefined') {
	  viewPortWidth = window.innerWidth;
	}
	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	else if (typeof document.documentElement !== 'undefined'	&& typeof document.documentElement.clientWidth !== 'undefined' && document.documentElement.clientWidth !== 0) {
	   viewPortWidth = document.documentElement.clientWidth;
	}
	// older versions of IE
	else {
		viewPortWidth = document.getElementsByTagName('body')[0].clientWidth;
		}
	return viewPortWidth;
}

function TruncateURL (inputURL) {
	var TruncatedURL ="";
	
	if (inputURL.length > 40){
		TruncatedURL = inputURL.substring(0,15) + "..." + inputURL.slice(-16);
	} else {
		TruncatedURL = inputURL;
	}
	return TruncatedURL;	
}

function renderHAR(harfile){
	console.log("HAR processing invoked - toaster har.js");
	loadConfigFile();
//    console.log(harfile);

	//Parse HAR file
	var parsedHAR = $.parseJSON(harfile);

	//declare HARpages obj
	HARpages = {pages: []};



	//Loop log.pages -> array for each page (URL, timings, diags)
	$.each(parsedHAR.log.pages, function(i,pagesdata) {
		//initialise page object
		var pages = {
			id: pagesdata.id,
			objects: []
		};
//console.log("HAR processing - page id =" + pagesdata.id);

		//onload
		if ("onLoad" in pagesdata.pageTimings){
			pages.onLoadTime = SanitiseTimings(pagesdata.pageTimings.onLoad);
		}

		//renderstart (WPT)
		if ("_startRender" in pagesdata.pageTimings){
			pages.RenderStartTime = SanitiseTimings(pagesdata.pageTimings._startRender);
		}
		if ("_renderStart" in pagesdata.pageTimings && pagesdata.id){
			pages.RenderStartTime = SanitiseTimings(pagesdata.pageTimings._renderStart);
		}
		if ("_firstMeaningfulPaint" in pagesdata.pageTimings){ // Headless Chrome - Chrome Har Capturer
			pages._firstMeaningfulPaint = SanitiseTimings(pagesdata.pageTimings._firstMeaningfulPaint);
		}

		if ("_fullyLoaded" in pagesdata){
			pages.FullyLoadedTime = SanitiseTimings(pagesdata._fullyLoaded);
		}
		if ("_totalTime" in pagesdata.pageTimings){
			pages._totalTime = SanitiseTimings(pagesdata.pageTimings._totalTime);
		}

		if ("_domContentLoadedEventStart" in pagesdata){
			pages.DomContentStartTime = SanitiseTimings(pagesdata._domContentLoadedEventStart);
		}
		if ("_domContentLoaded" in pagesdata.pageTimings){
			pages._domContentStartTime = SanitiseTimings(pagesdata.pageTimings._domContentLoaded);
		}
		if ("onContentLoad" in pagesdata && pagesdata.id){
			pages.onContentLoad = SanitiseTimings(pagesdata.onContentLoad);
		}
		if ("onContentLoad" in pagesdata.pageTimings){
			pages.onContentLoad = SanitiseTimings(pagesdata.pageTimings.onContentLoad);
		}



		//get base time
		var basetime = new Date(pagesdata.startedDateTime);

        // get host domain
        //console.log("Site title = " + SiteTitle);
        var r = /:\/\/(.[^/]+)/;
        var hostdomain = SiteTitle.match(r)[1];

		//foreach log.entries in parsedHAR
		$.each(parsedHAR.log.entries, function(j,entries) {		
			//if log.entries.pageref == log.pages.id
			if(entries.pageref == pagesdata.id){

				//calculate offset
				var startTime = new Date(entries.startedDateTime);
				var offset = startTime - basetime;

				//Correct Connect time value
				if("ssl" in entries.timings) {
					var NewConnectTime = SanitiseTimings(entries.timings.connect) - SanitiseTimings(entries.timings.ssl);
				} else{
					var NewConnectTime = SanitiseTimings(entries.timings.connect);
				}
				
				NewConnectTime = Math.round(NewConnectTime * 1000) / 1000;

				
				//calculate totaltime & round it
				var totalTime = SanitiseTimings(entries.timings.receive) + SanitiseTimings(entries.timings.wait) + SanitiseTimings(entries.timings.send) + SanitiseTimings(entries.timings.ssl) + SanitiseTimings(entries.timings.connect) + SanitiseTimings(entries.timings.dns);
				totalTime = Math.round(totalTime*1000) / 1000;
				
				
				//build diags text
				var diagsText = "";
				diagsText = diagsText + "<p class='diagsHeader'>Request</p>";
				diagsText = diagsText + "<table>";
				$.each(entries.request.headers, function(j, cont) {
					diagsText = diagsText + "<tr><td class='diagsName'>" + cont.name + "</td><td class='diagsValue'>" + cont.value + "</td></tr>";
				});
				diagsText = diagsText + "</table>";
				
				diagsText = diagsText + "<p class='diagsHeader'>Response</p>";
				diagsText = diagsText + "<table>";
				$.each(entries.response.headers, function(j, cont) {
					diagsText = diagsText + "<tr><td class='diagsName'>" + cont.name + "</td><td class='diagsValue'>" + cont.value + "</td></tr>";
				});
				diagsText = diagsText + "</table>";
				
				if ("text" in entries.response.content){
						
					if(entries.response.content.mimeType.substring(0,4) == "text") {
						diagsText = diagsText + "<p class='diagsHeader'>Content</p>";
						HTMLoutput = entries.response.content.text.replace(/</g, "&lt;").replace(/>/g, "&gt;");
						diagsText = diagsText + "<pre>" + HTMLoutput + "</pre>";
					}
					
					if(entries.response.content.mimeType.substring(0,5) == "image") {
						diagsText = diagsText + "<p class='diagsHeader'>Content</p>";
						HTMLoutput = '<img src="data:' + entries.response.content.mimeType + ';base64,' + entries.response.content.text + '">';
						diagsText = diagsText + "<p>" + HTMLoutput + "</p>";
					}
					
				}
				


//console.log("HAR processing - object =" + entries.request.url);

// get domain name for object

                    var domainnm = entries.request.url.match(r)[1];

//console.log("HAR processing - " + domainnm + " for object =" + entries.request.url);

var group = '';
            //lookup domain name in domains list
                $.each(DomainsList, function() {

                    var tbl_row = "";

                    //console.log(this);

                    var domainName = this['Domain Name'];

                    group = this['Group'];
                    if (group === undefined || group == null)
                        group = 'Other';



                    if(domainnm == domainName)
                    {
//console.log("Group:  " + group + ": match found on domain: "+ domainnm + " = " + domainName );
						var protocol = hostdomain.indexOf("http://");
						if(protocol == 0)
						protocol = hostdomain.indexOf("https://");
						var hostdomaninonprop = hostdomain.substring(protocol.length);
                        if (domainnm == hostdomain)
                        {
                            group = 'First Party';
//console.log("Group:  " + group + ": match found on domain: "+ domainnm + " = " + domainName );
                        }
                        return false;
                    }


                 });

                var grpcolor = "";
                switch(group)
                {
                    case "Advertising":
                       grpcolor = "#C5190C";
                       break;

                    case "Analytics":
                       grpcolor = "#D79115";
                           break;

                    case "Content Provision":
                       grpcolor = "#DA98B3";
                           break;

                    case "Dynamic Content":
                       grpcolor = "#853E6F";
                           break;

                    case "Financial Services":
                       grpcolor = "#316E1C";
                           break;

                    case "Fraud & Security":
                       grpcolor = "#935AE6";
                           break;

                    case "Hosted Libraries":
                       grpcolor = "#9DB181";
                           break;

                    case "Hosted Media":
                       grpcolor = "#91410F";
                           break;

                    case "Social Media":
                       grpcolor = "#F37157";
                           break;

                    case "Tag Management":
                       grpcolor = "#11B9F0";
                           break;

                    case "User Interaction":
                       grpcolor = "#275676";
                           break;

                    case "Other":
                       grpcolor = "#EED3A3";
                            break;

                    case "First Party":
                       grpcolor = "#97C2FC";
                        break;

                    default: // target site
                       grpcolor = "#97C2FC";
                        break;

                }

				var transsize = 0;
				if(entries.response.bodySize != -1)
					transsize = SanitiseSize(entries.response.bodySize);
				else
					if(entries.response._transferSize != -1)
						transsize = SanitiseSize(entries.response._transferSize);



				//create object array
				var entryData = {
					URL: entries.request.url,
                    Domain: domainnm,
					TransSize: transsize,
					UncompSize: SanitiseSize(entries.response.content.size),
					ReqHeaderSize: SanitiseSize(entries.request.headersSize),
					ReqContentSize: SanitiseSize(entries.request.bodySize),
					RespHeaderSize: SanitiseSize(entries.response.headersSize),
					OffsetTime: SanitiseTimings(offset),
					DNSTime: SanitiseTimings(entries.timings.dns),
					ConnectTime: NewConnectTime,
					SSLConnTime: SanitiseTimings(entries.timings.ssl),
					ReqSentTime: SanitiseTimings(entries.timings.send),
					DataStartTime: SanitiseTimings(entries.timings.wait),
					ContentTime: SanitiseTimings(entries.timings.receive),
					TotalTime: totalTime,
					HTTPStatus: entries.response.status,
					DiagHTML: diagsText,
					StartTime: entries.startedDateTime,
                    Group: group,
					grpColor: grpcolor,
					MimeType: entries.response.content.mimeType
				};

				//push objects into pages
				pages.objects.push(entryData);
			//
			}
		//
		});
		
		//sort objects by offset ascending
		pages.objects.sort(function(a,b) { return parseFloat(a.OffsetTime) - parseFloat(b.OffsetTime) ;} );
		
		//push page into main object	
		HARpages.pages.push(pages);

	});



	//Loop each page to draw Waterfall & table (tabs??)
	x = 0;
	
	
	$.each(HARpages.pages, function(i,val) {
		
		x++;

		//Set up the draw area
		var ChartDIV = 'chart_' + x;
		var tableID = 'logs_' + x;
		hashtableID = '#' + tableID;
		hashtabsID = '#tabs_' + x;

		$('#HARChart').append('<div class="page_collapsible" id="section' + x + '"><span></span>Step ' + x + '</div><div class="container"><div class="content"><div id="tabs_' + x + '"><ul><li><a href="#tabs-1">Waterfall</a></li><li><a href="#tabs-2">Table</a></li></ul><div id="tabs-1"><div id="'+ChartDIV+'"></div></div><div id="tabs-2"><div id="timingsTable"><table id="' + tableID +'" border="1"><thead><tr><th>Group</th><th>Object Domain</th><th>Object</th><th>Trans.</th><th>Uncomp.</th><th>Req. Header</th><th>Req. Content</th><th>Resp. Header</th><th>Offset</th><th>DNS</th><th>Connect</th><th>SSL Conn.</th><th>Req. Sent</th><th>Data Start</th><th>Content</th><th>Total</th><th>Status</th><th>Diag</th></tr></thead></table></div></div></div></div></div>');


		//draw waterfall
		var options = {
	    	'chart':{
				'zoomType':'xy',
				'defaultSeriesType':'bar',
				},
			'title':{
				'margin':60
				},
			'subtitle':{
				'text':'',
				'margin':10
				},
			'legend':{
			    enabled: false
				},
			'credits':{
				'enabled':true,
				'href':'',
				'position':{
					'x':-15
					},
					text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
					href: analysisURL
				},
			'plotOptions':{
				'series':{
					'stacking':'normal',
					'shadow':false,
					'borderWidth':0,
					'animation':false,
					'pointPadding':0
					}
				},
			'yAxis':{
				'title':{
					'text':'Seconds'
					},
				'plotLines': [
					{
			            'color': 'red',
			            'width': 1,
			            'label':{}
			        },
		            {
		            	'color': 'green',
			            'width': 1,
			            'label':{}

		            }
                    ,
		            {
		            	'color': 'blue',
			            'width': 1,
			            'label':{}

		            }]
			},
			'xAxis':{
				'title':{
					'text':'Domains of Objects'
					},
				'categories': [],
				'opposite':true
			},

            'tooltip': {
                formatter: function() {
                    return "Group: " + this.point.grp +"<br/>" + "Domain: " + this.x + "<br/>" + "URL: " + this.point.url;
                }
            },
			'series':[
				{
			 	'name':'Content Download',
	         	'color':'#FF8500',
	         	'data':[]
	        },
			{
				'name':'Data Start',
				'color':'#66B3FF',
				'data':[]
	        },
	      	{
	        	'name':'Request Sent',
	        	'color':'#00FF00',
	         	'data':[]
	       	},
	        {
	         	'name':'SSL Connect',
	         	'color':'#FFCC33',
	         	'data':[]
	        },
	        {
	         	'name':'Connect',
	         	'color':'#995500',
	         	'data':[]
	        },
	        {
	         	'name':'DNS',
	         	'color':'#005CBB',
	         	'data':[]
	        },
	      	{
	         	'name':'Offset',
	         	'pointWidth':0,
	         	'color':'#FFFFFF',
	         	'data':[]
	        }
	        ]
	    };

		options.chart.renderTo = ChartDIV;
		options.chart.height = 200 + (val.objects.length * 20);
		options.chart.width = 1000; //getViewportWidth() - 100;
		options.title.text = "Third Party Timing Breakdown<br>" +  val.objects[0].URL;

		//onload
		options.yAxis.plotLines[0].value = val.onLoadTime;
		options.yAxis.plotLines[0].label.text = "onLoad (" + val.onLoadTime + ")";

		//renderstart (WPT)
		options.yAxis.plotLines[1].value = val.RenderStartTime;
		options.yAxis.plotLines[1].label.text = "Render Start (" + val.RenderStartTime + ")";
		if(val._firstMeaningfulPaint > 0)
		{
			options.yAxis.plotLines[1].value = val._firstMeaningfulPaint;
			options.yAxis.plotLines[1].label.text = "First Meaningful Paint (" + val._firstMeaningfulPaint + ")";
			rstitle = "First Meaningful Paint";
		}
		options.yAxis.plotLines[2].value = val._domContentStartTime;
		options.yAxis.plotLines[2].label.text = "DOM ContentLoaded Start (" + val._domContentStartTime + ")";


		$.each(val.objects, function(j,objects) {
			if (objects.URL.substring(0,4) != "data" && objects.URL.substring(0,6) != "chrome") {

//console.log(objects.grpColor);

				options.xAxis.categories.push(TruncateURL(objects.Domain));

				options.series[0].data.push({y:objects.ContentTime, color: objects.grpColor, grp: objects.Group, domain: objects.Domain, url: TruncateURL(objects.URL)});
				options.series[1].data.push({y:objects.DataStartTime, color: objects.grpColor, grp: objects.Group, domain: objects.Domain, url: TruncateURL(objects.URL)});
				options.series[2].data.push({y:objects.ReqSentTime, color: objects.grpColor, grp: objects.Group, domain: objects.Domain, url: TruncateURL(objects.URL)});
				options.series[3].data.push({y:objects.SSLConnTime, color: objects.grpColor, grp: objects.Group, domain: objects.Domain, url: TruncateURL(objects.URL)});
				options.series[4].data.push({y:objects.ConnectTime, color: objects.grpColor, grp: objects.Group, domain: objects.Domain, url: TruncateURL(objects.URL)});
				options.series[5].data.push({y:objects.DNSTime, color: objects.grpColor, grp: objects.Group, domain: objects.Domain, url: TruncateURL(objects.URL)});
				options.series[6].data.push(objects.OffsetTime);
			}
		});

		var chart = new Highcharts.Chart(options);

		//write table

		y=0;
		$.each(val.objects, function(j,objects) {
			y++;
			if (objects.HTTPStatus > 399) {
				trFormat = '<tr class="non200">';
			} else {
				trFormat = '<tr>';
			}

			$(hashtableID).append(trFormat + "<td>" + objects.Group + "</td>" + "<td>" + objects.Domain + "</td><td title='" + objects.URL + "'>" + TruncateURL(objects.URL) + "&nbsp;&nbsp;<a href='"+ objects.URL + "' target='_blank'><img src='/toaster/images/openpopup.png' alt='" + objects.URL + "'/></a></td><td>"+ objects.TransSize + "</td><td>"+ objects.UncompSize + "</td><td>" + objects.ReqHeaderSize + "</td><td>" + objects.ReqContentSize + "</td><td>" + objects.RespHeaderSize + "</td><td>" + objects.OffsetTime + "</td><td>" + objects.DNSTime + "</td><td>" + objects.ConnectTime + "</td><td>" + objects.SSLConnTime + "</td><td>" + objects.ReqSentTime + "</td><td>" + objects.DataStartTime + "</td><td>" + objects.ContentTime + "</td><td>" + objects.TotalTime + "</td><td>" + objects.HTTPStatus + "</td><td><a onclick=\"$( '#" + tableID + '_diag_'+ y + "' ).dialog({width:600, maxHeight:600});\"><img src='/toaster/images/diagnostics_on.gif' alt='Diagnostics'></a></td></tr>");
			$('#HARChart').append('<div id="' + tableID + '_diag_'+ y + '" title="Diagnostics for ' + objects.URL +'" style="display: none; width=250px;"><p>' + objects.DiagHTML + '</p></div>');
		});
		$( hashtabsID ).tabs();
	});
	$(function() {
		$( ".page_collapsible" ).collapsible();
		$("#instructions").hide();
	});

//console.log("generating custom legend for HAR Third Parties");
    $('#HARChartLegend').append('<h4 id="text">Legend: Third Party Groups</h4>');
    $('#HARChartLegend').append('<span id="firstparty">First Party</span>');
    $('#HARChartLegend').append('<span id="advertising">Advertising</span>');
    $('#HARChartLegend').append('<span id="analytics">Analytics</span>');
    $('#HARChartLegend').append('<span id="contentprovision">Content Provision</span>');
    $('#HARChartLegend').append('<span id="dynamiccontent">Dynamic Content</span>');
    $('#HARChartLegend').append('<span id="financialservices">Financial Services</span><br/>');
    $('#HARChartLegend').append('<span id="fraudandsecurity">Fraud &amp; Security</span>');
    $('#HARChartLegend').append('<span id="hostedlibraries">Hosted Libraries</span>');
    $('#HARChartLegend').append('<span id="hostedmedia">Hosted Media</span>');
    $('#HARChartLegend').append('<span id="socialmedia">Social Media</span>');
    $('#HARChartLegend').append('<span id="tagmanagement">Tag Management</span>');
    $('#HARChartLegend').append('<span id="userinteraction">User Interaction</span>');
    $('#HARChartLegend').append('<span id="other">Other</span>');

	display3PTagWaterfall(1);
}

function display3PTagWaterfall(tagwfmode){

	console.log("Generating tag waterfall chart");

	// mode 1 = default view = third party groups
	// mode 2 = nav timing
	// mode 3 = mimetype

	// loop through each page and draw tag waterfall chart
	var node = {};


	// mode 1
	var dataAdvertising = [];
	var dataAnalytics = [];
	var dataContentProvision = [];
	var dataDynamicContent = [];
	var dataFinancialServices = [];
	var dataFraudSecurity = [];
	var dataHostedLibraries = [];
	var dataHostedMedia = [];
	var dataSocialMedia = [];
	var dataTagManagement =  [];
	var dataUserInteraction = []
	var dataOther = [];
	var dataFirstPartyUnknown = [];

	// mode 2
	var dataNavTiming1 = [];
	var dataNavTiming2 = [];
	var dataNavTiming3 = [];
	var dataNavTiming4 = [];

	// mode 3
	var dataHTML = [];
	var dataCSS = [];
	var dataJavaScript = [];
	var dataFont = [];
	var dataImage = [];
	var dataData = [];
	var dataMTOther = [];
	var maxtiming = 0;


	$.each(HARpages.pages, function(i,val) {
		
console.log("HAR page Timings...");
console.log(val);

		if(val.id == "page_0" || val.id == "page_1_0" || val.id == "page_1_0_1") // former HTTPWatch iPhone / latter WPT
		{
			renderstarttime = val.RenderStartTime;
			domloadtime = val.DomContentStartTime;
			fullyloadedtime = val.FullyLoadedTime;
			onloadtime = val.onLoadTime;
		}
		else
		{
			renderstarttime = val._firstMeaningfulPaint;
			domloadtime = val.onContentLoad;
			onloadtime = val.onLoadTime;
			fullyloadedtime = val._totalTime;
		}

		x++;
		y=0;

		$.each(val.objects, function(j,objects) {
			y++;
 console.log("object...");
 console.log(objects);



			$.each(DomainsList, function () {
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

				var coprod = product;
				if (product.indexOf(sitecompany) == -1)
					coprod = sitecompany + " " + product;

				var truncUrl = objects.URL;
				if(truncUrl.length > 127)
					truncUrl = "..." + objects.URL.substring(objects.URL.length - 127,objects.URL.length)
					

				if(domainName == objects.Domain)
				{
					var nodename = product;
					if(domainRef == "Primary")
					{
						nodename = '';
						group = 'First Party';
						sitecat = 'First Party';
						coprod = '';
					}
						var totalsize = objects.TransSize + objects.RespHeaderSize;
						if(totalsize == 0)
						{
							tsize  = 5;
//console.log("small object - " + objects.URL);
						}

					node = {x: objects.OffsetTime, y:totalsize, z: totalsize, "url": truncUrl, name: nodename, "group": objects.Group, "cat": sitecat, "company": sitecompany, "product": product, "host": domainName, "contenttime": objects.ContentTime, coprod: coprod, mimetype: objects.MimeType, hdrsize: objects.RespHeaderSize, transize: objects.TransSize, totsize: totalsize};
					// mode 1 - third party groups
					switch(objects.Group) {
						case "Advertising":
							dataAdvertising.push(node);
							break;
						case "Analytics":
							dataAnalytics.push(node);
//console.log("analytics: " + objects.URL);
							break;
						case "Content Provision":
							dataContentProvision.push(node);
							break;
						case "Dynamic Content":
							dataDynamicContent.push(node);
							break;
						case "Financial Services":
							dataFinancialServices.push(node);
							break;
						case "Fraud & Security":
							dataFraudSecurity.push(node);
							break;
						case "Hosted Libraries":
							dataHostedLibraries.push(node);
							break;
						case "Hosted Media":
							dataHostedMedia.push(node);
							break;
						case "Social Media":
							dataSocialMedia.push(node);
							break;
						case "Tag Management":
							dataTagManagement.push(node);
							break;
						case "User Interaction":
							dataUserInteraction.push(node);
							break;
						case "Other":
							dataOther.push(node);
							break;
						default:
							dataFirstPartyUnknown.push(node);
					}
					
					//mode 2 - nav timing
//console.log("mode 2 check " + objects.OffsetTime + " v " + renderstarttime);
					if (parseFloat(objects.OffsetTime) < parseFloat(renderstarttime))
						dataNavTiming1.push(node);
					else
						if(parseFloat(objects.OffsetTime)< parseFloat(domloadtime))
							dataNavTiming2.push(node);
						else 
						if(parseFloat(objects.OffsetTime)< parseFloat(onloadtime))
							dataNavTiming3.push(node);
						else
						//	if(parseFloat(objects.OffsetTime)< parseFloat(fullyloadedtime))
								dataNavTiming4.push(node);
					if(objects.OffsetTime  > maxtiming)
						maxtiming = objects.OffsetTime;
					// mode 3 - mime types
					// remove chartset ref
					var mt = '';

					if(objects.MimeType !== null)
					{
						objects.MimeType.toLowerCase();
						mt = mt.replace(/\s+/g, '') // remove spaces
						var charsetpos = mt.indexOf("charset");
						var semicolonpos = mt.indexOf(";");
	// /console.log("mimetype check " + mt);
						if( charsetpos != -1)
							{
	//console.log("mimetype check - charset found - " + mt);
								if(charsetpos == 0)
								{
									mt = mt.substring(semicolonpos);
	//console.log("mimetype check - 0 charset removed");
								}
								else
								{
									mt = mt.substring(0, charsetpos -1);
	//console.log("mimetype check - pos charset removed");	
								}
	//console.log("mimetype check - charset removed; mt = " + mt);
							}
							else
							mt = objects.MimeType;
					}
	//console.log("mimetype check ",objects.MimeType,mt);
					switch (mt)
					{
						case "text/html" :
							dataHTML.push(node);
							break;
						case "text/css" :
							dataCSS.push(node);
							break;
						case "application/javascript" :
						case "application/x-javascript" :
						case "text/javascript" :
						case "text/x-js" :
							dataJavaScript.push(node);
							break;
						case "text/xml" :
						case "application/xml" :
						case "application/json" :
							dataData.push(node);
							break;
						case "image/jpeg" :
						case "image/jpg" :
						case "image/x-bpg" :
						case "image/bpg" :
						case "image/gif" :
						case "image/png" :
						case "image/bmp" :
						case "image/tiff" :
						case "image/webp" :
						case "image/svg+xml" :
						case "image/x-icon" :
							dataImage.push(node);
							break;
						case "application/x-font-woff" :
						case "application/font-woff2" :
						case "font/woff" :
						case "font/woff2" :
						case "application/x-font-ttf" :
						case "application/x-font-truetype" :
						case "application/x-font-opentype" :
						case "application/vnd.ms-fontobject" :
						case "application/font-sfnt" :
						case "application/octet-stream" :
							dataFont.push(node);
							break;
						case "text/plain" :
						default:
							// try by extension instead
							var url = objects.URL;
							var ob_fm = getFilename(url);
//console.log("mimetype other - checking extension " + ob_fm.ext);
							switch(ob_fm.ext)
							{ // incomplete - to grow
								case "woff":
								case "woff2":
									dataFont.push(node);
									break;
								default:
									dataMTOther.push(node);
							}
							break;
					}

				}

		//console.log(domainName + " = " + product + ": group/cat: '" + group + "'/'" + sitecat +"'");
	});
		}); // end object

	});  // end page

	// mode 1
	var dataSeriesMode1 = [
		{
			'name':'Advertising',
			'color':'#C5190C',
			'data':dataAdvertising
	   },
	   {
		   'name':'Analytics',
		   'color':'#D79115',
		   'data':dataAnalytics
	   },
		 {
		   'name':'Content Provision',
		   'color':'#DA98B3',
			'data':dataContentProvision
		  },
	   {
			'name':'Dynamic Content',
			'color':'#853E6F',
			'data':dataDynamicContent
	   },
	   {
			'name':'Financial Services',
			'color':'#316E1C',
			'data':dataFinancialServices
	   },
	   {
			'name':'Fraud &amp; Security',
			'color':'#935AE6',
			'data':dataFraudSecurity
	   },
	   {
			'name':'Hosted Libraries',
			'pointWidth':0,
			'color':'#9DB181',
			'data':dataHostedLibraries
	   },
	   {
		  'name':'Hosted Media',
		  'color':'#91410F',
		  'data':dataHostedMedia
		   },
	   {
		   'name':'Social Media',
		   'color':'#F37157',
		   'data':dataSocialMedia
		   },
		   {
		   'name':'Tag Management',
		   'color':'#11B9F0',
		   'data':dataTagManagement
	   },
		 {
			'name':'User Interation',
			'color':'#275676',
			'data':dataUserInteraction
	   },
		 {
			'name':'Other',
			'color':'#EED3A3',
			'data':dataOther
	   },
	   {
		  'name':'First Party or Unknown',
		  'color':'#97C2FC',
		  'data':dataFirstPartyUnknown
	 }
];


	// mode 2
	var dataSeriesMode2 = [
		{
			'name':'Before ' + "render/firstpaint",
			'color':'rgba(0,136,0,0.25)',
			'data':dataNavTiming1
		},
		{
			'name':'Before DOM Load',
			'color':'rgba(0,0,255,0.3)',
			'data':dataNavTiming2
		},
		{
			'name':'Before OnLoad',
			'color':'rgba(255,0,0,0.3)',
			'data':dataNavTiming3
		},
		{
			'name':'Before Total Time',
			'color':'rgba(0,0,0,0.3)',
			'data':dataNavTiming4
		},
		]


	// mode 3
	var dataSeriesMode3 = [
		{
			'name':'HTML',
			'color':'#66190C',
			'data':dataHTML
	   },
	   {
		   'name':'StyleSheets',
		   'color':'#669115',
		   'data':dataCSS
	   },
		 {
		   'name':'JavaScript',
		   'color':'#3398B3',
			'data':dataJavaScript
		  },
	   {
			'name':'Images',
			'color':'#223E6F',
			'data':dataImage
	   },
	   {
			'name':'Fonts',
			'color':'#316E1C',
			'data':dataFont
	   },
	   {
			'name':'Data',
			'color':'#215AE6',
			'data':dataData
	   },
		{
			'name':'Other',
			'color':'#aa5AE6',
			'data':dataMTOther
		},
];


console.log("plotting data for mode " + tagwfmode.toString());
	var tagtext = '';
	// select series data for mode being displayed
	if(tagwfmode == 1)
	{
		dataSeries = dataSeriesMode1;
		tagtext = 'Tags by Group and Total Size (incl. response header) against page load duration'
	}
	if(tagwfmode == 2)
	{
		dataSeries = dataSeriesMode2;
		tagtext = 'Tags by Navigation Timing and Total Size (incl. response header) against page load duration';
	}
	if(tagwfmode == 3)
	{
		dataSeries = dataSeriesMode3;
		tagtext = 'Tags by Content Type and Total Size (incl. response header) against page load duration';
	}

console.log("render start time/First Meaningful Paint: " + renderstarttime + " = " + rstitle);
// blitz all nav timings if render not present
// if(typeof renderstarttime === "undefined" )
// {
// 	renderstarttime = "undefined";
// 	domloadtime = "undefined";
// 	onloadtime = "undefined";
// 	fullyloadedtime = "undefined"
// }
//console.log("timing dataseries",dataSeries);
if(fullyloadedtime === undefined)
	var xaxismaxvalue = Math.max(onloadtime, maxtiming ) + 1;
else
	var xaxismaxvalue = Math.max(onloadtime, fullyloadedtime, maxtiming ) + 1;
console.log(rstitle + " ; max x value", onloadtime,fullyloadedtime, maxtiming);
    $(function () {
        $('#container_3Ptagwaterfall').highcharts({

        chart: {
            type: 'bubble',
            plotBorderWidth: 1,
			zoomType: 'xy',
			height: 900
        },
    
        legend: {
            enabled: true
        },
    
        title: {
            text: 'Third-Party Tag Waterfall'
        },
    
        subtitle: {
            text: tagtext
        },
    
        xAxis: {
            //gridLineWidth: 1,
            title: {
                text: 'Page Load Duration (seconds)'
			},
			max: xaxismaxvalue,
            labels: {
                format: '{value} s'
			},
			plotLines: [{
                color: 'green',
                width: 2,
                value: renderstarttime,
                label: {
                    align: 'left',
                    style: {
                        fontStyle: 'normal'
                    },
                    text: rstitle,
                    x: -10
                },
                zIndex: 3
			},
			{
                color: 'blue',
                width: 2,
                value: domloadtime,
                label: {
                    align: 'left',
                    style: {
                        fontStyle: 'normal'
                    },
                    text: 'DOM Load',
                    x: -10
                },
                zIndex: 3
			},
			{
                color: 'red',
                width: 2,
                value: onloadtime,
                label: {
                    align: 'left',
                    style: {
                        fontStyle: 'normal'
                    },
                    text: 'onLoad',
                    x: -10
                },
                zIndex: 3
			},
			{
                color: 'black',
                width: 2,
                value: fullyloadedtime,
                label: {
                    align: 'left',
                    style: {
                        fontStyle: 'normal'
                    },
                    text: 'Total Time',
                    x: -10
                },
                zIndex: 3
			},
		
		]
        },
    
        yAxis: {
			type: 'logarithmic',
			minorTickInterval: 1,
            startOnTick: false,
            endOnTick: false,
            title: {
                text: 'Object Size (bytes)'
            },
            maxPadding: 0.2,

        },
    
        tooltip: {
            useHTML: true,
            headerFormat: '<b style="color:{series.color}">{series.name}</b><br>',
            pointFormat: '<b style="color:{series.color}">{point.cat} - {point.coprod}</b><br/>URL: {point.url}<br/>Host: {point.host}<br/>Total Size: {point.totsize:,0f} Bytes<br/>Object Size: {point.transize:,0f} bytes; hdr size: {point.hdrsize:,0f} bytes<br/> Content Download Time: {point.contenttime} sec<br/>Content Type: {point.mimetype}',
            footerFormat: '',
            followPointer: true
        },
    
        plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
					format: '{point.name}',
					color: 'black',
					style: {
						textOutline: false 
					}
                }
            }
        },
		credits: {
			text: '\u00A9' + " " + analysisYear + " "+ analysisOwner + " " + analysisSite,
			href: analysisURL
		},
		series: dataSeries,
		exporting: {
			sourceWidth: 1900,
        	sourceHeight: 1080,
		}
	
    })

	setTimeout('$("#tagwfToggle").removeAttr("disabled")', 1500);

    });


}

$('#tagwfToggle').click(function () {
	event.preventDefault();
	$(this).attr("disabled", "disabled");
	if(tagwfmode == 1)
	tagwfmode = 2;
	else
		if(tagwfmode == 2)
		tagwfmode = 3;
			else
			if(tagwfmode == 3)
			tagwfmode = 1;

console.log("3p tag waterfall toggle in mode " + tagwfmode.toString());
	display3PTagWaterfall(tagwfmode);

});

function getFilename(url){
	// returns an object with {filename, ext} from url (from: http://coursesweb.net/ )
  
	// get the part after last /, then replace any query and hash part
	url = url.split('/').pop().replace(/\#(.*?)$/, '').replace(/\?(.*?)$/, '');
	url = url.split('.');  // separates filename and extension
	return {filename: (url[0] || ''), ext: (url[1] || '')}
  }
