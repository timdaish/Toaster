var doc = document.documentElement;
var toload = 0;
var loaded = 0;
var target = document.getElementById("svgout");

    
var createSVG = function (charsvg, ex, em) {
    var svgEl = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    var pathEl = document.createElementNS("http://www.w3.org/2000/svg", "path");

    pathEl.setAttribute('d', charsvg);
    pathEl.setAttribute('transform', 'translate(0,' + em + ') scale(1, -1)')
    pathEl.style.fill = 'currentColor';

    svgEl.setAttribute('viewBox', '0 0 ' + (ex*1+256) + ' ' + (em*1+256)); //FIXME
    svgEl.appendChild(pathEl);

    return svgEl;
}

doc.ondragover = function () {
    //this.className = 'hover';
    return false;
};
doc.ondragend = function () {
    //this.className = '';
    return false;
};
doc.ondrop = function (event) {
    event.preventDefault && event.preventDefault();
    this.className = '';

    // now do something with:
    var files = event.dataTransfer.files;


    toload = files.length;
    loaded = 0;
    target.innerHTML = '';

    p = document.createElement('p');

    a = document.createElement('a');
    a.href = "javascript:document.body.setAttribute('class',document.body.getAttribute('class')==='show-code'?'':'show-code');";
    a.innerText = "[code]";
    p.appendChild(a);

    target.appendChild(p);

    
    for (var x = 0; x < files.length; x++) {
        var file = files[x];
        displayFontDragDrop(file);

    }
    return false;
};


    // tell the embed parent frame the height of the content
    if (window.parent && window.parent.parent){
        console.log("height: " + document.body.getBoundingClientRect().height);
    window.parent.parent.postMessage(["resultsFrame", {
        height: document.body.getBoundingClientRect().height,
        slug: "r4ckgdc0"
    }], "*")
    }

console.log(jsfontfile);
// Construct a blob
var f = '' ; //new File([""], jsfontfile, {type: "image/svg+xml", lastModified: new Date(0)});
var fr = new FileReader();

var request = new XMLHttpRequest();
request.open('GET', jsfontfile, true);
request.responseType = 'blob';
request.onload = function() {
    var reader = new FileReader();
    reader.readAsText(request.response);
    reader.onload =  function(e){
        displayFontNamed(jsfontfile,e.target.result);
        //console.log(e.target.result);
    };
};

request.send();



// fr.onload = function(evt){
//     target.innerHTML = evt.target.result + "<br><a href="+URL.createObjectURL(f)+" download=" + f.name + ">Download " + f.name + "</a><br>type: "+f.type+"<br>last modified: "+ f.lastModifiedDate + "; filesize: " + f.size;

//  }
//   fr.readAsText(f);
//   var error = fr.error;
//   var text = fr.result;
//   document.getElementById("DisplayText").innerText="'"+error+"'"; /*<p id="DisplayText>*/
// fr.close;

function displayFontDragDrop(file)
{
//console.log('Font loading:', file.size, file.name, file.type);
                /*
            If the file is a font and the web browser supports FileReader,
            append css @font-face rule to the page; Change page`s font
        */
        
        
                if (typeof FileReader !== "undefined" && (/svg/i).test(file.type)) {
                    reader = new FileReader();
                    reader.type = file.type;
                    reader.onload = (function () {
                        return function (evt) {
                            var txt = evt.target.result;
                            if (window.DOMParser) {
                                parser = new DOMParser();
                                xmlDoc = parser.parseFromString(txt, "text/xml");
                            } else // Internet Explorer
                            {
                                xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                                xmlDoc.async = false;
                                xmlDoc.loadXML(txt);
                            }
                            var glyphs = xmlDoc.getElementsByTagName('glyph');
                            var src = '';
                            fontFamily = xmlDoc.getElementsByTagName('font-face')[0].getAttribute('font-family') || xmlDoc.getElementsByTagName('font')[0].id;
        
                            var g = document.createElement("h2");
                            g.innerText = fontFamily;
                            target.appendChild(g);
        
                            var ff = xmlDoc.getElementsByTagName('font-face')[0];
                            var em = ff.getAttribute('units-per-em');
                            var g = document.createElement("pre");
                            var fw = ff.getAttribute('font-weight');
                            var fwtext = '';
                            if(fw != 'null')
                            {
                                fww = "\nFont weight:" + ff.getAttribute('font-weight');
                                console.log("font has null weight");
                            }
                                g.innerText = em +  " font units per em (lower is better for filesize)\nNo.of glyphs = "+ glyphs.length + "\n";
                            target.appendChild(g);
        
                            src = '';
                            for (var n = 0; n < glyphs.length; n++) {
                                var glyph = glyphs[n];
                                if (glyph) {
                                    var char = glyph.getAttribute('unicode');
                                    var unicode = char ? (char.charCodeAt(0).toString(16)) : null;
                                    var charname = glyph.getAttribute('glyph-name');
                                    var charsvg = glyph.getAttribute('d');
                                    var ex = glyph.getAttribute('horiz-adv-x') || em;
                                    src = '<dt id="' + unicode + '" class="glyph"><a href="#' + unicode + '" class="char" id="svg-' + unicode + '"></a></dt>';
                                    src += '<dd class="char-code">' + unicode + '</dd>';
                                    if(charname != "null")
                                        src += '<dd class="char-name">' + charname + '</dd>';
                                    else
                                        src += '<dd class="char-name">' + unicode + '</dd>';
                                    var g = document.createElement("dl");
                                    g.innerHTML = src;
                                    target.appendChild(g);
                                    if (charsvg) {
                                        document.getElementById('svg-' + unicode).appendChild(createSVG(charsvg, ex, em));
                                    }
                                }
                            }
                        };
                    }());
                    reader.readAsText(file);
                }
}

