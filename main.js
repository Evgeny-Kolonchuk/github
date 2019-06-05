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

function get_cookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function set_cookie(name, value, options) {
    options = options || {};

    var expires = options.expires;
    if (typeof(expires) == "number" && expires) {
        var current_date = new Date();
        current_date.setTime(current_date.getTime() + expires * 1000);
        expires = options.expires = current_date;
    }

    if (expires && expires.toUTCString)
    {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updated_cookie = name + "=" + value;

    for (var property_name in options)
    {
        if (!options.hasOwnProperty(property_name))
        {
            continue;
        }
        updated_cookie += "; " + property_name;
        var property_value = options[property_name];
        if (property_value !== true) {
            updated_cookie += "=" + property_value;
        }
    }

    document.cookie = updated_cookie;

    return true;
}

