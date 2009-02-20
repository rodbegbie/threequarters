SI.Doorbell = 
{	
	feedback	: 0,
	doorbell	: null,
	flasher		: null,
	origin		: 1,
	repeat		: 2,
	unique		: 4,
	init		: function()
	{
		// audio
		this.doorbell = $('doorbell');
		this.doorbell.style.width = this.origin + 'px';
		var so = new SWFObject('pepper/shauninman/doorbell/doorbell.swf', 'doorbell_swf', '100%', 1, 7, '#000000');
		so.addParam('wmode', 'transparent');
		so.addVariable('unique', this.unique);
		so.addVariable('repeat', this.repeat);
		so.write('doorbell');
		
		window.setInterval(function() { SI.Doorbell.listen(); }, 2 * 1000);
		
		// visual
		this.flasher = $('doorbell-flasher');
		this.setOpacity(this.flasher, 0);
		this.flasher.style.backgroundColor = '#FFF';
		this.flasher.style.zIndex = 9999;
	},
	ring		: function(unique)
	{
		// audio
		if (this.feedback == 0 || this.feedback == 2)
		{
			var width = unique ? SI.Doorbell.unique : SI.Doorbell.repeat;
			this.doorbell.style.width = width + 'px';
			window.setTimeout(function() { SI.Doorbell.doorbell.style.width = SI.Doorbell.origin + 'px'; }, 50);
		};
		
		// visual
		if (this.feedback == 1 || this.feedback == 2)
		{
			var width	= -1;
			var height	= -1;
		
			var color	= unique ? '#BCE27F' : '#FFF';
		
			if (typeof window.innerWidth != "undefined")
			{
				width = window.innerWidth;
				height = window.innerHeight;
			}
			else if (document.documentElement && typeof document.documentElement.offsetWidth != "undefined" && document.documentElement.offsetWidth != 0)
			{
				width = document.documentElement.offsetWidth;
				height = document.documentElement.offsetHeight;
			}
			else if (document.body && typeof document.body.offsetWidth != "undefined")
			{
				width = d.body.offsetWidth;
				height = d.body.offsetHeight;
			};
		
			this.flasher.style.width			= width + 'px';
			this.flasher.style.height			= height + 'px';
			this.flasher.style.backgroundColor	= color;
			this.setOpacity(this.flasher, 100);
			this.flash();
		};
	},
	flash : function()
	{
		if (this.flasher.opacity > 0)
		{
			this.setOpacity(this.flasher, Math.floor(this.flasher.opacity / 2));
			window.setTimeout(function(){ SI.Doorbell.flash(); }, 100);
		}
		else
		{
			this.flasher.style.width			= 0 + 'px';
			this.flasher.style.height			= 0 + 'px';
		};
	},
	listen		: function()
	{
		var request = false;
		var now		= new Date();
		
		/*@cc_on @*/
		/*@if (@_jscript_version >= 5)
		try { request = new ActiveXObject("Msxml2.XMLHTTP"); } 
		catch (e) {
			try { request = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (E) { request = false; };
			};
		@end @*/
		if (!request && typeof XMLHttpRequest!='undefined')
		{
			request = new XMLHttpRequest();
		};
		if (!request)
		{
			return;
		};
		
		request.open('GET', 'pepper/shauninman/doorbell/lastvisit.txt', true);
		request.send(null);
		
		request.onreadystatechange = function()
		{
			if (request.readyState == 4 && request.status == 200)
			{
				var response	= request.responseText.split(',');
				var unique		= parseInt(response[0]);
				var lastvisit	= parseInt(response[1]);
				
				if (lastvisit > SI.Doorbell.lastvisit)
				{
					SI.Doorbell.lastvisit = lastvisit;
					SI.Doorbell.ring(unique);
				};
			};
		};
	},
	setOpacity : function(e, opacity)
	{
		opacity					= (opacity == 100) ? 99.999 : opacity;
		e.style.filter			= "alpha(opacity:"+opacity+")";
		e.style.KHTMLOpacity	= opacity/100;
		e.style.MozOpacity		= opacity/100;
		e.style.opacity			= opacity/100;
		
		// For retrieval purposes
		e.opacity				= opacity;
	}
};

/**
 * SWFObject v1.4.4: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2006 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * **SWFObject is the SWF embed script formerly known as FlashObject. The name was changed for
 *   legal reasons.
 */
