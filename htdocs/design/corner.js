/**
 * corner.js 1.0 (17-Apr-2007)
 * (c) by Christian Effenberger 
 * Inspired by reflection.js
 * Source: corner.netzgesta.de
 * Freely distributable under GPL
**/

function getImages(className){
	var children = document.getElementsByTagName('img'); 
	var elements = new Array(); var i = 0;
	var child; var classNames; var j = 0;
	for (i=0;i<children.length;i++) {
		child = children[i];
		classNames = child.className.split(' ');
		for (var j = 0; j < classNames.length; j++) {
			if (classNames[j] == className) {
				elements.push(child);
				break;
			}
		}
	}
	return elements;
}

function getClasses(classes,string){
	var temp = '';
	for (var j=0;j<classes.length;j++) {
		if (classes[j] != string) {
			if (temp) {
				temp += ' '
			}
			temp += classes[j];
		}
	}
	return temp;
}

function getClassValue(classes,string){
	var temp = 0; var pos = string.length;
	for (var j=0;j<classes.length;j++) {
		if (classes[j].indexOf(string) == 0) {
			temp = Math.min(classes[j].substring(pos),100);
			break;
		}
	}
	return Math.max(0,temp);
}

function getClassAttribute(classes,string){
	var temp = 0; var pos = string.length;
	for (var j=0;j<classes.length;j++) {
		if (classes[j].indexOf(string) == 0) {
			temp = 1; break;
		}
	}
	return temp;
}

/* From developer.mozilla.org/en/docs/Canvas_tutorial/ */
function roundedRect(ctx,x,y,width,height,radius,nopath){
	if (!nopath) ctx.beginPath();
	ctx.moveTo(x,y+radius);
	ctx.lineTo(x,y+height-radius);
	ctx.quadraticCurveTo(x,y+height,x+radius,y+height);
	ctx.lineTo(x+width-radius,y+height);
	ctx.quadraticCurveTo(x+width,y+height,x+width,y+height-radius);
	ctx.lineTo(x+width,y+radius);
	ctx.quadraticCurveTo(x+width,y,x+width-radius,y);
	ctx.lineTo(x+radius,y);
	ctx.quadraticCurveTo(x,y,x,y+radius);
	if (!nopath) ctx.closePath();
}

function addGradient(ctx,x,y,w,h,color,opacity) {
	var tmp = ctx.createLinearGradient(x,y,w,h);
	var val = (color!=0?0.25:0.35);
	tmp.addColorStop(0,'rgba('+color+','+color+','+color+',0.9)');
	tmp.addColorStop(val,'rgba('+color+','+color+','+color+','+opacity+')');
	tmp.addColorStop(0.75,'rgba('+color+','+color+','+color+',0)');
	tmp.addColorStop(1,'rgba('+color+','+color+','+color+',0)');
	return tmp;
}

function addShine(ctx,width,height,radius,opacity,extra) {
	var style; var color = (extra!=1?254:0);
	style = addGradient(ctx,0,radius,radius,radius,color,opacity);
	ctx.beginPath();
	ctx.moveTo(0,0);
	ctx.lineTo(0,height);	
	ctx.lineTo(radius,height);
	ctx.lineTo(radius,radius);		
	ctx.closePath();			
	ctx.fillStyle = style;
	ctx.fill();
	style = addGradient(ctx,radius,0,radius,radius,color,opacity);
	ctx.beginPath();
	ctx.moveTo(0,0);
	ctx.lineTo(width,0);	
	ctx.lineTo(width,radius);
	ctx.lineTo(radius,radius);		
	ctx.closePath();			
	ctx.fillStyle = style;
	ctx.fill();
}

