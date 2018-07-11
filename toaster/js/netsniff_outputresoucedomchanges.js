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


var page = require('webpage').create(),
    system = require('system');

if (system.args.length === 1) {
    console.log('Usage: netsniff.js <some URL>');
    phantom.exit(1);
} else {

    page.address = system.args[1];
	var viewportheight = system.args[2];
	var viewportwidth = system.args[3];
	var fname = system.args[4];
	var uastring = system.args[5];
    var brout = system.args[6];
    var outfile = '';
    if(brout.substr(0,5) != '/usr/')
        outfile = 'tmp/'+system.args[6] + '.txt'; // windows
    else
        outfile = system.args[6] + '.txt'; // linux
	var username = system.args[7];
	var password = system.args[8];
    var DOMbefore = '';
    var DOMafter = '';
    var DOMtempname ='tmp/'+system.args[6];

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
        DOMbefore = page.content;
    };


    page.onResourceReceived = function (res) {
        if (res.stage === 'start') {
            page.resources[res.id].startReply = res;

                        var fs = require('fs');
            var LF = "\r\n";
            //console.log(fs.workingDirectory);
            //fs.changeWorkingDirectory('C:\\temp');
            DOMafter = page.content;

            var path = DOMtempname + '-' + res.id + '.txt';
            var domlogpath = DOMtempname + '-log' + '.txt';
            //fs.remove(path);`


              var head = page.evaluate(function() {
                      return document.head.outerHTML;
                  });
              var content_head  = JSON.stringify(head);
              var body = page.evaluate(function() {
                      return document.body.outerHTML;
                  });
              var content_body  = JSON.stringify(body);

            if(DOMbefore != DOMafter)
            {
              var diff = '<html>'+diffString(DOMbefore,DOMafter)+'</html>';
              var id = pad(res['id'],5);
              var logcontent = id + ' -- ' + res.url + LF;
              fs.write('dom_'+domlogpath, logcontent, 'a');

              var content = id + ' -- ' + res.url + LF + content_head + LF + content_body + LF + LF + DOMbefore + LF + LF + DOMafter + LF;


                var str = page.evaluate(function() {

                  var scripts = document.scripts;
                    var s = '';
                  for (var i=0;i<scripts.length;i++) {
                     if (scripts[i].src) s = s + scripts[i].src + "\r\n";
                     else s = s + scripts[i].innerHTML + "\r\n";

                  }
                      return s;
                  });
                   fs.write('dom_'+path, str, 'a');
            } // end dom processing
        } //end start reply
        if (res.stage === 'end') {
            page.resources[res.id].endReply = res;
			//console.log(JSON.stringify(res));

        } // end emd reply



    };

//    page.onInitialized = function() {
 //     domloadeventtime = page.evaluate(function(domContentLoadedMsg)
 //     {
 //       var domload;
 //       document.addEventListener('DOMContentLoaded', function()
 //       {
 //         //console.log('DOM content has loaded.');
 //       }, false);
 //       domload = new Date();
 //       return domload;
 //       }
 //     );
 //   };


page.onInitialized = function() {
 domloadeventtime = page.evaluate(function(domContentLoadedMsg) {
    document.addEventListener('DOMContentLoaded', function() {
      window.callPhantom('DOMContentLoaded');
    }, false);
    domload = new Date();
        return domload;
  });
};



page.onCallback = function(data) {
  // your code here
  console.log('DOMContentLoaded');

      var fs = require('fs');
      //console.log(fs.workingDirectory);
      //fs.changeWorkingDirectory('C:\\temp');

      var domlogpath = DOMtempname + '-log' + '.txt';
      var LF = "\r\n";
      //fs.remove(path);`
      var content = 'DOM CONTENT LOADED' + LF;//page.content;
      fs.write('dom_'+domlogpath, content, 'a');

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

      var fs = require('fs');
      //console.log(fs.workingDirectory);
      //fs.changeWorkingDirectory('C:\\temp');

      var path = outfile;
      //fs.remove(path);`
      var content = page.content;
      fs.write(path, content, 'a');


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
});

/*
 * Javascript Diff Algorithm
 *  By John Resig (http://ejohn.org/)
 *  Modified by Chu Alan "sprite"
 *
 * Released under the MIT license.
 *
 * More Info:
 *  http://ejohn.org/projects/javascript-diff-algorithm/
 */

