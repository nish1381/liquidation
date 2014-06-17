/*--- custom checkbox's ---*/
(function($){
	"use strict";
	function CustomCheckbox(thisDOMObj, config){
		this.checkbox = jQuery(thisDOMObj);
		if(this.checkbox.data('CustomCheckbox') && typeof this.checkbox.data('CustomCheckbox')[config] === 'function'){ // call api function
			this.checkbox.data('CustomCheckbox')[config]();
		} else if(typeof config != 'string' && !this.checkbox.data('CustomCheckbox')){ // init custom checkbox
			// default options
			this.options = jQuery.extend({
				checkboxStructure: '<div></div>', // HTML struct for custom checkbox
				checkboxDisabled: 'disabled', // disabled class name
				checkboxDefault: 'checkboxArea', // default class name
				checkboxChecked: 'checkboxAreaChecked', // checked class name
				hideClass: 'outtaHere', // hide class for checkbox
				onInit: null, // oninit callback
				onChange: null // onchage callback
			}, config);

			this.init();
		}
		return this;
	}

	CustomCheckbox.prototype = {
		// init function
		init: function(){
			// add api in data checkbox
			this.checkbox.data('CustomCheckbox', this);

			this.createElements();
			this.createStructure();
			this.attachEvents();
			this.checkbox.addClass(this.options.hideClass);

			// init callback
			if(typeof this.options.onInit == 'function'){
				this.options.onInit(this.getUI());
			}
		},
		getUI: function(){
			return {
				checkbox: this.checkbox[0],
				fakecheckbox: this.fakecheckbox
			};
		},
		// attach events and listeners
		attachEvents: function(){
			this.clickEvent = this.bindScope(function(event){
				if(event.target != this.checkbox[0]){
					if (this.checkbox[0].checked) {
						this.checkbox.removeAttr('checked');
						this.checkbox[0].checked = false;
					} else {
						this.checkbox.attr('checked', 'checked');
						this.checkbox[0].checked = true;
					}
				}
				this.toggleState();
				// change callback
				if(typeof this.options.onChange == 'function'){
					this.options.onChange(event, this.getUI());
				}
			});
			this.fakeCheckbox.on({'click': this.clickEvent});
			this.checkbox.on({'click': this.clickEvent});
		},
		// checked or disabled checkbox
		toggleState: function(){
			this.fakeCheckbox.removeAttr('class').addClass(this.options[this.checkbox[0].checked ? 'checkboxChecked' : 'checkboxDefault']);
		},
		// create api elements
		createElements: function(){
			this.fakeCheckbox = jQuery(this.options.checkboxStructure);
		},
		// create custom checkbox struct
		createStructure: function(){
			if (this.checkbox.is(':disabled')) {
				this.fakeCheckbox.addClass(this.options.checkboxDisabled);
			} else if (this.checkbox.is(':checked')) {
				this.fakeCheckbox.addClass(this.options.checkboxChecked);
			} else {
				this.fakeCheckbox.addClass(this.options.checkboxDefault);
			}
			this.fakeCheckbox.insertBefore(this.checkbox);
		},
		// api update function
		update: function(){
			this.fakeCheckbox.detach();
			this.fakeCheckbox = jQuery(this.options.checkboxStructure);
			this.checkbox.off('click', this.clickEvent);
			this.createStructure();
			this.attachEvents();
			// init callback
			if(typeof this.options.onInit == 'function'){
				this.options.onInit(this.getUI(), true);
			}
		},
		// api destroy function
		destroy: function(){
			this.fakeCheckbox.detach();
			this.checkbox.removeClass(this.options.hideClass);
			this.checkbox.off('click', this.clickEvent);
			this.checkbox.removeData('CustomCheckbox');
		},
		bindScope: function(func, scope){
			return jQuery.proxy(func, scope || this);
		}
	};

	jQuery.fn.customCheckbox = function(config){
		return this.each(function(){
			new CustomCheckbox(this, config);
		});
	};
}(jQuery));