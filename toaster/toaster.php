<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
  <title>THE WEBPAGE TOASTER</title>
  <link rel="stylesheet" type="text/css" href="css/toaster.css">
</head>

<body>
  <meta charset="UTF-8">
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
  <div id="titlebar">
    <span class="titlebarmain">The Webpage Toaster:</span>
    <span class="titlebarsub">Webpage Tool for Optimisation and Analysis thru Static Testing and Enhanced Reporting</span>
  </div>
  <div id="floatcontainer">
    <form enctype="multipart/form-data" id="form" name="input" action="" method="post">
      <div class="radios">
        <label for="wbengine" title="Web Browser Engine - use timings with caution!">Web Browser Engine</label>
        <!--<input type="radio" name="wbengine" value="pjs1.9" title="PhantomJS v1.9.8">Webkit v1.9&nbsp;-->
        <span class="multipleradio">
        <!--<input type="radio" name="wbengine" value="pjs2.0" title="PhantomJS v2">Webkit v2.0&nbsp;-->
        <input type="radio" name="wbengine" class="wbNone" id="none" value="none" checked title="none"><span class="wbNone">None</span>
        <input type="radio" name="wbengine" class="wbChromeheadless" id="chromeheadless" value="ch_headless" title="Headless Chrome"><span class="wbChromeheadless">Headless Chrome</span>
        <input type="radio" name="wbengine" class="wbpjs25" id="pjs2.5" value="pjs2.5" title="PhantomJS v2.5"><span class="wbpjs25">PhantomJS v2.5</span>
        <input type="radio" name="wbengine" class="wbpjs21" id="pjs2.1" value="pjs2.1" title="PhantomJS v2.1"><span class="wbpjs21">PhantomJS v2.1</span>
        <!--<input type="radio" class="wbsjs0.10" id="sjs0.10" name="wbengine" value="sjs0.10" title="SlimerJS v0.10"><span class="wbsjs0.10">Gecko v0.10</span>-->
        <input type="radio" name="wbengine" class="wbwpt_private" id="wpt_private" value="wpt_private" title="WPT Private Instance"><span class="wbwpt_private">WPT Private</span>
        <input type="radio" name="wbengine" class="wbwpt_public" id="wpt_public" value="wpt_public" title="WPT Public" disabled><span class="wbwpt_public">WPT Public</span>
      </span>
      </div>
      <div id="wbengineoptions"></div>
      <!--<a href="toasted.php" target="_blank"><img class="mg" src="/toaster/images/magglass.png"alt="View Toasted Pages" title="View Toasted Pages"></img></a>-->
      <label>Page URL</label>
      <input type="text" name="url" id="urlfield" size="70" maxlength="10000" value="" class="long" required autofocus>
      <label>Real(*WPT)/Emulated Device</label>
      <select id="ualist" name="ua" class="long" required>
      </select>
      <div class="checkbox">
        <label for="cssimgs">Get All Available Objects</label>
        <input id="cssimgs" type="checkbox" name="cssimgs" value="cssimgs">
        <!--<label for="links3p">Get 3P Descs.</label>
<input id="links3p" type="checkbox" name="links3p" value="links3p">-->
        <label for="links">Show Page Links</label>
        <input id="links" type="checkbox" name="chklinks" value="links">
        <!--<label for="dbusage">Use 3rd Party DB</label>
<input id="dbusage" type="checkbox" hidden checked name="dbusage" value="dbusage">-->
        <!--<label for="debug">Show Debug Msgs</label>
        <input id="debug" type="checkbox" name="chkdebug" value="chkdebug">-->
        <label for="akdebug">Get Akamai Debug Hdrs</label>
        <input id="akdebug" type="checkbox" name="akdebug" value="akdebug">
        <!--<label for="3pchain">Get 3rd Party Call Chain</label>
