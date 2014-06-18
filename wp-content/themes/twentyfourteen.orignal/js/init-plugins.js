if(!window.application){
	window.application = {};
}

window.application.initPlugins = function(){
	'use strict';

	// init gallery function
	function initGallery(vert){
		gallery.find('.slider').css({
			'height': '',
			'marginLeft': '',
			'width': '',
			'marginTop': ''
		});
		gallery.scrollGallery({
			mask: '.holder',
			slider: '.slider',
			slides: '> li',
			btnPrev: 'a.prev',
			btnNext: 'a.next',
			disabledClass:'disabled',
			circularRotation: false,
			switchTime: 2000,
			step: 1,
			animSpeed: 600,
			vertical: vert ? true : false,
			onInit: function(){
				var self = this, flag = vert;
				if (!flag) {
					setTimeout(function(){
						self.slider.css({
							'height': '',
							'marginTop': ''
						});
					}, 100);
				} else {
					setTimeout(function(){
						self.slider.css({
							'width': '',
							'marginLeft': ''
						});
					}, 100);
				}
			}
		});
	}

	// init custom select
	if(typeof jQuery.fn.customSelect === 'function'){
		jQuery('select').customSelect({
			selectStructure: '<div class="selectArea"><div class="left"></div><div class="center"></div><a href="#" class="selectButton"><i class="ico">&nbsp;</i></a><div class="disabled"></div></div>',
			onChange: function(event, ui){
				var cur = jQuery(event.target), pairElem, curOptionVal = cur.find('option[selected]').attr('data-choice');
				curOptionVal = curOptionVal == 'null' ? false : true;
				if (cur.attr('data-pair')) {
					pairElem = jQuery(cur.attr('data-pair'));
					if (curOptionVal) {
						if (pairElem[0].disabled) {
							pairElem[0].disabled = false;
							pairElem.removeAttr('disabled');
							pairElem.customSelect('update');
						}
					} else {
						if (!pairElem[0].disabled) {
							pairElem[0].disabled = true;
							pairElem.attr('disabled', 'disabled');
							pairElem.customSelect('update');
						}
					}
				}
			},
			updateOnResize: true
		});
	}

	// init tabs
	if (typeof jQuery.fn.contentTabs == 'function') {
		jQuery('ul.tabset').contentTabs({
			effect: 'fade',
			event: 'click'
		});
	}

	// init scaling nav
	if (typeof initAutoScalingNav == 'function') {
		initAutoScalingNav({
			menuId: "site-list",
			flexible: true,
			equalLinks: false,
			sideClasses: true
		});
	}

	// fade gallery initialization
	if(typeof jQuery.fn.fadeGallery === 'function'){
		jQuery('.gallery-block').fadeGallery({
			slides: '.slideset > li',
			autoHeight: true,
			autoRotation: false,
			pagerLinks: '.thumbnails li',
			switchTime: 2000,
			animSpeed: 600
		});
		jQuery('.visual').fadeGallery({
			slides: '.slideset > li',
			autoHeight: true,
			autoRotation: true,
			switchTime: 2000,
			animSpeed: 600
		});
	}

	// scroll gallery init
	if(typeof jQuery.fn.scrollGallery === 'function'){
		var onWindowResize = function(){
			if (gallery.css('float') == 'none') {
				if (gallery.hasClass('gallery-js-ready') && gallery.hasClass('vertical')) {
					gallery.data('ScrollGallery').destroy();
					initGallery();
				} else if (!gallery.hasClass('gallery-js-ready')) {
					initGallery();
				}
			} else {
				if (gallery.hasClass('gallery-js-ready') && gallery.hasClass('horizontal')) {
					gallery.data('ScrollGallery').destroy();
					initGallery(true);
				} else if (!gallery.hasClass('gallery-js-ready')) {
					initGallery(true);
				}
			}
		}

		var gallery = jQuery('.thumbnails'),
			win = jQuery(window);
		win.on({
			'ready orientationchange load resize': onWindowResize
		});
	}

	// custom checkbox initialization
	if(typeof jQuery.fn.customCheckbox === 'function'){
		var checkboxListItems = jQuery('.credit-form, .new-letter, .refine-search-form, .info-form, .box.alt-layout'),
			checkboxListInputCollection = checkboxListItems.find('input[type="checkbox"]');
		checkboxListInputCollection.each(function(){
			var currentCheckbox = jQuery(this);
			if(currentCheckbox.is(':checked')){
				currentCheckbox.closest('li').addClass('active');
			}
		});
		checkboxListInputCollection.customCheckbox({
			onChange: function(){
				checkboxListInputCollection.each(function(){
					var currentCheckbox = jQuery(this),
						currentParent = currentCheckbox.closest('li');
					if(currentCheckbox.is(':checked')){
						currentParent.addClass('active');
					}
					else{
						currentParent.removeClass('active');
					}
				});
			}
		});
	}

	// background resize
	if(typeof BackgroundStretcher === 'object'){
		initBackgroundResize();
	}

	// replace custom forms
	if(typeof jcf === 'object' && typeof jcf.customForms === 'object'){
		jcf.lib.domReady(function(){
			jcf.customForms.replaceAll();
		});
	}

	// init open close
	if(typeof jQuery.fn.openClose === 'function'){
		jQuery('ul.products , .toggle-block').openClose({
			activeClass: 'expanded',
			opener: 'a.btn.orange, a.row-out',
			slider: 'li.slide, div.slide',
			animSpeed: 500,
			effect: 'none'
		});
	}

	// init placeholder
	if(typeof jQuery.fn.placeholder === 'function'){
		jQuery('input, textarea').placeholder();
	}

	// init fancybox
	if(typeof jQuery.fn.fancybox === 'function'){
		jQuery('.open-fancybox').fancybox();
	}
}