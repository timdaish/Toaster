require.def("domplate/tableView",["domplate/domplate","core/lib","i18n!nls/tableView","domplate/domTree","core/trace"],function(Domplate,Lib,Strings,DomTree,Trace){with(Domplate){var TableView=domplate({className:"table",tag:DIV({"class":"dataTableSizer",tabindex:"-1"},TABLE({"class":"dataTable",cellspacing:0,cellpadding:0,width:"100%",role:"grid"},THEAD({"class":"dataTableThead",role:"presentation"},TR({"class":"headerRow focusRow dataTableRow subFocusRow",role:"row",onclick:"$onClickHeader"},FOR("column","$object.columns",TH({"class":"headerCell a11yFocus",role:"columnheader",$alphaValue:"$column.alphaValue"},DIV({"class":"headerCellBox"},"$column.label"))))),TBODY({"class":"dataTableTbody",role:"presentation"},FOR("row","$object.data|getRows",TR({"class":"focusRow dataTableRow subFocusRow",role:"row"},FOR("column","$row|getColumns",TD({"class":"a11yFocus dataTableCell",role:"gridcell"},TAG("$column|getValueTag",{object:"$column"})))))))),getValueTag:function(a){var b=typeof a;if(b=="object")return DomTree.Reps.Tree.tag;var c=DomTree.Reps.getRep(a);return c.shortTag||c.tag},getRows:function(a){var b=this.getProps(a);if(!b.length)return[];return b},getColumns:function(a){if(typeof a!="object")return[a];var b=[];for(var c=0;c<this.columns.length;c++){var d=this.columns[c].property;if(d)if(typeof a[d]==="undefined"){var e=typeof d=="string"?d.split("."):[d],f=a;for(var g in e)f=f&&f[e[g]]||undefined}else f=a[d];else f=a;b.push(f)}return b},getProps:function(a){if(typeof a!="object")return[a];if(a.length)return Lib.cloneArray(a);var b=[];for(var c in a){var d=a[c];this.domFilter(d,c)&&b.push(d)}return b},onClickHeader:function(a){var b=Lib.getAncestorByClass(a.target,"dataTable"),c=Lib.getAncestorByClass(a.target,"headerCell");if(c){var d=!Lib.hasClass(c,"alphaValue"),e=0;for(c=c.previousSibling;c;c=c.previousSibling)++e;this.sort(b,e,d)}},sort:function(a,b,c){var d=Lib.getChildByClass(a,"dataTableTbody"),e=Lib.getChildByClass(a,"dataTableThead"),f=[];for(var g=d.childNodes[0];g;g=g.nextSibling){var h=g.childNodes[b],i=c?parseFloat(h.textContent):h.textContent;f.push({row:g,value:i})}f.sort(function(a,b){return a.value<b.value?-1:1});var j=e.firstChild,k=Lib.getChildByClass(j,"headerSorted");Lib.removeClass(k,"headerSorted"),k&&k.removeAttribute("aria-sort");var l=j.childNodes[b];Lib.setClass(l,"headerSorted");if(l.sorted&&l.sorted!=1){Lib.removeClass(l,"sortedAscending"),Lib.setClass(l,"sortedDescending"),l.setAttribute("aria-sort","descending"),l.sorted=1;for(var m=f.length-1;m>=0;--m)d.appendChild(f[m].row)}else{Lib.removeClass(l,"sortedDescending"),Lib.setClass(l,"sortedAscending"),l.setAttribute("aria-sort","ascending"),l.sorted=-1;for(var m=0;m<f.length;++m)d.appendChild(f[m].row)}},getHeaderColumns:function(a){var b;for(var c in a){b=a[c];break}if(typeof b!="object")return[{label:Strings.objectProperties}];var d=[];for(var c in b){var e=b[c];if(!this.domFilter(e,c))continue;d.push({property:c,label:c,alphaValue:typeof e!="number"})}return d},domFilter:function(a,b){return!0},render:function(a,b,c){if(b){var d=[];for(var e=0;c&&e<c.length;e++){var f=c[e],g=typeof f.property!="undefined"?f.property:f,h=typeof f.label!="undefined"?f.label:g;d.push({property:g,label:h,alphaValue:!0})}d.length||(d=this.getHeaderColumns(b));try{this.columns=d;var i={data:b,columns:d},j=this.tag.append({object:i,columns:d},a),k=Lib.getElementByClass(j,"dataTableTbody"),l=200;l>0&&k.clientHeight>l&&(k.style.height=l+"px")}catch(m){Trace.exception("tableView.render; EXCEPTION "+m,m)}finally{delete this.columns}}}});return TableView}})
