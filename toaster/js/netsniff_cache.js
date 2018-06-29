if (!Date.prototype.toISOString) {
    Date.prototype.toISOString = function () {
        function pad(n) { return n < 10 ? '0' + n : n; }
        function ms(n) { return n < 10 ? '00'+ n : n < 100 ? '0' + n : n }
        return this.getFullYear() + '-' +
            pad(this.getMonth() + 1) + '-' +
            pad(this.getDate()) + 'T' +
            pad(this.getHours()) + ':' +
            pad(this.getMinutes()) + ':' +
            pad(this.getSeconds()) + '.' +
            ms(this.getMilliseconds()) + 'Z';
    }
}

function createHAR(address, title, startTime, resources,domloadtime)
{
    var entries = [];

    resources.forEach(function (resource) {
        var request = resource.request,
            startReply = resource.startReply,
            endReply = resource.endReply;

        //console.log resource;

        if (!request || !startReply || !endReply) {
            return;
        }

        // Exclude Data URI from HAR file because
        // they aren't included in specification
        if (request.url.match(/(^data:image\/.*)/i)) {
            return;
	}

        entries.push({
            startedDateTime: request.time.toISOString(),
            time: endReply.time - request.time,
            request: {
                method: request.method,
                url: request.url,
                httpVersion: "HTTP/1.1",
                cookies: [],
                headers: request.headers,
                queryString: [],
                headersSize: -1,
                bodySize: -1
            },
            response: {
                status: endReply.status,
                statusText: endReply.statusText,
                httpVersion: "HTTP/1.1",
                cookies: [],
                headers: endReply.headers,
                redirectURL: "",
                headersSize: -1,
                bodySize: startReply.bodySize,
                content: {
                    size: -1,
                    mimeType: endReply.contentType
                }
            },
            cache: {},
            timings: {
                blocked: 0,
                dns: -1,
                connect: -1,
                send: 0,
                wait: startReply.time - request.time,
                receive: endReply.time - startReply.time,
                ssl: -1
            },
            pageref: address
        });
    });

    return {
        log: {
            version: '1.2',
            creator: {
                name: "PhantomJS",
                version: phantom.version.major + '.' + phantom.version.minor +
                    '.' + phantom.version.patch
            },
            pages: [{
                startedDateTime: startTime.toISOString(),
                id: address,
                title: title,
                pageTimings: {
                    onContentLoad: domloadtime - page.startTime,
                    onLoad: page.endTime - page.startTime
                }
            }],
            entries: entries
        }
    };
}


var page = require('webpage').create();
var system = require('system');
var cache = require('./cache');
var mimetype = require('./mimetype');
var fs = require('fs');

//this is the path that QTNetwork classes uses for caching files for it's http client
//the path should be the one that has 16 folders labeled 0,1,2,3,...,F
cache.cachePath = '/AppData/Local/Ofi Labs/PhantomJS/cache/data8/';
page.clearMemoryCache();

if (system.args.length === 1) {
    console.log('Usage: netsniff.js <some URL>');
    phantom.exit(1);
} else {
	//console.log(system.args[1]);
    page.address = system.args[1];
	var viewportheight = system.args[2];
	var viewportwidth = system.args[3];
	var fname = system.args[4];
	var uastring = system.args[5];
    var brout = system.args[6];
    var outfile = '';
    var outfileCK = '';
    var outfileCR = '';
    if(brout.substr(0,5) != '/usr/')
    {
        outfile = 'tmp/'+system.args[6]; // windows
        outfileCK = 'tmp/CK'+system.args[6]; // windows
        outfileCR = 'tmp/CR'+system.args[6]; // windows
    }
    else
    {
        outfile = system.args[6]; // linux
        outfileCK = system.args[6]+"CK"; // linux
        outfileCR = system.args[6]+"CR"; // linux
    }
    var username = system.args[7];
	var password = system.args[8];

	//console.log("PJS ua :" + uastring);
	//console.log("PJS us :" + username);
	//console.log("PJS pw :" + password);
    //console.log("PJS outfile :" + outfile);
    page.resources = [];

	page.settings.userAgent = uastring;
    var domloadeventtime = -99;

	if(username != '' && password != '' && typeof username != 'undefined' && typeof password != 'undefined')
	{
		//console.log('PJS adding authentication: '+ username + " " + password);
		page.customHeaders={'Authorization': 'Basic '+btoa(username+':'+ password)};
	}

	//console.log("viewport height:" + viewportheight + " ; width: " + viewportwidth);
	//console.log("img name:" + fname);
	page.viewportSize = {
	  width: viewportwidth,
	  height: viewportheight
	};


    page.onLoadStarted = function () {
        page.startTime = new Date();
    };

    page.onResourceRequested = function (req) {
        page.resources[req.id] = {
            request: req,
            startReply: null,
            endReply: null
        };
    };


    page.onResourceReceived = function (res) {
	//console.log(JSON.stringify(res));
	cache.includeResource(res);
    //    if (res.stage === 'start') {
    //        page.resources[res.id].startReply = res.time;
    //    }
    //    if (res.stage === 'end') {
    //        page.resources[res.id].endReply = res.time;	
    //    }
    };

    page.onInitialized = function() {
      domloadeventtime = page.evaluate(function(domContentLoadedMsg)
      {
        var domload;
        document.addEventListener('DOMContentLoaded', function()
        {
          //console.log('DOM content has loaded.');
        }, false);
        domload = new Date();
        return domload;
        }
      );
    };

    function onPageReady() {
        var har;
            page.endTime = new Date();
            page.title = page.evaluate(function () {
                return document.title;
            });
            har = createHAR(page.address, page.title, page.startTime, page.resources,domloadeventtime);
			console.log(JSON.stringify(har, undefined, 4));
			page.render(fname);
     
      //
      //fs.changeWorkingDirectory('C:\\temp');

      var path = outfile;
      //fs.remove(path);`
      var content = page.content;
      fs.write(path, content, 'a');

      saveCookies(outfileCK);
	  phantom.exit();
    }




page.open(page.address, function (status) {
    function checkReadyState() {
        setTimeout(function () {
            var readyState = page.evaluate(function () {
                return document.readyState;
            });

            if ("complete" === readyState) {
                onPageReady();
            } else {
                checkReadyState();
            }
        },2000);
    }

    checkReadyState();
	//page.render('saved/google.pdf');
	
});

//when the page is done loading, go through each cachedResource and do something with it,
//I'm just saving them to a file
page.onLoadFinished = function(status) {
	//console.log("onload finished");
	cnt = 0;
	for(index in cache.cachedResources) {
		cnt = cnt + 1;
		var file = cache.cachedResources[index].cacheFileNoPath;
		var ext = mimetype.ext[cache.cachedResources[index].mimetype];
		var finalFile = file.replace("."+cache.cacheExtension,"."+ext);
		fs.write('saved/'+finalFile,cache.cachedResources[index].getContents(),'b');
	}
	//console.log("onload finished:" + cnt);
};

function saveCookies(g_cookiesFile) {

    var fs = require("fs");

    fs.write(g_cookiesFile, JSON.stringify(phantom.cookies));
    //console.log("Saving cookies: " + JSON.stringify(phantom.cookies));
}


}