var page = require('webpage').create();

page.onInitialized = function() {
  page.evaluate(function(domContentLoadedMsg) {
    document.addEventListener('DOMContentLoaded', function() {
      window.callPhantom('DOMContentLoaded');
    }, false);
  });
};

page.onCallback = function(data) {
  // your code here
  console.log('DOMContentLoaded');
  slimer.exit(0);
};

page.open('http://phantomjs.org/');
