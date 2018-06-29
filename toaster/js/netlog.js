"use strict";
var page = require('webpage').create(),
    system = require('system'),
    address;
var outfilePD = 'c:\\temp\\PD'+'abc'; // windows     

if (system.args.length === 1) {
    console.log('Usage: netlog.js <some URL>');
    phantom.exit(1);
} else {
    address = system.args[1];



    page.onResourceRequested = function (req,response) {
        console.log('requested: ' + JSON.stringify(req, undefined, 4));
        var r = JSON.stringify(req);
   
        var path = outfilePD;
        var fs = require('fs');
        if (req.method == 'POST') 
        {
            console.log("POST to URL: " + req.url);
            console.log(JSON.stringify(req.postData));//dump
            console.log(req.postData['1']);//key is '1'
            console.log(req.postData['2']);//data is '2'
//            fs.write(path, "URL:" + req.url + "\r\n",'a');
//            fs.write(path, ",PostData:" +req.postData + "\r\n",'a');

            var pd = JSON.stringify({"URL": req.url, "PostData": req.postData});
            fs.write(path, pd,'a');
        }
    };

    page.onResourceReceived = function (res) {
   //     console.log('received: ' + JSON.stringify(res, undefined, 4));
    };

    page.open(address, function (status) {
        if (status !== 'success') {
            console.log('FAIL to load the address');
        }
	page.render('tmp/test.png');
        phantom.exit();
    });
}