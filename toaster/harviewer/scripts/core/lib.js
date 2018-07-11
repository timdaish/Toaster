require.def("core/lib",["core/trace"],function(a){var b={},c=navigator.userAgent.toLowerCase();b.isFirefox=/firefox/.test(c),b.isOpera=/opera/.test(c),b.isWebkit=/webkit/.test(c),b.isSafari=/webkit/.test(c),b.isIE=/msie/.test(c)&&!/opera/.test(c),b.isIE6=/msie 6/i.test(navigator.appVersion),b.browserVersion=(c.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)||[0,"0"])[1],b.isIElt8=b.isIE&&b.browserVersion-0<8,b.extend=function d(a,c){var d={};b.append(d,a),b.append(d,c);return d},b.append=function(a,b){for(var c in b)a[c]=b[c];return a},b.bind=function(){var a=b.cloneArray(arguments),c=a.shift(),d=a.shift();return function(){return c.apply(d,b.arrayInsert(b.cloneArray(a),0,arguments))}},b.bindFixed=function(){var a=b.cloneArray(arguments),c=a.shift(),d=a.shift();return function(){return c.apply(d,a)}},b.dispatch=function(b,c,d){for(var e=0;b&&e<b.length;e++){var f=b[e];if(f[c])try{f[c].apply(f,d)}catch(g){a.exception(g)}}},b.dispatch2=function(b,c,d){for(var e=0;e<b.length;e++){var f=b[e];if(f[c])try{var g=f[c].apply(f,d);if(g)return g}catch(h){a.exception(h)}}};var e=Object.prototype.toString,f=/^\s*function(\s+[\w_$][\w\d_$]*)?\s*\(/;b.isArray=function(a){return jQuery.isArray(a)},b.isFunction=function(a){if(!a)return!1;return e.call(a)==="[object Function]"||b.isIE&&typeof a!="string"&&f.test(""+a)},b.isAncestor=function(a,b){for(var c=a;c;c=c.parentNode)if(c==b)return!0;return!1},b.fixEvent=function(a){return jQuery.event.fix(a||window.event)},b.fireEvent=function(a,b){if(document.createEvent){var c=document.createEvent("Events");c.initEvent(b,!0,!1);return!a.dispatchEvent(c)}},b.cancelEvent=function(a){var c=b.fixEvent(a);c.stopPropagation(),c.preventDefault()},b.addEventListener=function(a,b,c,d){d=d||!1,a.addEventListener?a.addEventListener(b,c,d):a.attachEvent("on"+b,c)},b.removeEventListener=function(a,b,c,d){d=d||!1,a.removeEventListener?a.removeEventListener(b,c,d):a.detachEvent("on"+b,c)},b.isLeftClick=function(a){return a.button==0&&b.noKeyModifiers(a)},b.noKeyModifiers=function(a){return!a.ctrlKey&&!a.shiftKey&&!a.altKey&&!a.metaKey},b.isControlClick=function(a){return a.button==0&&b.isControl(a)},b.isShiftClick=function(a){return a.button==0&&b.isShift(a)},b.isControl=function(a){return(a.metaKey||a.ctrlKey)&&!a.shiftKey&&!a.altKey},b.isAlt=function(a){return a.altKey&&!a.ctrlKey&&!a.shiftKey&&!a.metaKey},b.isAltClick=function(a){return a.button==0&&b.isAlt(a)},b.isControlShift=function(a){return(a.metaKey||a.ctrlKey)&&a.shiftKey&&!a.altKey},b.isShift=function(a){return a.shiftKey&&!a.metaKey&&!a.ctrlKey&&!a.altKey},b.inflateRect=function(a,b,c){return{top:a.top-c,left:a.left-b,height:a.height+2*c,width:a.width+2*b}},b.pointInRect=function(a,b,c){return c>=a.top&&c<=a.top+a.height&&b>=a.left&&b<=a.left+a.width},b.cloneArray=function(a,b){var c=[];if(b)for(var d=0;d<a.length;++d)c.push(b(a[d]));else for(var d=0;d<a.length;++d)c.push(a[d]);return c},b.arrayInsert=function(a,b,c){for(var d=0;d<c.length;++d)a.splice(d+b,0,c[d]);return a},b.remove=function(a,b){for(var c=0;c<a.length;++c)if(a[c]==b){a.splice(c,1);return!0}return!1},b.formatSize=function(a){var b=1;b=b>2?2:b,b=b<-1?-1:b;if(b==-1)return a+" B";var c=Math.pow(10,b);return a==-1||a==undefined?"?":a==0?"0":a<1024?a+" B":a<1048576?Math.round(a/1024*c)/c+" KB":Math.round(a/1048576*c)/c+" MB"},b.formatTime=function(a){return a==-1?"-":a<1e3?a+"ms":a<6e4?Math.ceil(a/10)/100+"s":Math.ceil(a/6e4*100)/100+"m"},b.formatNumber=function(a){a+="";var b=a.split("."),c=b[0],d=b.length>1?"."+b[1]:"",e=/(\d+)(\d{3})/;while(e.test(c))c=c.replace(e,"$1 $2");return c+d},b.formatString=function(a){var c=b.cloneArray(arguments),a=c.shift();for(var d=0;d<c.length;d++){var e=c[d].toString();a=a.replace("%S",e)}return a},b.parseISO8601=function(a){var c=b.fromISOString(a);return c?c.getTime():null},b.fromISOString=function(a){if(!a)return null;var b=/(\d\d\d\d)(-)?(\d\d)(-)?(\d\d)(T)?(\d\d)(:)?(\d\d)(:)?(\d\d)(\.\d+)?(Z|([+-])(\d\d)(:)?(\d\d))/,c=new RegExp(b),d=a.toString().match(new RegExp(b));if(!d)return null;var e=new Date;e.setUTCDate(1),e.setUTCFullYear(parseInt(d[1],10)),e.setUTCMonth(parseInt(d[3],10)-1),e.setUTCDate(parseInt(d[5],10)),e.setUTCHours(parseInt(d[7],10)),e.setUTCMinutes(parseInt(d[9],10)),e.setUTCSeconds(parseInt(d[11],10)),d[12]?e.setUTCMilliseconds(parseFloat(d[12])*1e3):e.setUTCMilliseconds(0);if(d[13]!="Z"){var f=d[15]*60+parseInt(d[17],10);f*=d[14]=="-"?-1:1,e.setTime(e.getTime()-f*60*1e3)}return e},b.toISOString=function(a){function b(a,b){b||(b=2);var c=new String(a);while(c.length<b)c="0"+c;return c}var c=a.getUTCFullYear()+"-"+b(a.getMonth()+1)+"-"+b(a.getDate())+"T"+b(a.getHours())+":"+b(a.getMinutes())+":"+b(a.getSeconds())+"."+b(a.getMilliseconds(),3),d=a.getTimezoneOffset(),e=Math.floor(d/60),f=Math.floor(d%60),g=(d>0?"-":"+")+b(Math.abs(e))+":"+b(Math.abs(f));return c+g},b.getFileName=function(c){try{var d=b.splitURLBase(c);return d.name}catch(e){a.log(unescape(c))}return c},b.getFileExtension=function(a){if(!a)return null;var b=a.indexOf("?");b!=-1&&(a=a.substr(0,b));var c=a.lastIndexOf(".");return a.substr(c+1)},b.splitURLBase=function(a){if(b.isDataURL(a))return b.splitDataURL(a);return b.splitURLTrue(a)},b.isDataURL=function(a){return a&&a.substr(0,5)=="data:"},b.splitDataURL=function(a){var c=a.indexOf(":",3);if(c!=4)return!1;var d=a.indexOf(",",c+1);if(d<c)return!1;var e={encodedContent:a.substr(d+1)},f=a.substr(c+1,d),g=f.split(";");for(var h=0;h<g.length;h++){var i=g[h].split("=");i.length==2&&(e[i[0]]=i[1])}if(e.hasOwnProperty("fileName")){var j=decodeURIComponent(e.fileName),k=b.splitURLTrue(j);if(e.hasOwnProperty("baseLineNumber")){e.path=k.path,e.line=e.baseLineNumber;var l=decodeURIComponent(e.encodedContent.substr(0,200)).replace(/\s*$/,"");e.name="eval->"+l}else e.name=k.name,e.path=k.path}else e.hasOwnProperty("path")||(e.path="data:"),e.hasOwnProperty("name")||(e.name=decodeURIComponent(e.encodedContent.substr(0,200)).replace(/\s*$/,""));return e},b.splitURLTrue=function(a){var b=/:\/{1,3}(.*?)\/([^\/]*?)\/?($|\?.*)/,c=b.exec(a);return c?c[2]?{path:c[1],name:c[2]+c[3]}:{path:c[1],name:c[1]}:{name:a,path:a}},b.getURLParameter=function(a){var b=window.location.search.substring(1),c=b.split("&");for(var d=0;d<c.length;d++){var e=c[d].split("=");if(e[0]==a)return unescape(e[1])}return null},b.getURLParameters=function(a){var b=[],c=window.location.search.substring(1),d=c.split("&");for(var e=0;e<d.length;e++){var f=d[e].split("=");f[0]==a&&b.push(unescape(f[1]))}return b},b.parseURLParams=function(a){var c=a?a.indexOf("?"):-1;if(c==-1)return[];var d=a.substr(c+1),e=d.lastIndexOf("#");e!=-1&&(d=d.substr(0,e));if(!d)return[];return b.parseURLEncodedText(d)},b.parseURLEncodedText=function(a,c){function f(a){try{return decodeURIComponent(a)}catch(b){return decodeURIComponent(unescape(a))}}var d=25e3,e=[];if(a=="")return e;a=a.replace(/\+/g," ");var g=a.split("&");for(var h=0;h<g.length;++h)try{var i=g[h].indexOf("=");if(i!=-1){var j=g[h].substring(0,i),k=g[h].substring(i+1);k.length>d&&!c&&(k=b.$STR("LargeData")),e.push({name:f(j),value:f(k)})}else{var j=g[h];e.push({name:f(j),value:""})}}catch(l){}e.sort(function(a,b){return a.name<=b.name?-1:1});return e},b.getBody=function(a){if(a.body)return a.body;var b=a.getElementsByTagName("body")[0];if(b)return b;return null},b.getHead=function(a){return a.getElementsByTagName("head")[0]},b.getAncestorByClass=function(a,c){for(var d=a;d;d=d.parentNode)if(b.hasClass(d,c))return d;return null},b.$=function(){return b.getElementByClass.apply(this,arguments)},b.getElementByClass=function(a,c){if(!a)return null;var d=b.cloneArray(arguments);d.splice(0,1);for(var e=a.firstChild;e;e=e.nextSibling){var f=b.cloneArray(d);f.unshift(e);if(b.hasClass.apply(this,f))return e;var g=b.getElementByClass.apply(this,f);if(g)return g}return null},b.getElementsByClass=function(a,c){function f(a,c,d){for(var e=a.firstChild;e;e=e.nextSibling){var g=b.cloneArray(c);g.unshift(e),b.hasClass.apply(null,g)&&d.push(e),f(e,c,d)}}if(a.querySelectorAll){var d=b.cloneArray(arguments);d.shift();var e="."+d.join(".");return a.querySelectorAll(e)}var g=[],d=b.cloneArray(arguments);d.shift(),f(a,d,g);return g},b.getChildByClass=function(a){for(var c=1;c<arguments.length;++c){var d=arguments[c],e=a.firstChild;a=null;for(;e;e=e.nextSibling)if(b.hasClass(e,d)){a=e;break}}return a},b.eraseNode=function(a){while(a.lastChild)a.removeChild(a.lastChild)},b.clearNode=function(a){a.innerHTML=""},b.hasClass=function(a,b){if(a&&a.nodeType==1){for(var c=1;c<arguments.length;++c){var b=arguments[c],d=a.className;if(!d||d.indexOf(b+" ")==-1)return!1}return!0}return!1},b.setClass=function(a,c){a&&!b.hasClass(a,c)&&(a.className+=" "+c+" ")},b.removeClass=function(a,b){if(a&&a.className){var c=a.className.indexOf(b);if(c>=0){var d=b.length;a.className=a.className.substr(0,c-1)+a.className.substr(c+d)}}},b.toggleClass=function(a,c){if(b.hasClass(a,c)){b.removeClass(a,c);return!1}b.setClass(a,c);return!0},b.setClassTimed=function(a,c,d){d||(d=1300),a.__setClassTimeout?clearTimeout(a.__setClassTimeout):b.setClass(a,c),a.__setClassTimeout=setTimeout(function(){delete a.__setClassTimeout,b.removeClass(a,c)},d)},b.trim=function(a){return a.replace(/^\s*|\s*$/g,"")},b.wrapText=function(a,c){var d=/[^A-Za-z_$0-9'"-]/,e=[],f=100,g=b.splitLines(a);for(var h=0;h<g.length;++h){var i=g[h];while(i.length>f){var j=d.exec(i.substr(f,100)),k=f+(j?j.index:0),l=i.substr(0,k);i=i.substr(k),c||e.push("<pre>"),e.push(c?l:b.escapeHTML(l)),c||e.push("</pre>")}c||e.push("<pre>"),e.push(c?i:b.escapeHTML(i)),c||e.push("</pre>")}return e.join(c?"\n":"")},b.insertWrappedText=function(a,c,d){c.innerHTML="<pre>"+b.wrapText(a,d)+"</pre>"},b.splitLines=function(a){var b=/\r\n|\r|\n/;if(!a)return[];if(a.split)return a.split(b);var c=a+"",d=c.split(b);return d},b.getPrettyDomain=function(a){var b=/[^:]+:\/{1,3}(www\.)?([^\/]+)/.exec(a);return b?b[2]:""},b.escapeHTML=function(a){function b(a){switch(a){case"<":return"&lt;";case">":return"&gt;";case"&":return"&amp;";case"'":return"&#39;";case'"':return"&quot;"}return"?"}return String(a).replace(/[<>&"']/g,b)},b.cropString=function(a,c){a=a+"";if(c)var d=c/2;else var d=50;return a.length>c?b.escapeNewLines(a.substr(0,d)+"..."+a.substr(a.length-d)):b.escapeNewLines(a)},b.escapeNewLines=function(a){return a.replace(/\r/g,"\\r").replace(/\n/g,"\\n")},b.cloneJSON=function(b){if(b==null||typeof b!="object")return b;try{var c=b.constructor();for(var d in b)c[d]=this.cloneJSON(b[d]);return c}catch(e){a.exception(e)}return null},b.getOverflowParent=function(a){for(var b=a.parentNode;b;b=b.offsetParent)if(b.scrollHeight>b.offsetHeight)return b},b.getElementBox=function(a){var c={};if(a.getBoundingClientRect){var d=a.getBoundingClientRect(),e=b.isIE?document.body.clientTop||document.documentElement.clientTop:0,f=b.getWindowScrollPosition();c.top=Math.round(d.top-e+f.top),c.left=Math.round(d.left-e+f.left),c.height=Math.round(d.bottom-d.top),c.width=Math.round(d.right-d.left)}else{var g=b.getElementPosition(a);c.top=g.top,c.left=g.left,c.height=a.offsetHeight,c.width=a.offsetWidth}return c},b.getElementPosition=function(a){var b=0,c=0;do b+=a.offsetLeft,c+=a.offsetTop;while(a=a.offsetParent);return{left:b,top:c}},b.getWindowSize=function(){var a=0,b=0,c;typeof window.innerWidth=="number"?(a=window.innerWidth,b=window.innerHeight):(c=document.documentElement)&&(c.clientHeight||c.clientWidth)?(a=c.clientWidth,b=c.clientHeight):(c=document.body)&&(c.clientHeight||c.clientWidth)&&(a=c.clientWidth,b=c.clientHeight);return{width:a,height:b}},b.getWindowScrollSize=function(){var a=0,c=0,d;!b.isIEQuiksMode&&(d=document.documentElement)&&(d.scrollHeight||d.scrollWidth)&&(a=d.scrollWidth,c=d.scrollHeight),(d=document.body)&&(d.scrollHeight||d.scrollWidth)&&(d.scrollWidth>a||d.scrollHeight>c)&&(a=d.scrollWidth,c=d.scrollHeight);return{width:a,height:c}},b.getWindowScrollPosition=function(){var a=0,b=0,c;typeof window.pageYOffset=="number"?(a=window.pageYOffset,b=window.pageXOffset):(c=document.body)&&(c.scrollTop||c.scrollLeft)?(a=c.scrollTop,b=c.scrollLeft):(c=document.documentElement)&&(c.scrollTop||c.scrollLeft)&&(a=c.scrollTop,b=c.scrollLeft);return{top:a,left:b}},b.scrollIntoCenterView=function(a,c,d,e){if(a){c||(c=b.getOverflowParent(a));if(!c)return;var f=b.getClientOffset(a);if(!e){var g=f.y-c.scrollTop,h=c.scrollTop+c.clientHeight-(f.y+a.offsetHeight);if(g<0||h<0){var i=f.y-c.clientHeight/2;c.scrollTop=i}}if(!d){var j=f.x-c.scrollLeft,k=c.scrollLeft+c.clientWidth-(f.x+a.clientWidth);if(j<0||k<0){var l=f.x-c.clientWidth/2;c.scrollLeft=l}}}},b.getClientOffset=function(a){function b(a,c,d){var e=a.offsetParent,f=d.getComputedStyle(a,"");a.offsetLeft&&(c.x+=a.offsetLeft+parseInt(f.borderLeftWidth)),a.offsetTop&&(c.y+=a.offsetTop+parseInt(f.borderTopWidth)),e?e.nodeType==1&&b(e,c,d):a.ownerDocument.defaultView.frameElement&&b(a.ownerDocument.defaultView.frameElement,c,a.ownerDocument.defaultView)}var c={x:0,y:0};if(a){var d=a.ownerDocument.defaultView;b(a,c,d)}return c},b.addStyleSheet=function(a,c){if(!a.getElementById(c)){var d=a.createElement("link");d.type="text/css",d.rel="stylesheet",d.href=c,d.setAttribute("id",c);var e=b.getHead(a);e.appendChild(d)}},b.selectElementText=function(a,b,c){var d=window,e=d.document;if(d.getSelection&&e.createRange){var f=d.getSelection(),g=e.createRange();g.setStart(a,b),g.setEnd(a,c),f.removeAllRanges(),f.addRange(g)}else e.body.createTextRange&&(g=e.body.createTextRange(),g.moveToElementText(a),g.select())};return b})