function addShade(ctx,width,height,radius,opacity) {
	var style;
	style = addGradient(ctx,width,radius,width-radius,radius,0,opacity);
	ctx.beginPath();
	ctx.moveTo(width,0);
	ctx.lineTo(width,height);	
	ctx.lineTo(width-radius,height-radius);
	ctx.lineTo(width-radius,radius);		
	ctx.closePath();			
	ctx.fillStyle = style;
	ctx.fill();
	style = addGradient(ctx,radius,height,radius,height-radius,0,opacity);
	ctx.beginPath();
	ctx.moveTo(width,height);
	ctx.lineTo(0,height);	
	ctx.lineTo(radius,height-radius);
	ctx.lineTo(width-radius,height-radius);		
	ctx.closePath();			
	ctx.fillStyle = style;
	ctx.fill();
}

function addCorners() {
	var theimages = getImages('corner');
	var image; var object; var canvas; var context; var i;
	var iradius = null; var ishade = null; var ishadow = null;
	var inverse = null; var classes = ''; var newClasses = ''; 
	var maxdim = null; var style = null; var offset = null;
	for (i=0;i<theimages.length;i++) {	
		image = theimages[i];
		object = image.parentNode; 
		canvas = document.createElement('canvas');
		if (canvas.getContext) {
			classes = image.className.split(' ');
			iradius = getClassValue(classes,"iradius");
			ishadow = getClassValue(classes,"ishadow");
			ishade  = getClassValue(classes,"ishade");
			inverse = getClassAttribute(classes,"inverse");
			newClasses = getClasses(classes,"corner");
			canvas.className = newClasses;
			canvas.style.cssText = image.style.cssText;
			canvas.style.height = image.height+'px';
			canvas.style.width = image.width+'px';
			canvas.height = image.height;
			canvas.width = image.width;
			maxdim = Math.min(canvas.width,canvas.height)/2;
			iradius = Math.min(maxdim,iradius); offset = 4;
			offset = (ishadow>0?(inverse>0?0:Math.min(Math.max(offset,iradius/2),16)):0);
			context = canvas.getContext("2d");
			object.replaceChild(canvas,image);
			context.clearRect(0,0,canvas.width,canvas.height);
			context.save();
			if (ishadow>0 && inverse<=0) {
				ishadow = ishadow/100;
				if (iradius>0) {
					roundedRect(context,offset,offset,canvas.width-offset,canvas.height-offset,iradius*1.25);
				}else {
					offset = 8; 
					context.rect(0+offset,0+offset,canvas.width-offset,canvas.height-offset);
				}
				context.fillStyle = 'rgba(0,0,0,'+ishadow+')';
				context.fill();
			}
			globalCompositeOperation = "source-in";
			if (iradius<=0) {
				context.beginPath();
				context.rect(0,0,canvas.width-offset,canvas.height-offset);
				context.closePath();
			}else {
				roundedRect(context,0,0,canvas.width-offset,canvas.height-offset,iradius);
			}
			context.clip();
			context.fillStyle = 'rgba(0,0,0,0)';
			context.fillRect(0,0,canvas.width,canvas.height);
			context.drawImage(image,0,0,canvas.width-offset,canvas.height-offset);
			if (ishadow>0 && inverse>0) {
				ishadow = ishadow/100;
				if (iradius>0) {
					addShine(context,canvas.width,canvas.height,iradius,ishadow,1);
					roundedRect(context,0,0,canvas.width,canvas.height,iradius);
				}else {
					iradius = 16; 
					addShine(context,canvas.width,canvas.height,iradius,ishadow,1);
					context.beginPath();
					context.rect(0,0,canvas.width,canvas.height);
					context.closePath();
				}
				context.strokeStyle = 'rgba(0,0,0,'+ishadow+')';
				context.lineWidth = 2;
				context.stroke();
			}			
			if (ishade>0) {
				ishade = ishade/100;
				if (iradius<=0) iradius = 16; 
				addShade(context,canvas.width-offset,canvas.height-offset,iradius,ishade);
				addShine(context,canvas.width-offset,canvas.height-offset,iradius,ishade);
			}
		}
	}
}

var previousOnload = window.onload;
window.onload = function () { if(previousOnload) previousOnload(); addCorners(); }