<input id="3pchain" type="checkbox" hidden name="3pchain" value="3pchain" title="CAUTION - This can take a while!">-->
      </div>
   <!--   <div class="radios">
        <label for="ip">Geo IP Location Lookup</label>
        <input type="radio" name="ip" value="none" checked>None&nbsp;
        <input type="radio" name="ip" value="domain">Domain&nbsp;
        <input type="radio" name="ip" value="all">All
        <br>
        <label for="ipapi">Geo IP API Provider</label>
        <input type="radio" name="ipapi" value="dbip" title="Best option - Cities - limit of 2,000 requests per day" checked>db-IP&nbsp;
        <input type="radio" name="ipapi" value="freegeoip" title="2nd option - Countries - limit of 10,000 requests per hour" checked>Freegeoip
        <input type="radio" name="ipapi" value="hackertarget" title="3rd option - good for US and Australian locations" >HackerTarget
      </div>-->
      <label>Auth Username</label>
      <input type="text" name="username" size="60" width="60" class="long" value="">
      <label>Auth Password</label>
      <input type="text" name="password" size="60" width="60" class="long" value="">
      <label>Notes</label>
      <textarea id="comment" name="comment" placeholder="Enter text here..." class="long"></textarea>
      <!-- The data encoding type, enctype, MUST be specified as below -->
      <!-- MAX_FILE_SIZE must precede the file input field -->
      <!--<label for="fileupload">Upload HAR</label>-->
      <input type="hidden" name="MAX_FILE_SIZE" value="26214400" />
      <!-- Name of input element determines name in $_FILES array -->
      <!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="harfile" name="fileupload" type="file" />&nbsp;