function displayFontNamed(filepath,txt)
{
//console.log('Font loading:', filepath);
                /*
            If the file is a font and the web browser supports FileReader,
            append css @font-face rule to the page; Change page`s font
        */  

 //console.log("reading svg file: " + txt);
                            if (window.DOMParser) {
                                parser = new DOMParser();
                                xmlDoc = parser.parseFromString(txt, "text/xml");
                            } else // Internet Explorer
                            {
                                xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                                xmlDoc.async = false;
                                xmlDoc.loadXML(txt);
                            }
                            var glyphs = xmlDoc.getElementsByTagName('glyph');
                            var src = '';
                            fontFamily = xmlDoc.getElementsByTagName('font-face')[0].getAttribute('font-family') || xmlDoc.getElementsByTagName('font')[0].id;
        
                            var g = document.createElement("h2");
                            g.innerText = fontFamily;
                            target.appendChild(g);
                            // var gh = document.createElement("h3");
                            // gh.className = "glyph";
                            // for (var n = 20; n < 128; n++) {
                            //     var glyph = glyphs[n];
                            //     if (glyph) {
                            //         var char = glyph.getAttribute('unicode');
                            //         var unicode = char ? (char.charCodeAt(0).toString(16)) : null;
                            //         var charname = glyph.getAttribute('glyph-name');
                            //         var charsvg = glyph.getAttribute('d');
                            //         var ex = glyph.getAttribute('horiz-adv-x') || em;
                            //         gh.html += '<dt id="' + unicode + '>';
                            //         //console.log(char);
                                    
                            //     }
                            // }
                            // target.appendChild(gh);


                            var ff = xmlDoc.getElementsByTagName('font-face')[0];
                            var em = ff.getAttribute('units-per-em');
                            var g = document.createElement("pre");
                            var fw = ff.getAttribute('font-weight');
                            var fwtext = '';
                            if(fw != 'null')
                            {
                                fww = "\nFont weight:" + ff.getAttribute('font-weight');
                    //console.log("font has null weight");
                            }
                                g.innerText = em +  " font units per em (lower is better for filesize)\nNo.of glyphs = "+ glyphs.length + "\n";
                            target.appendChild(g);
        
                            src = '';
                            for (var n = 0; n < glyphs.length; n++) {
                                var glyph = glyphs[n];
                                if (glyph) {
                                    var char = glyph.getAttribute('unicode');
                                    var unicode = char ? (char.charCodeAt(0).toString(16)) : null;
                                    var charname = glyph.getAttribute('glyph-name');
                                    var charsvg = glyph.getAttribute('d');
                                    var ex = glyph.getAttribute('horiz-adv-x') || em;
                                    src = '<dt id="' + unicode + '" class="glyph"><a href="#' + unicode + '" class="char" id="svg-' + unicode + '"></a></dt>';
                                    src += '<dd class="char-code">' + unicode + '</dd>';
//console.log(charname);
                                    if(charname != null)
                                        src += '<dd class="char-name">' + charname + '</dd>';
                                    else
                                        src += '<dd class="char-name">' + unicode + '</dd>';
                                    var g = document.createElement("dl");
                                    g.innerHTML = src;
                                    target.appendChild(g);
                                    if (charsvg) {
                                        document.getElementById('svg-' + unicode).appendChild(createSVG(charsvg, ex, em));
                                    }
                                }
                            }

}