BX.addCustomEvent('onAjaxSuccess', afterAjaxReload);

function afterAjaxReload() {
   //hide some fields
}

BX.addCustomEvent('onAjaxSuccessFinish', onAjaxSuccessFinish);

// ajax preloader
var preloader = BX('preloader');
window.BX.showWait = function(node, msg)
{
   BX.show(preloader);
}

window.BX.closeWait = function(node, obMsg)
{
   BX.hide(preloader);
}

// native document ready
document.addEventListener('DOMContentLoaded', function () {

})


// view object
function viewObject(name) 
{ 
	var obj = eval(name), i;

	if(!obj) 
	{ 
	alert("\""+name+"\" ia not an object"); 
	return; 
	}

	var w_Test = open("","Test","width=600,height=500,scrollbars=1");

	if(!w_Test) 
	{ 
	alert("Cannot open window for "+name); 
	return; 
	}

	w_Test.document.open();

	for(i in obj) 
	w_Test.document.write(name+"."+i+"="+obj[i]+"<br>");

	w_Test.document.close(); 
} 

// get params
var vars = {};
var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
   vars[key] = value;
});

