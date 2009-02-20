window.onload = function()
{
	if (Cookie.Mint_SI_jrmint)
	{
		document.body.className = Cookie.Mint_SI_jrmint;
	};
	
	var uls = document.getElementsByTagName('ul');
	for (var i = 0; i < uls.length; i++)
	{
		uls[i].onclick = function()
		{
			var active = document.body.className;
			switch(active)
			{
				case 'ever':
					active = 'today';
				break;
				
				case 'today':
					active = 'hour';
				break;
				
				case 'hour':
					active = 'ever';
				break;
			}
			
			document.body.className = active;
			Cookie.bake('Mint_SI_jrmint', active)
		};
	};
};

var Cookie = function()
{
	var domain = function()
	{
		var domain = '.'+location.hostname.replace(/^www\./, '');
		// the following conditionals do nothing, JavaScript adds the . back when setting the cookie
		if (domain == '.localhost') { domain = 'localhost.local'; }
		else if (domain == '.127.0.0.1') { domain = '127.0.0.1'; };
		return domain;
	}();
	
	var cookie = 
	{
		bake : function(name, value)
		{
			this[name] = value;
			var year	= 365 * 24 * 60 * 60 * 1000;
			var expires = new Date;
			expires.setTime(expires.getTime() + year);
			document.cookie = name + "=" + value + ";expires=" + expires.toGMTString() + ";path=/;domain=" + domain;
		},

		toss : function(name)
		{
			if (this[name] != undefined)
			{
				delete this[name];
			}
			document.cookie = name + "=;expires=Thu, 01-Jan-70 00:00:00 GMT;path=/;domain=" + domain;
		}
	};
	
	var pairs = document.cookie.split(/;\s*/);
	for (var i in pairs)
	{
		var key_val = pairs[i].split('=');
		cookie[key_val[0]] = unescape(key_val[1]);
	};
	
	return cookie;
}();