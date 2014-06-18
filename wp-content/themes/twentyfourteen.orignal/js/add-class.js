if(!window.application){
	window.application = {};
}

window.application.addClass = {
	config: {
		activeClass: 'active',
		animSpeed: 400,
		smHideClass: 'sm-hidden',
		mHideClass: 'm-hidden',
		sHideClass: 's-hidden',
		xlHideClass: 'xl-hidden',
		smVisibleClass: 'sm-visible',
		lVisibleClass: 'l-visible',
		hoverClass: 'hovered'
	},
	createElements: function(){
		var self = this, curItem;
		this.html = jQuery('html');
		this.footer = jQuery('#footer');
		this.win = jQuery(window);
		this.wrapper = jQuery('#wrapper');
		this.isTouchDevice = /MSIE 10.*Touch/.test(navigator.userAgent) || ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;
		this.productElem = {};
		// this.productElem.holder = this.html.find('.product-list, .account-list');
		this.productElem.holder = this.html.find('.product-list');
		this.productElem.items = this.productElem.holder.find('.hasdrop, > li').has('> .drop, > .drop-down');

		// if (this.productElem.items.filter('.' + this.config.hoverClass).length) {
		// 	curItem = this.productElem.items.filter('.' + this.config.hoverClass).removeClass(this.config.hoverClass).eq(0).addClass(this.config.hoverClass);
		// } else {
		// 	curItem = this.productElem.items.eq(0).addClass(this.config.hoverClass);
		// }
		if (this.productElem.items.add(this.html.find('.account-list').find('.hasdrop, > li').has('> .drop, > .drop-down')).filter('.' + this.config.hoverClass).length) {
			curItem = this.productElem.items.add(this.html.find('.account-list').find('.hasdrop, > li').has('> .drop, > .drop-down')).filter('.' + this.config.hoverClass).removeClass(this.config.hoverClass).eq(0).addClass(this.config.hoverClass);
		} else {
			curItem = this.productElem.items.add(this.html.find('.account-list').find('.hasdrop, > li').has('> .drop, > .drop-down')).eq(0).addClass(this.config.hoverClass);
		}

		setTimeout($.proxy(function(){
			this.updateDropHeight(curItem, this.html.find('.product-list, .account-list'));
		}, this), 100);

		this.moreElem = {};
		// this.moreElem.holder = this.html.find('.box.service, .box.manifest, .box.brand, .box.active-auctions, .box.types').has('.more');
		this.moreElem.holder = this.html.find('.box.service, .box.manifest, .box.brand, .box.types').has('.more');
		this.moreElem.items = this.moreElem.holder.find('> ul > li.' + this.config.smHideClass
								+ ', p.' + this.config.smHideClass
								+ ', p.' + this.config.mHideClass
								+ ', p.' + this.config.sHideClass
								+ ', .table-products > li.' + this.config.xlHideClass
								+ ', > ul > li.' + this.config.sHideClass);
		this.moreElem.btn = this.moreElem.holder.find('.more a, a.more');
		this.navElem = {};
		this.navElem.holder = this.html.find('#nav');
		this.navElem.opener = this.navElem.holder.find('.opener');
		this.navElem.drop = this.navElem.opener.siblings('.drop');
		this.loginElem = {};
		this.loginElem.opener = this.html.find('.register-panel .list-technical .login-link a');
		this.loginElem.drop = this.html.find('.btn-block').removeClass(this.config.lVisibleClass);
		this.searchElem = {};
		this.searchElem.opener = this.html.find('.register-panel .list-technical .search-link a');
		this.searchElem.drop = this.html.find('.box.form-block').has('.search-form').show();
		this.dropElem = {};
		this.dropElem.lists = this.html.find('.proposal .technical-list, .has-drops').has('.drop');
		this.dropElem.opener = this.dropElem.lists.find('li, .has-drops-holder').has('>.drop').find('>a');
		this.dropElem.drops = this.dropElem.lists.find('li > .drop, .has-drops-holder > .drop');
		this.dropElem.closeBtn = this.dropElem.drops.find('.close a');
		this.tableElem = {};
		this.tableElem.holder = this.html.find('.box.specification');
		this.tableElem.tableArr = [];
		this.tableElem.holder.each(function(){
			self.tableElem.tableArr.push(jQuery(this).find('table'));
		});
		this.formElem = {};
		this.formElem.close = jQuery('.btn-drop .errors .close a, .btn-drop .successes .close a');
		this.searchColsElem = {};
		this.searchColsElem.items = jQuery('.refine-search-form .col .search-box:last-child');
		this.subElem = {};
		this.subElem.holder = jQuery('.sub');
		this.subElem.opener = this.subElem.holder.find('> a');
		this.subElem.drop = this.subElem.holder.find('.drop');
		this.subElem.items = this.subElem.holder.find('input:text');
	},
	updateDropHeight: function(curItem, holder){
		holder = holder || this.productElem.holder;
		var curDrop = curItem.find('> .drop, > .drop-down');
		if (parseInt(curDrop.css('left')) !== 0) {
			curDrop.css('bottom', 'auto');
			if (holder.has(curDrop).is('.product-list')) {
				holder.has(curDrop).closest('div').css('min-height', curDrop.outerHeight(true) - 7);
			} else {
				holder.has(curDrop).css('min-height', curDrop.outerHeight(true));
			}
			curDrop.css('bottom', '');
		}
	},
	searchCols: function(type){
		if (type == 'orientationchange' || type == 'resize') return;
		var maxHeight = 0;
		this.searchColsElem.items.each(function(){
			var cur = jQuery(this),
				top = parseInt(cur.offset().top), height = parseInt(cur.height());
			cur.css({
				'padding-bottom': ''
			});
			cur.data({
				top: top,
				height: height
			})
			if (maxHeight <= height + top) {
				maxHeight = height + top;
			}
		});
		this.searchColsElem.items.each(function(){
			var cur = jQuery(this),
				top = cur.data('top'), height = cur.data('height'),
				paddingBottom = parseInt(cur.css('padding-bottom'));

			cur.css({
				'padding-bottom': paddingBottom + (maxHeight - (top + height))
			});
		});
	},
	attachListeners: function(){
		var self = this;
		jQuery(this.formElem.close.context).on({
			'click': function(event){
				event.preventDefault();
				jQuery(this).closest('.btn-drop').hide();
			}
		}, this.formElem.close.selector);
		this.dropElem.opener.on({
			'click': function(event){
				event.preventDefault();
				event.stopPropagation();
				self.dropElem.drops.not(jQuery(this).siblings('.drop').show()).hide();
				self.win.trigger('resize');
			},
			'touchstart': function(event){
				event.stopPropagation();
			}
		});
		this.dropElem.closeBtn.on({
			'click': function(event){
				event.preventDefault();
				event.stopPropagation();
				self.dropElem.drops.has(this).hide();
			}
		});
		this.dropElem.drops.on({
			'click touchstart': function(event){
				event.stopPropagation();
			}
		});
		self.searchCols('ready');
		this.win.on({
			'resize load ready orientationchange': function(event){
				if (self.searchElem.opener.is(':visible')) {
					if (self.searchElem.opener.hasClass(self.config.activeClass)) {
						self.searchElem.drop.show();
					} else {
						self.searchElem.drop.hide();
					}
				} else {
					self.searchElem.drop.show();
				}
				self.refreshTable();
				self.searchCols(event.type);
				if (parseInt(self.productElem.holder.find('> .drop, > .drop-down').css('left')) === 0) {
					self.productElem.holder.css('min-height', '');
					self.productElem.holder.filter('.product-list').closest('div').css('min-height', '');
				}
				self.updateDropHeight(self.productElem.items.add(self.html.find('.account-list').find('.hasdrop, > li').has('> .drop, > .drop-down')).filter('.' + self.config.hoverClass), self.html.find('.product-list, .account-list'));
			}
		});
		this.searchElem.opener.on({
			'click': function(event){
				event.preventDefault();
				var cur = jQuery(this);
				if (cur.hasClass(self.config.activeClass)) {
					cur.removeClass(self.config.activeClass);
					self.searchElem.drop.hide();
				} else {
					cur.addClass(self.config.activeClass);
					self.searchElem.drop.show();
					self.win.trigger('resize');
				}
			}
		});
		this.loginElem.opener.on({
			'click': function(event){
				event.stopPropagation();
				if (self.loginElem.drop.hasClass(self.config.lVisibleClass)) {
					self.loginElem.drop.removeClass(self.config.lVisibleClass);
				} else {
					self.loginElem.drop.addClass(self.config.lVisibleClass);
				}
			}
		});
		// this.moreElem.btn.on({
		// 	'click': function(event){
		// 		event.preventDefault();
		// 		self.moreElem.holder.has(this).find(self.moreElem.items.selector).removeClass(
		// 			self.config.smHideClass
		// 			+ ' ' + self.config.mHideClass
		// 			+ ' ' + self.config.sHideClass
		// 			+ ' ' + self.config.xlHideClass
		// 		);
		// 		jQuery(this).closest('.more').removeClass(self.config.smVisibleClass).hide();
		// 	}
		// });
		if (this.isTouchDevice) {
			this.productElem.items.on({
				'click': function(event){
					event.stopPropagation();
					var cur = jQuery(this),
						opener = cur.find('> a');
					if (cur.hasClass(self.config.hoverClass) && opener[0] == event.target) {
						cur.removeClass(self.config.hoverClass);
					} else {
						var curDrop = cur.find('> .drop, > .drop-down');
						if (parseInt(curDrop.css('left')) !== 0) {
							curDrop.css('bottom', 'auto');
							if (self.productElem.holder.has(this).is('.product-list')) {
								self.productElem.holder.has(this).closest('div').css('min-height', curDrop.outerHeight(true) - 7);
							} else {
								self.productElem.holder.has(this).css('min-height', curDrop.outerHeight(true));
							}
							curDrop.css('bottom', '');
						}
						self.productElem.items.removeClass(self.config.hoverClass).filter(this).addClass(self.config.hoverClass);
					}
				}
			}).find('> a').on({
				'click': function(event){
					event.preventDefault();
				}
			});
			this.subElem.opener.on({
				'click': function(event){
					event.preventDefault();
					event.stopPropagation();
					if (self.subElem.holder.has(this).hasClass(self.config.hoverClass)) {
						self.subElem.holder.has(this).removeClass(self.config.hoverClass);
					} else {
						self.subElem.holder.has(this).addClass(self.config.hoverClass);
					}
				},
				'touchstart': function(event){
					event.stopPropagation();
				}
			});
			this.navElem.opener.on({
				'click': function(event){
					event.preventDefault();
					event.stopPropagation();
					var curParent = jQuery(this).closest('li');
					if (curParent.hasClass(self.config.hoverClass)) {
						curParent.removeClass(self.config.hoverClass);
					} else {
						curParent.addClass(self.config.hoverClass);
					}
				},
				'touchstart': function(event){
					event.stopPropagation();
				}
			});
			this.navElem.drop.add(this.subElem.drop).on({
				'click touchstart': function(event){
					event.stopPropagation();
				}
			});
			this.html.on({
				'touchstart': function(){
					self.navElem.opener.closest('li').removeClass(self.config.hoverClass);
					self.dropElem.drops.hide();
					if (!self.subElem.items.filter(':focus').length) {
						self.subElem.holder.removeClass(self.config.hoverClass);
					}
				}
			});
		} else {
			this.subElem.holder.on({
				'click': function(event){
					event.stopPropagation();
				}
			});
			this.subElem.holder.on({
				'mouseenter': function(){
					jQuery(this).addClass(self.config.hoverClass);
				},
				'mouseleave': function(){
					if (!self.subElem.items.filter(':focus').length) {
						jQuery(this).removeClass(self.config.hoverClass);
					}
				}
			});
			this.navElem.opener = this.navElem.opener.closest('li');
			this.navElem.opener.on({
				'mouseenter': function(){
					jQuery(this).addClass(self.config.hoverClass);
				},
				'mouseleave': function(){
					jQuery(this).removeClass(self.config.hoverClass);
				}
			});
			this.productElem.func = function(){
				var curDrop = jQuery(this).find('> .drop, > .drop-down');
				if (parseInt(curDrop.css('left')) !== 0) {
					curDrop.css('bottom', 'auto');
					if (self.productElem.holder.has(this).is('.product-list')) {
						self.productElem.holder.has(this).closest('div').css('min-height', curDrop.outerHeight(true) - 7);
					} else {
						self.productElem.holder.has(this).css('min-height', curDrop.outerHeight(true));
					}
					curDrop.css('bottom', '');
				}
				self.productElem.items.removeClass(self.config.hoverClass).filter(this).addClass(self.config.hoverClass);
			};
			this.productElem.items.on({
				'mouseenter': this.productElem.func
			}).find('> a').on({
				'click': function(event){
					event.preventDefault();
					var curDrop = self.productElem.items.has(this).find('> .drop, > .drop-down');
					if (parseInt(curDrop.css('left')) !== 0) {
						curDrop.css('bottom', 'auto');
						if (self.productElem.holder.has(this).is('.product-list')) {
							self.productElem.holder.has(this).closest('div').css('min-height', curDrop.outerHeight(true) - 7);
						} else {
							self.productElem.holder.has(this).css('min-height', curDrop.outerHeight(true));
						}
						curDrop.css('bottom', '');
					}
					self.productElem.items.removeClass(self.config.hoverClass).has(this).addClass(self.config.hoverClass);
					self.productElem.items.off('mouseenter', self.productElem.func);
				}
			});
			this.html.on({
				'click': function(event){
					self.dropElem.drops.hide();
					if (!self.productElem.items.has(event.target).length) {
						self.productElem.items.on('mouseenter', self.productElem.func);
					}
					self.subElem.holder.removeClass(self.config.hoverClass);
				}
			});
		}
	},
	refreshTable: function(){
		var i = 0;
		for ( i = 0; i < this.tableElem.tableArr.length; i++ ) {
			var cur = this.tableElem.tableArr[i],
				maxHeight = 0;
			cur.find('.fake').detach();
			if (cur.closest('.col').css('float') != 'none') {
				cur.each(function(){
					var current = jQuery(this);
					if (current.height() > maxHeight) {
						maxHeight = current.height();
					}
				});
				cur.each(function(){
					var current = jQuery(this);
					if (current.height() < maxHeight) {
						current.append(current.find('tr').eq(0).clone().addClass('fake'));
						current.find('tr').eq(-1).find('td').empty();
						current.find('tr:last-child td').height(maxHeight - current.height());
					}
				});
			}
		}
	},
	init: function(){
		if(typeof jQuery !== 'function')
			return window.application;

		this.createElements();
		this.attachListeners();

		return window.application;
	}
}