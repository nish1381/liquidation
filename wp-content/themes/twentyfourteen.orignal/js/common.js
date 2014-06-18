function initPage(){
	'use strict';

	if(!window.application){
		window.application = {};
	}

	// application initializaion starts
	var mainFuncName = 'init',
		subFuncArr = ['init'];
	window.application[mainFuncName] = function(){
		var i = null, j = null;
		for (i in this) {
			if (i != mainFuncName) {
				if (typeof this[i] === 'function') {
					try {
						this[i]();
					} catch (e) {
						console.log(e);
					}
				} else if (subFuncArr.length == 1 && typeof this[i][subFuncArr[0]] === 'function') {
					try {
						this[i][subFuncArr[0]]();
					} catch (e) {
						console.log(e);
					}
				} else {
					for (j = 0; j < subFuncArr.length; j++) {
						if (typeof this[i][subFuncArr[j]] === 'function') {
							try {
								this[i][subFuncArr[j]]();
							} catch (e) {
								console.log(e);
							}
						}
					}
				}
			}
		}
	};
	// application initialization ends

	window.application[mainFuncName]();
}

if(document.addEventListener){
	document.addEventListener('DOMContentLoaded', function(){
		initPage();
	}, false);
} else if(document.attachEvent){
	document.attachEvent('onreadystatechange', function(){
		if(document.readyState === "complete"){
			initPage();
		}
	});
}