if(typeof deconcept=="undefined"){var deconcept=new Object();}
if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}
if(typeof deconcept.SWFObjectUtil=="undefined"){deconcept.SWFObjectUtil=new Object();}
deconcept.SWFObject=function(_1,id,w,h,_5,c,_7,_8,_9,_a,_b){if(!document.getElementById){return;}
this.DETECT_KEY=_b?_b:"detectflash";
this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);
this.params=new Object();
this.variables=new Object();
this.attributes=new Array();
if(_1){this.setAttribute("swf",_1);}
if(id){this.setAttribute("id",id);}
if(w){this.setAttribute("width",w);}
if(h){this.setAttribute("height",h);}
if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}
this.installedVer=deconcept.SWFObjectUtil.getPlayerVersion();
if(c){this.addParam("bgcolor",c);}
var q=_8?_8:"high";
this.addParam("quality",q);
this.setAttribute("useExpressInstall",_7);
this.setAttribute("doExpressInstall",false);
var _d=(_9)?_9:window.location;
this.setAttribute("xiRedirectUrl",_d);
this.setAttribute("redirectUrl","");
if(_a){this.setAttribute("redirectUrl",_a);}};
deconcept.SWFObject.prototype={setAttribute:function(_e,_f){
this.attributes[_e]=_f;
},getAttribute:function(_10){
return this.attributes[_10];
},addParam:function(_11,_12){
this.params[_11]=_12;
},getParams:function(){
return this.params;
},addVariable:function(_13,_14){
this.variables[_13]=_14;
},getVariable:function(_15){
return this.variables[_15];
},getVariables:function(){
return this.variables;
},getVariablePairs:function(){
var _16=new Array();
var key;
var _18=this.getVariables();
for(key in _18){_16.push(key+"="+_18[key]);}
return _16;},getSWFHTML:function(){var _19="";
if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){
if(this.getAttribute("doExpressInstall")){
this.addVariable("MMplayerType","PlugIn");}
_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\"";
_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";
var _1a=this.getParams();
for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}
var _1c=this.getVariablePairs().join("&");
if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";
}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");}
_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\">";
_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";
var _1d=this.getParams();
for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}
var _1f=this.getVariablePairs().join("&");
if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}
return _19;
},write:function(_20){
if(this.getAttribute("useExpressInstall")){
var _21=new deconcept.PlayerVersion([6,0,65]);
if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){
this.setAttribute("doExpressInstall",true);
this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));
document.title=document.title.slice(0,47)+" - Flash Player Installation";
this.addVariable("MMdoctitle",document.title);}}
if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){
var n=(typeof _20=="string")?document.getElementById(_20):_20;
n.innerHTML=this.getSWFHTML();return true;
}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}
return false;}};
deconcept.SWFObjectUtil.getPlayerVersion=function(){
var _23=new deconcept.PlayerVersion([0,0,0]);
if(navigator.plugins&&navigator.mimeTypes.length){
var x=navigator.plugins["Shockwave Flash"];
if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}
}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}
catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}
catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}
catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}
return _23;};
deconcept.PlayerVersion=function(_27){
this.major=_27[0]!=null?parseInt(_27[0]):0;
this.minor=_27[1]!=null?parseInt(_27[1]):0;
this.rev=_27[2]!=null?parseInt(_27[2]):0;
};
deconcept.PlayerVersion.prototype.versionIsValid=function(fv){
if(this.major<fv.major){return false;}
if(this.major>fv.major){return true;}
if(this.minor<fv.minor){return false;}
if(this.minor>fv.minor){return true;}
if(this.rev<fv.rev){
return false;
}return true;};
deconcept.util={getRequestParameter:function(_29){
var q=document.location.search||document.location.hash;
if(q){var _2b=q.substring(1).split("&");
for(var i=0;i<_2b.length;i++){
if(_2b[i].substring(0,_2b[i].indexOf("="))==_29){
return _2b[i].substring((_2b[i].indexOf("=")+1));}}}
return "";}};
deconcept.SWFObjectUtil.cleanupSWFs=function(){if(window.opera||!document.all){return;}
var _2d=document.getElementsByTagName("OBJECT");
for(var i=0;i<_2d.length;i++){_2d[i].style.display="none";for(var x in _2d[i]){
if(typeof _2d[i][x]=="function"){_2d[i][x]=function(){};}}}};
deconcept.SWFObjectUtil.prepUnload=function(){__flash_unloadHandler=function(){};
__flash_savedUnloadHandler=function(){};
if(typeof window.onunload=="function"){
var _30=window.onunload;
window.onunload=function(){
deconcept.SWFObjectUtil.cleanupSWFs();_30();};
}else{window.onunload=deconcept.SWFObjectUtil.cleanupSWFs;}};
if(typeof window.onbeforeunload=="function"){
var oldBeforeUnload=window.onbeforeunload;
window.onbeforeunload=function(){
deconcept.SWFObjectUtil.prepUnload();
oldBeforeUnload();};
}else{window.onbeforeunload=deconcept.SWFObjectUtil.prepUnload;}
if(Array.prototype.push==null){
Array.prototype.push=function(_31){
this[this.length]=_31;
return this.length;};}
var getQueryParamValue=deconcept.util.getRequestParameter;
var FlashObject=deconcept.SWFObject;
var SWFObject=deconcept.SWFObject;