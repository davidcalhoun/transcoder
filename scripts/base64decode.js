// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com

onmessage = function (s) {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.indexOf(" chrome/") >= 0 || ua.indexOf(" firefox/") >= 0 || ua.indexOf(' gecko/') >= 0) {
    	var StringMaker = function () {
    		this.str = "";
    		this.length = 0;
    		this.append = function (s) {
    			this.str += s;
    			this.length += s.length;
    		}
    		this.prepend = function (s) {
    			this.str = s + this.str;
    			this.length += s.length;
    		}
    		this.toString = function () {
    			return this.str;
    		}
    	}
    } else {
    	var StringMaker = function () {
    		this.parts = [];
    		this.length = 0;
    		this.append = function (s) {
    			this.parts.push(s);
    			this.length += s.length;
    		}
    		this.prepend = function (s) {
    			this.parts.unshift(s);
    			this.length += s.length;
    		}
    		this.toString = function () {
    			return this.parts.join('');
    		}
    	}
    }
    
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    
    var input = s.data;

		var base64Index = input.indexOf('base64,');
    if(base64Index != -1) {
      input = input.substr(base64Index + 7, input.length);
    }
    
	var output = new StringMaker();
	var chr1, chr2, chr3;
	var enc1, enc2, enc3, enc4;
	var i = 0;
 
	// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
	while (i < input.length) {
		enc1 = keyStr.indexOf(input.charAt(i++));
		enc2 = keyStr.indexOf(input.charAt(i++));
		enc3 = keyStr.indexOf(input.charAt(i++));
		enc4 = keyStr.indexOf(input.charAt(i++));
 
		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;
 
		output.append(String.fromCharCode(chr1));
 
		if (enc3 != 64) {
			output.append(String.fromCharCode(chr2));
		}
		if (enc4 != 64) {
			output.append(String.fromCharCode(chr3));
		}
	}
 
	postMessage(output.toString());
}