function escape(s) {
    var n = s;
    n = n.replace(/&/g, "&amp;");
    n = n.replace(/</g, "&lt;");
    n = n.replace(/>/g, "&gt;");
    n = n.replace(/"/g, "&quot;");

    return n;
}

function diffString( o, n ) {
  o = o.replace(/\s+$/, '');
  n = n.replace(/\s+$/, '');

  var out = diff(o == "" ? [] : o.split(/\s+/), n == "" ? [] : n.split(/\s+/) );
  var str = "";

  var oSpace = o.match(/\s+/g);
  if (oSpace == null) {
    oSpace = ["\n"];
  } else {
    oSpace.push("\n");
  }
  var nSpace = n.match(/\s+/g);
  if (nSpace == null) {
    nSpace = ["\n"];
  } else {
    nSpace.push("\n");
  }

  if (out.n.length == 0) {
      for (var i = 0; i < out.o.length; i++) {
        str += '<del>' + escape(out.o[i]) + oSpace[i] + "</del>";
      }
  } else {
    if (out.n[0].text == null) {
      for (n = 0; n < out.o.length && out.o[n].text == null; n++) {
        str += '<del>' + escape(out.o[n]) + oSpace[n] + "</del>";
      }
    }

    for ( var i = 0; i < out.n.length; i++ ) {
      if (out.n[i].text == null) {
        str += '<ins>' + escape(out.n[i]) + nSpace[i] + "</ins>";
      } else {
        var pre = "";

        for (n = out.n[i].row + 1; n < out.o.length && out.o[n].text == null; n++ ) {
          pre += '<del>' + escape(out.o[n]) + oSpace[n] + "</del>";
        }
        str += " " + out.n[i].text + nSpace[i] + pre;
      }
    }
  }

  return str;
}

function randomColor() {
    return "rgb(" + (Math.random() * 100) + "%, " +
                    (Math.random() * 100) + "%, " +
                    (Math.random() * 100) + "%)";
}
function diffString2( o, n ) {
  o = o.replace(/\s+$/, '');
  n = n.replace(/\s+$/, '');

  var out = diff(o == "" ? [] : o.split(/\s+/), n == "" ? [] : n.split(/\s+/) );

  var oSpace = o.match(/\s+/g);
  if (oSpace == null) {
    oSpace = ["\n"];
  } else {
    oSpace.push("\n");
  }
  var nSpace = n.match(/\s+/g);
  if (nSpace == null) {
    nSpace = ["\n"];
  } else {
    nSpace.push("\n");
  }

  var os = "";
  var colors = new Array();
  for (var i = 0; i < out.o.length; i++) {
      colors[i] = randomColor();

      if (out.o[i].text != null) {
          os += '<span style="background-color: ' +colors[i]+ '">' +
                escape(out.o[i].text) + oSpace[i] + "</span>";
      } else {
          os += "<del>" + escape(out.o[i]) + oSpace[i] + "</del>";
      }
  }

  var ns = "";
  for (var i = 0; i < out.n.length; i++) {
      if (out.n[i].text != null) {
          ns += '<span style="background-color: ' +colors[out.n[i].row]+ '">' +
                escape(out.n[i].text) + nSpace[i] + "</span>";
      } else {
          ns += "<ins>" + escape(out.n[i]) + nSpace[i] + "</ins>";
      }
  }

  return { o : os , n : ns };
}

function diff( o, n ) {
  var ns = new Object();
  var os = new Object();

  for ( var i = 0; i < n.length; i++ ) {
    if ( ns[ n[i] ] == null )
      ns[ n[i] ] = { rows: new Array(), o: null };
    ns[ n[i] ].rows.push( i );
  }

  for ( var i = 0; i < o.length; i++ ) {
    if ( os[ o[i] ] == null )
      os[ o[i] ] = { rows: new Array(), n: null };
    os[ o[i] ].rows.push( i );
  }

  for ( var i in ns ) {
    if ( ns[i].rows.length == 1 && typeof(os[i]) != "undefined" && os[i].rows.length == 1 ) {
      n[ ns[i].rows[0] ] = { text: n[ ns[i].rows[0] ], row: os[i].rows[0] };
      o[ os[i].rows[0] ] = { text: o[ os[i].rows[0] ], row: ns[i].rows[0] };
    }
  }

  for ( var i = 0; i < n.length - 1; i++ ) {
    if ( n[i].text != null && n[i+1].text == null && n[i].row + 1 < o.length && o[ n[i].row + 1 ].text == null &&
         n[i+1] == o[ n[i].row + 1 ] ) {
      n[i+1] = { text: n[i+1], row: n[i].row + 1 };
      o[n[i].row+1] = { text: o[n[i].row+1], row: i + 1 };
    }
  }

  for ( var i = n.length - 1; i > 0; i-- ) {
    if ( n[i].text != null && n[i-1].text == null && n[i].row > 0 && o[ n[i].row - 1 ].text == null &&
         n[i-1] == o[ n[i].row - 1 ] ) {
      n[i-1] = { text: n[i-1], row: n[i].row - 1 };
      o[n[i].row-1] = { text: o[n[i].row-1], row: i - 1 };
    }
  }

  return { o: o, n: n };
}

function pad (str, max) {
  return str.length < max ? pad("0" + str, max) : str;
}


}
