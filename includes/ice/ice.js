/*************************************************************************
*
* ADOBE SYSTEMS INCORPORATED
* Copyright 2008 Adobe Systems Incorporated
* All Rights Reserved.
*
* NOTICE:  Adobe permits you to use, modify, and distribute this file
* in accordance with the terms of the Adobe license agreement accompanying
* it. If you have received this file from a source other than Adobe, then
* your use, modification, or distribution of it requires the prior written
* permission of Adobe.
*
* AdobePatentID="B564"
* AdobePatentID="B565"
*
**************************************************************************/

ICE = {};

// Set the ICE.useConfig flag to false in case you do not want to use the ice.conf.js file or if
// this file is missing form your server. This should be done to avoid HTTP error code 404.
ICE.useConfig = true;

(function () {
	var isNotInIde = parent == window || ( parent != window && !(/ide\.htm/i).test(parent.location.href) );
	if (isNotInIde) {
		/*
		 * This setting defines the default shortcut for launching the InContext Editing Login
		 * dialog on any PC machine.
		 * 
		 * DO NOT MODIFY THIS CONSTANT!
		 * 
		 * Instead modify the ICE.USER_LOGIN_PC variable inside the ice.conf.js file.
		 */
		ICE.DEFAULT_LOGIN_PC = "CTRL+E";

		/*
		 * This setting defines the default shortcut for launching the InContext Editing Login
		 * dialog on any MAC machine.
		 * 
		 * DO NOT MODIFY THIS CONSTANT!
		 * 
		 * Instead modify the ICE.USER_LOGIN_MAC variable inside the ice.conf.js file.
		 */
		ICE.DEFAULT_LOGIN_MAC = "CMD+E";

		/**
		 * Gets the relative path based on the ice.js includes.
		 */
		ICE.getSiteRoot = function() {
			var searchPath = "includes/ice/ice.js";
			var s = null;
			var url = '';
			var scripts = document.getElementsByTagName("script");
			for (var i=0; i<scripts.length; i++) {
				url = scripts[i].src.toLowerCase();
				if (typeof url != "undefined") {
					if (url.indexOf(searchPath) != -1) {
						s = scripts[i].src;
						break;
					}
				}
			}

			s = s.substring(0, s.length - searchPath.length);

			if (!/http/i.test(s)) {
				// This Happens on IE
				var m = s.match(/\.\.\//g);
				var deep = m?m.length:0;
				var loca = window.location;
				var path = loca.pathname.replace(/\/([^\/]*)$/, '/');
				var temp = path.split('/');
				var newLength = temp.length - deep - 1;
				temp.length = (newLength >= 0) ? newLength : 0;
				path = temp.join('/') + '/';
				s = loca.protocol + '//'+ loca.hostname + (loca.port?':' + loca.port:'') + path;
			}

			return s;
		};

		/**
		 * Adds a link to the current configuration file to the page HEAD so the values in this file become available
		 */
		ICE.getConfig = function() {
			var scr = document.createElement('SCRIPT');
			scr.type = 'text/javascript';
			var head=document.getElementsByTagName('HEAD')[0];
			head.appendChild(scr);
			scr.onreadystatechange = scr.onload = function(e) {
				e = e || event;
				if (e.type == 'readystatechange') {
					if (typeof this.jsloaded == 'undefined' && (this.readyState == 'complete')){
						this.jsloaded = true;
						ICE.attachKeyEvent();
					}
				} else {
					ICE.attachKeyEvent();
				}
			};
			var siteRoot = ICE.getSiteRoot();
			scr.src = siteRoot + 'includes/ice/ice.conf.js';
		};

		/**
		 * This function begins the actual edit by pointing the browser to the new URL.
		 */
		ICE.startEdit = function() {
			var siteRoot = ICE.getSiteRoot();
			var siteRootParts = siteRoot.replace(/\/$/gi, "").split("/");

			var redirectTo = window.location.href;

			var pathname = window.location.pathname;

			//In webkit the pathname is decoded and the pound and space characters are unescaped.
			//Pound and space and ampersand must be escaped before applying encodeURIComponent on the page "hash parameter".
			var shouldBeEscapedArray=['#', ' ', '&'];
			var shouldBeEscapedRegexp = new RegExp("("+shouldBeEscapedArray.join("|")+")", "gi");
			var escapedPathname = pathname.replace(shouldBeEscapedRegexp, function(s, c) {
				return escape(c);
			});
			if (escapedPathname != pathname) {
				var l = window.location;
				redirectTo = l.protocol + '//' + l.host + escapedPathname + l.search + l.hash;
			}

			var redirecttoParts = redirectTo.split("/");

			// Remove the no of parts identical with the no of parts from siteRoot => will obtain the part after the site root form the URL
			redirecttoParts.splice(0, siteRootParts.length);
			redirectTo = redirecttoParts.join("/");

			redirectTo = siteRoot + 'includes/ice/ide.html#page=' + encodeURIComponent(redirectTo);

			// Do the actual redirect
			window.location.href = redirectTo;
		};

		/**
		 * This function attaches key events so we catch the combination that will launch the InContext Editing Login UI
		 */
		ICE.attachKeyEvent = function() {
			var platform = navigator.userAgent.match(/mac/i) ? "MAC" : "PC";
			var shortcut = ICE['DEFAULT_LOGIN_' + platform];

			if (typeof ICE['USER_LOGIN_' + platform] == 'string') {
				shortcut = ICE['USER_LOGIN_' + platform];
			}

			if (shortcut !== '' && shortcut.indexOf('+') != -1) {
				ICE.shortcut = {
					ctrlKey: shortcut.indexOf('CTRL+') != -1,
					altKey: shortcut.indexOf('ALT+') != -1,
					metaKey: shortcut.indexOf('CMD+') != -1,
					shiftKey: shortcut.indexOf('SHIFT+') != -1,
					keyCode: shortcut.slice(-1).charCodeAt(0),
					platform: platform
				};

				ICE.isShortcutPressed = function(event) {
					var retVal = true;
					retVal = retVal && (ICE.shortcut.ctrlKey ? event.ctrlKey : !event.ctrlKey);
					if (ICE.shortcut.platform == "MAC") {
						retVal = retVal && (ICE.shortcut.metaKey ? event.metaKey : !event.metaKey);
					}
					retVal = retVal && (ICE.shortcut.altKey ? event.altKey : !event.altKey);
					retVal = retVal && (ICE.shortcut.shiftKey ? event.shiftKey : !event.shiftKey);
					retVal = retVal && (ICE.shortcut.keyCode == event.keyCode);
					return retVal;
				};

				ICE.onkeydown = function (e) {
					e = e || event;
					if (ICE.isShortcutPressed(e)) {
						//stop keyboard shortcut and redirect to ICE IDE
						try{e.cancelBubble = true;}catch(err){}
						try{e.returnValue = false;}catch(err){}
						try{e.stopPropagation();}catch(err){}
						try{e.preventDefault();}catch(err){}
						ICE.startEdit();
						return false;
					}
				};

				if (document.addEventListener) {
					document.addEventListener('keydown', ICE.onkeydown, false);
				} else if (document.attachEvent) {
					document.attachEvent('onkeydown', ICE.onkeydown);
				} else {
					document.onkeydown = ICE.onkeydown;
				}
			}
		};

		/**
		 * Depending on the current setting - to use or not the config file - we'll decide to read the config or not
		 */
		if (ICE.useConfig) {
			// If the site owner has decided to use the ice.conf.js we'll go ahead and read it
			ICE.getConfig();
		} else {
			// Otherwise we'll go with the defaults
			ICE.attachKeyEvent();
		}
	}
})();