<div class="checkbox">
<label for="harex">Exclusive HAR Content</label>
<input id="harex" type="checkbox" name="harex" value="harex">
</div>-->
      <div id="centerbuttons">
        <!--<button id="reset" type="reset" value="Reset!">Reset</button>-->
        <button id="submit" type="submit" value="Toast It!">
          <img src="/toaster/images/toastslice.png" height="36" />Toast it!</button>
      </div>
    </form>
    <!--<textarea rows="4" cols="50" name="comment" form="form">Enter multiple URLs here...</textarea>-->
    <div align="center" id="toastingurl" class="statusupdates">
      <span id="toasturl"></span>
    </div>
    <div align="center" id="statusupdate" class="statusupdates">&nbsp;
      <span id="status">Ready to Toast</span>
    </div>
    <div align="center" id="fileupdate" class="statusupdates">
      <span id="statusdetail">&nbsp;</span>
    </div>
    <!-- <div align="center" id="fileupdate" class="statusupdates"><img src="" height="64" width="64" class="imgicon"></img><span id="statusdetail">&nbsp;</span></div> -->
    <!-- <button id="stop">Stop</button> -->
    <div id="response">
      <pre></pre>
    </div>
    <div id="boxlinks">
      <span id="timeval"></span>
      <a class="tpdb" href="toasted.php/" target="_blank" title="View History">View History</a>&nbsp;
      <br/>
        <a class="tpdb" href="#" target="_blank" title="Help">Help</a>&nbsp;
      </div>
      <div id="toastingnow"></div>
    </div>
  </div>
  <script type="text/javascript" charset="utf8" src="/toaster/js/jquery.min.js"></script>
  <script type="text/javascript">
    var toastedfilename = '';
    var refreshStatus = '';
    var refreshstatuserrorcount = 0;
    $(document).ready(function () {
      $storedurl = localStorage.getItem('urlfield');

//console.log("storedurl",$storedurl);
      if($storedurl != '')
      {
        $("#urlfield").val($storedurl);
      }
      $("#urlfield").focus('focus', function() { $(this).select(); });

      // determine server name
      var svrname = location.hostname;
//console.log("hostname",svrname);
      // determine server capabailities
      var optPhantomJS21 = false;
      var optPhantomJS25 = false;
      var optNodeChromeHeadless = false;
      var optWPTPrivateInstance = false;
      var optWPTPublic = false;
      switch(svrname)
      {
        case "www.webpagetoaster.com": // public web page toaster
          // only local
          optNodeChromeHeadless = true;
          optWPTPublic = true;
          break;
        case "hpdev": // private web page toaster hosted on a local server
          optNodeChromeHeadless = true;
          optWPTPrivateInstance = true;
          optPhantomJS25 = true;
          break;
        case "localhost": // private web page toaster hosted on the local machine
          optPhantomJS25 = true;
          optNodeChromeHeadless = true;
          optWPTPrivateInstance = true;
          optWPTPublic = true;
        default:
      }
      // populate wbengine fields depending upon server
      // console.log("pjs", optPhantomJS);
      // console.log("LocalNodeChromeHeadless", optLocalNodeChromeHeadless);
      // console.log("RemoteNodeChromeHeadless", optNodeChromeHeadless);
      // console.log("WPTPublic", optWPTPublic);
      // console.log("WPTPrivateInstance", optWPTPrivateInstance);
      if(optPhantomJS21 == false)
      {
//console.log("removing phantomjs");
        // remove phantomjs fields
        $(".wbpjs21").hide();
      }
      if(optPhantomJS25 == false)
      {
//console.log("removing phantomjs");
        // remove phantomjs fields
        $(".wbpjs25").hide();
      }
      if(optNodeChromeHeadless == false)
      {
//console.log("removing chrome headless");
        // remove chrome headless fields
        $(".wbChromeheadless").hide();
      }
      if(optWPTPrivateInstance == false)
      {
//console.log("removing WPT private");
        // remove WPT Private fields
        $(".wbwpt_private").hide();
      }
      if(optWPTPublic == false)
      {
//console.log("removing WPT public");
        // remove WPT public fields
        $(".wbwpt_public").hide();
      }
      //set up option fields
      $('input[name="wbengine"]').change(function () {
        $("#wbengineoptions").empty();
        if ($('#wpjs2.1').prop('checked') || $('#wpjs2.5').prop('checked')) {
//console.log('phantomjs is checked!');
          // change url label

        }
        if ($('#chromeheadless').prop('checked')) {
//console.log('chrome headless is checked!');
          // add remote port option
            $("#wbengineoptions").append('<label for="chremoteurlandport">Remote URL and Port</label><input id="chremoteurlandport" name="chremoteurlandport" placeholder="enter server address and port" class="long"></input>');
          $storedchhserver = localStorage.getItem('chhserver');
          $('#chremoteurlandport').val($storedchhserver);
        }
        if ($('#wpt_private').prop('checked')) {
//console.log('wpt private is checked!');
        //  $("#wbengineoptions").append('<label for="wptprivate">Private Server</label><input id="wptprivate" name="wptprivate"></input>');
        }
        if ($('#wpt_public').prop('checked')) {
//console.log('wpt public is checked!');
          $("#wbengineoptions").append('<label for="wptpublic">Remote Server<input id="wptpublic" name="wptpublic"></input>');
          $("#wbengineoptions").append('<label for="wptpublicapikey">API Key<input id="wptpublicapikey" name="wptpublicapikey"></input>');
        }
      });

      // populate device user agent list
      let dropdown = $('#ualist');
      dropdown.empty();

      dropdown.append('<option selected="true" disabled>Choose Device</option>');
      //dropdown.prop('selectedIndex', 0);

      const uaConfigUrl = 'ua-config.json';

      // Populate dropdown with list of devices
      $.getJSON(uaConfigUrl, function (data) {
        $.each(data, function (key, entry) {
          var deviceName = entry.uastr;
          if(entry.realdevice == "true")
          deviceName += "*";
          dropdown.append($('<option></option>').attr('value', key).text(deviceName));
        })
      });
      dropdown.prop('selectedIndex', 1);


      function getTime(formatted) {
        var currentTime = new Date()
        var hours = currentTime.getHours()
        var minutes = currentTime.getMinutes()
        var seconds = currentTime.getSeconds()
        if (minutes < 10) {
          minutes = "0" + minutes;
        }
        if (seconds < 10) {
          seconds = "0" + seconds;
        }
        var t_str = hours + ":" + minutes + ":" + seconds;
        if (hours > 11) {
          t_str += "<small>PM</small>";
        } else {
          t_str += "AM";
        }

        changeQuote(seconds);

        if (formatted)
          return t_str;
        else
          return currentTime;
      }
      function updateTime() {
        t_str = getTime(true);
        //console.log(t_str);
        document.getElementById('timeval').innerHTML = t_str;
      }
      setInterval(updateTime, 1000);

      function changeQuote(seconds) {
        //console.log(seconds);
        var bartext = '';
        var boolChange = false;
        if (seconds == 0) {
          bartext = "Webpage Tool for Optimisation and Analysis thru Static Testing and Enhanced Reporting";
          boolChange = true;
        }
        else {
          if (seconds == 30) {
            bartext = "The best thing since sliced bread!";
            boolChange = true;
          }
        }
        if (boolChange == true) {
          $('.titlebarsub').fadeOut('slow', function () {
            $('.titlebarsub').text(bartext);
            $('.titlebarsub').fadeIn('slow');
          });
        }
      }

    }
    );

function startRefresh()
{
  refreshStatus = setInterval(function () {
  $.ajax(
    {
      type: "GET",
      url: "xhr_getstatus.php",
      //data: '',
      beforeSend: function () {
        console.log("getting status");;
            },
      dataType: 'json',
      success: function (obj) {
        //$('#status').html(msg);
  
console.log(JSON.stringify(obj));

        if (obj.status == 'Ready to Toast') {
//console.log("status is now ready to toast")
          $('#submit').removeAttr('disabled');
          $('#submit').removeClass("grey");
          $('#statusdetail').html("&nbsp;");
          $('#toasturl').html("&nbsp;");
          $('#fileupload').html("");
          $('div#toastingnow > img').remove();
          var imgpath;
        }
        else {
          $('#status').text(decodeURI(obj.status));
          if (obj.status.substr(0, 11) == "Downloading" || obj.status.substr(0, 9) == "Analysing" || obj.status.substr(0, 11) == "Identifying" || obj.status.substr(0, 6) == "iframe") {
            var statusToDisplay = decodeURI(obj.object);
            if (statusToDisplay.length > 50) {
              statusToDisplay = statusToDisplay.substr(1, 25) + ".." + statusToDisplay.slice(-25);
            }
            else
              $('#statusdetail').html(statusToDisplay);
            //if (obj.mimetype == '')
            //  obj.mimetype = 'application_x_mswinurl';
            //var loc = 'images/mimetypes128x128/' + obj.mimetype + '.png';
            //console.log("src " + obj.object + "; mt = " + obj.mimetype);

            //$('#fileupdate img').attr("src",loc);
          }
          else {
            $('#statusdetail').html("&nbsp;");
            //$('#fileupdate img').attr("src",'');
          }

          if (obj.imagepath != '') {
            imgpath = obj.imagepath;
            var src = $('#theImg').attr('src');
            if (src != imgpath) {
              //console.log("image = " + obj.imagepath);
              $('div#toastingnow > img').remove();
              $('#toastingnow').prepend('<img id="theImg" src="' + imgpath + '" />');
            }
          }

          if (obj.toastedfile != '') {
            toastedfilename = obj.toastedfile;
console.log('status toastedfilename: ' + toastedfilename);
          }
        }
      }, // end success
        error: function (jqXhr, textStatus, errorThrown) {
              console.log(jqXhr.responseText);
              if(jqXhr.responseText === undefined)
              {
                console.log("Toasting failed getting status undefined:");
                console.log(textStatus);
              }
              else{
                var x = jqXhr.responseText;
                var aError = x.split(',');
                console.log("Toasting failed getting status:: " + aError);
              
                if (Array.isArray(aError) == true)
                {
                  var amessage = '';
                  var message = '';
                  var adebug = '';
                  var debug = '';
                  if (typeof aError[1] !== "undefined") {
                    amessage = aError[1].split(':');

                    console.log("message = " + amessage[1]);
                    message = amessage[1];
                  }

                  if (typeof aError[2] !== "undefined") {
                    adebug = aError[2].split(':');
                    console.log("debug info = " + adebug[1]);
                    debug = adebug[1];
                  }
                  else {
                    console.log("Status = " + textStatus);
                  }
                }
                //alert($('#urlfield').val() + ": " + textStatus + ": " + message);
                console.log(errorThrown + ": " + $('#urlfield').val() + ": " + " " + message + ": " + debug);
              }
      }, // end error
 
    });  // end ajax

  }, 200
  );
} // end function startRefresh

    (function ($) {

      var listfurls = '';
      var splitlistofurls = new Array();
      var urlcount = 0;
      var pagecount = 0;
      var pgcounter = 0;


      function sendURL(thisd) {
        pgcounter = pagecount + 1;
        // update the form url one url at a time
        if($('#urlfield').val() != '')
          $('#urlfield').val(splitlistofurls[pagecount]);
        else
          return false;
        // carry on with the URL
        if(pgcounter <= urlcount)
          $('#toasturl').text("Toasting " + pgcounter + ' of ' + urlcount + ': ' + decodeURI(splitlistofurls[pagecount]));
        //console.log(pagecount + ": "+ splitlistofurls[pagecount]);

        $.ajax(
          {
            url: 'main.php',
            beforeSend: function () {
              $('body').addClass('busy');
              startRefresh(); // startRefresh timer
console.log("starting refresh status timer");
            },
            dataType: 'text',
            type: 'post',
            async: true,
            contentType: false,
            processData: false,
            data: new FormData(thisd),
            success: function (data, textStatus, jQxhr) {
console.log("main.php success - stopping refresh status timer");
              clearInterval(refreshStatus);
// console.log("success from main php");
              //$('html').html(data);

              if (urlcount == 1 || pgcounter == urlcount) {
                // only open in new windows if running single slice of toast
                //var x = window.open();
                //x.document.open();
                //x.document.write(data);
                //x.document.close();
//console.log("toasted page = " + toastedfilename);
                if(toastedfilename != '')
                {
                  var win = window.open(toastedfilename, '_blank');
                  win.focus();
                }
                else
                {
//console.log("done - resetting - toastedfilename was blank");
                pagecount = 0;
                urlcount = 0;
            //    $('#urlfield').val('');
                $('#toasturl').text('');
                $('body').removeClass('busy');
                $('#comment').val('');
                $('#harfile').val('');
                $('#status').text('Ready to Toast');
                $('#submit').removeAttr('disabled');
                $('#submit').removeClass("grey");
                return (false);
                }
              }
              //$('#response pre').html( data );
              //$('#stop').click();
              //console.log(thisd);
              //console.log("pagecount:" + pagecount);
              //console.log("urlcount: " + urlcount);

              if (pgcounter < urlcount) {
                var win = window.open(toastedfilename, '_blank');
                win.focus();
                win.addEventListener('load', function () { win.close(); }, false);

                $('div#toastingnow > img').remove();
                //console.log("done a page... next");
                $('#status').text('Ready to Toast');
                toastedfilename = '';
                pagecount = pagecount + 1;
                sendURL(thisd);
              }
              else {
                //console.log("done - resetting");
                pagecount = 0;
                urlcount = 0;
            //    $('#urlfield').val('');
                $('#toasturl').text('');
                $('body').removeClass('busy');
                $('#comment').val('');
                $('#harfile').val('');
                $('#status').text('Ready to Toast');
                $('div#toastingnow > img').remove();
                return (false);
              }


            },
            error: function (jqXhr, textStatus, errorThrown) {
              //alert("Error: Either PHP code has an error or URL is invalid!");
              //console.log(jqXhr.responseText);
              if(jqXhr.responseText === undefined)
              {
                console.log("Toasting failed submitting main: Either PHP code has an error or URL is invalid!");
                console.log(textStatus);
              }
              else{
                var x = jqXhr.responseText;
                var aError = x.split(',');
                console.log("Toasting failed submitting main: " + aError);
              
                if (Array.isArray(aError) == true)
                {
                  var amessage = '';
                  var message = '';
                  var adebug = '';
                  var debug = '';
                  if (typeof aError[1] !== "undefined") {
                    amessage = aError[1].split(':');

                    console.log("message = " + amessage[1]);
                    message = amessage[1];
                  }

                  if (typeof aError[2] !== "undefined") {
                    adebug = aError[2].split(':');
                    console.log("debug info = " + adebug[1]);
                    debug = adebug[1];
                  }
                  else {
                    console.log("Status = " + textStatus);
                  }
                }
                //alert($('#urlfield').val() + ": " + textStatus + ": " + message);
                console.log(errorThrown + ": " + $('#urlfield').val() + ": " + " " + message + ": " + debug);
              }
              // pagecount = 0;
              // urlcount = 0;
              $('#toasturl').text('');
              $('#status').text('Ready to Toast');
              $('#statusdetail').html("&nbsp;");
              $('body').removeClass('busy');
              $('#submit').removeAttr('disabled');
              $('#submit').removeClass("grey");
              $('#fileupdate img').attr("src", '');
              $('#comment').val('');
              $('#harfile').val('');
              $('div#toastingnow > img').remove();

// console.log("main.php error - stopping refresh status timer");
         //     clearInterval(refreshStatus);

              if(pgcounter < urlcount)
              {
                pagecount = pagecount + 1;
                sendURL(thisd);
              }
            },
            complete: function () {
//console.log("suburl test complete");
              $('#status').text('Ready to Toast');
              $('#statusdetail').html("&nbsp;");
              $('#fileupdate img').attr("src", '');
              $('#comment').val('');
              $('#harfile').val('');
              $('body').removeClass('busy');
              $('#submit').removeAttr('disabled');
              $('#submit').removeAttr('disabled');
              $('#submit').removeClass("grey");
              $('#statusdetail').html("&nbsp;");
              $('div#toastingnow > img').remove();
              //console.log("stopping refresh status timer");
         //     clearInterval(refreshStatus);
            }, // end complete
            always: function () {

            }
          }
        );
      } // end function sendURL

      function processForm(e) {
        if($("#ualist").prop('selectedIndex') == 0)
        {
          alert("Please select a device type");
          return false;
        }

        if (pagecount == 0) {
          e.preventDefault();
          $('#submit').attr("disabled", "true");
          $('#submit').addClass("grey");
          //console.log("submit pressed");
          // get full list of urls from input field
          listofurls = $('#urlfield').val();
          localStorage.setItem('urlfield', listofurls);
          var rchhsvr = $('#chremoteurlandport').val();
          if(rchhsvr != '')
            localStorage.setItem('chhserver', rchhsvr);
          //console.log(listofurls);

          if ($('#comment').val() != '')
            $('#comment').val($('#comment').val() + '; ');
          if ($('#cssimgs').is(':checked'))
            $('#comment').val($('#comment').val() + 'All available objects; ');
          if ($('#3pchain').is(':checked'))
            $('#comment').val($('#comment').val() + 'Third party call chain; ');
          if ($('#dbusage').is(':checked'))
            $('#comment').val($('#comment').val() + 'Third party database used; ');
          if ($('#debug').is(':checked'))
            $('#comment').val($('#comment').val() + 'Debug mode; ');
          if ($('#akdebug').is(':checked'))
            $('#comment').val($('#comment').val() + 'Akamai debug headers;');

          splitlistofurls = listofurls.split(/,\s*/);
          urlcount = splitlistofurls.length;

          if (urlcount > 1) {
            $('#toasturl').text("Multi Page");
            //console.log("Multi Page: "+ urlcount);
          }
          else {
            $('#toasturl').text("Single Page");
            //console.log("Single Page: " + urlcount);
          }

        }
        sendURL(this);


      } // end function processForm

      // MAIN jQUERY
      $('#form').submit(processForm);
    }
    )(jQuery);
  </script>
</body>

</html>
