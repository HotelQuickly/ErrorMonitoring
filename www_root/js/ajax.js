var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
var popped = ('state' in window.history && window.history.state !== null), initialURL = location.href;

function reinitialize() {
	/* ---------- Tooltip ---------- */
	$('[rel="tooltip"],[data-rel="tooltip"],[data-toggle="tooltip"]').tooltip({"placement":"top",delay: { show: 400, hide: 200 }});
	
	/* ---------- Popover ---------- */
	$('[rel="popover"],[data-rel="popover"]').popover();

	/* ---------- Datapicker ---------- */
	window.setTimeout(function(){
		$('.datepicker, .grid-datepicker').datepicker({ format: 'yyyy-mm-dd' });
	}, 200);

	/* ---------- Uniform ---------- */
	$("input:checkbox, input:file").not('[data-no-uniform="true"],#uniform-is-ajax').uniform();

	var init = function() {
		for (var i = 0; i < document.forms.length; i++)
					nette.initForm(document.forms[i]);
	};
	typeof jQuery === 'function' ? jQuery(init) : window.onload = init;

	/* ---------- Choosen ---------- */
	$('[data-rel="chosen"],[rel="chosen"]').chosen({no_results_text: "Oops, nothing found!"});
}

$(function () {
	$.ajaxSetup({
		cache: false,
		dataType: 'json'
	});

	$.nette.ext('spinner', {
		init: function () {
			this.spinner.appendTo('body');
		},
		start: function () {
			this.spinner.show(this.speed);
		},
		complete: function () {
			this.spinner.hide(this.speed);
		}
	}, {
		spinner: $('<div id="ajax-spinner" style="position: fixed;left: 50%;top: 50%;display: none;"><i class="icon-spinner icon-spin icon-large"></i></div>'),
		speed: undefined
	});

	$.nette.ext('confirm', {
		before: function (xhr, settings) {
			if (settings.nette !== undefined && settings.nette.el !== undefined) {

				var question = settings.nette.el.data('confirm');
				if (question) {
					return confirm(question);
				}
			}
		}
	});

	$.nette.ext('loadingButton', {
		before: function (xhr, settings) {
			if (settings.nette !== undefined && settings.nette.el !== undefined) {
				settings.nette.el.find("*[data-loading-text]").button('loading');
				settings.nette.el.closest("*[data-loading-text]").button('loading');
			}
		}
	});

	// Reinitialization of 
	$.nette.ext('reinitialization', {
		complete: function() {
			reinitialize();
		}
	});

	$.nette.init();

});

/**
 * AJAX Nette Framework plugin for jQuery
 *
 * @copyright Copyright (c) 2009, 2010 Jan Marek
 * @copyright Copyright (c) 2009, 2010 David Grudl
 * @copyright Copyright (c) 2012 Vojtěch Dobeš
 * @license MIT
 *
 * @version 1.2.2
 */
(function(window, $, undefined) {

if (typeof $ !== 'function') {
	return console.error('nette.ajax.js: jQuery is missing, load it please');
}

var nette = function () {
	var inner = {
		self: this,
		initialized: false,
		contexts: {},
		on: {
			init: {},
			load: {},
			prepare: {},
			before: {},
			start: {},
			success: {},
			complete: {},
			error: {}
		},
		fire: function () {
			var result = true;
			var args = Array.prototype.slice.call(arguments);
			var props = args.shift();
			var name = (typeof props === 'string') ? props : props.name;
			var off = (typeof props === 'object') ? props.off || {} : {};
			args.push(inner.self);
			$.each(inner.on[name], function (index, reaction) {
				if (reaction === undefined || $.inArray(index, off) !== -1) return true;
				var temp = reaction.apply(inner.contexts[index], args);
				return result = (temp === undefined || temp);
			});
			return result;
		},
		requestHandler: function (e) {
			if (!inner.self.ajax({}, this, e)) return;
		},
		ext: function (callbacks, context, name) {
			while (!name) {
				name = 'ext_' + Math.random();
				if (inner.contexts[name]) {
					name = undefined;
				}
			}

			$.each(callbacks, function (event, callback) {
				inner.on[event][name] = callback;
			});
			inner.contexts[name] = $.extend(context ? context : {}, {
				name: function () {
					return name;
				},
				ext: function (name, force) {
					var ext = inner.contexts[name];
					if (!ext && force) throw "Extension '" + this.name() + "' depends on disabled extension '" + name + "'.";
					return ext;
				}
			});
		}
	};

	/**
	 * Allows manipulation with extensions.
	 * When called with 1. argument only, it returns extension with given name.
	 * When called with 2. argument equal to false, it removes extension entirely.
	 * When called with 2. argument equal to hash of event callbacks, it adds new extension.
	 *
	 * @param  {string} Name of extension
	 * @param  {bool|object|null} Set of callbacks for any events OR false for removing extension.
	 * @param  {object|null} Context for added extension
	 * @return {$.nette|object} Provides a fluent interface OR returns extensions with given name
	 */
	this.ext = function (name, callbacks, context) {
		if (typeof name === 'object') {
			inner.ext(name, callbacks);
		} else if (callbacks === undefined) {
			return inner.contexts[name];
		} else if (!callbacks) {
			$.each(['init', 'load', 'prepare', 'before', 'start', 'success', 'complete', 'error'], function (index, event) {
				inner.on[event][name] = undefined;
			});
			inner.contexts[name] = undefined;
		} else if (typeof name === 'string' && inner.contexts[name] !== undefined) {
			throw "Cannot override already registered nette-ajax extension '" + name + "'.";
		} else {
			inner.ext(callbacks, context, name);
		}
		return this;
	};

	/**
	 * Initializes the plugin:
	 * - fires 'init' event, then 'load' event
	 * - when called with any arguments, it will override default 'init' extension
	 *   with provided callbacks
	 *
	 * @param  {function|object|null} Callback for 'load' event or entire set of callbacks for any events
	 * @param  {object|null} Context provided for callbacks in first argument
	 * @return {$.nette} Provides a fluent interface
	 */
	this.init = function (load, loadContext) {
		if (inner.initialized) throw 'Cannot initialize nette-ajax twice.';

		if (typeof load === 'function') {
			this.ext('init', null);
			this.ext('init', {
				load: load
			}, loadContext);
		} else if (typeof load === 'object') {
			this.ext('init', null);
			this.ext('init', load, loadContext);
		} else if (load !== undefined) {
			throw 'Argument of init() can be function or function-hash only.';
		}

		inner.initialized = true;

		inner.fire('init');
		this.load();
		return this;
	};

	/**
	 * Fires 'load' event
	 *
	 * @return {$.nette} Provides a fluent interface
	 */
	this.load = function () {
		inner.fire('load', inner.requestHandler);
		return this;
	};

	/**
	 * Executes AJAX request. Attaches listeners and events.
	 *
	 * @param  {object} settings
	 * @param  {Element|null} ussually Anchor or Form
	 * @param  {event|null} event causing the request
	 * @return {jqXHR|null}
	 */
	this.ajax = function (settings, ui, e) {
		if (!settings.nette && ui && e) {
			var $el = $(ui), xhr, originalBeforeSend;
			var analyze = settings.nette = {
				e: e,
				ui: ui,
				el: $el,
				isForm: $el.is('form'),
				isSubmit: $el.is('input[type=submit]') || $el.is('button[type=submit]'),
				isImage: $el.is('input[type=image]'),
				form: null
			};

			if (analyze.isSubmit || analyze.isImage) {
				analyze.form = analyze.el.closest('form');
			} else if (analyze.isForm) {
				analyze.form = analyze.el;
			}

			if (!settings.url) {
				settings.url = analyze.form ? analyze.form.attr('action') : ui.href;
			}
			if (!settings.type) {
				settings.type = analyze.form ? analyze.form.attr('method') : 'get';
			}

			if ($el.is('[data-ajax-off]')) {
				settings.off = $el.data('ajaxOff');
				if (typeof settings.off === 'string') settings.off = [settings.off];
			}
		}

		inner.fire({
			name: 'prepare',
			off: settings.off || {}
		}, settings);
		if (settings.prepare) {
			settings.prepare(settings);
		}

		originalBeforeSend = settings.beforeSend;
		settings.beforeSend = function (xhr, settings) {
			var result = inner.fire({
				name: 'before',
				off: settings.off || {}
			}, xhr, settings);
			if ((result || result === undefined) && originalBeforeSend) {
				result = originalBeforeSend(xhr, settings);
			}
			return result;
		};

		return this.handleXHR($.ajax(settings), settings);
	};

	/**
	 * Binds extension callbacks to existing XHR object
	 *
	 * @param  {jqXHR|null}
	 * @param  {object} settings
	 * @return {jqXHR|null}
	 */
	this.handleXHR = function (xhr, settings) {
		settings = settings || {};

		if (xhr && (typeof xhr.statusText === 'undefined' || xhr.statusText !== 'canceled')) {
			xhr.done(function (payload, status, xhr) {
				inner.fire({
					name: 'success',
					off: settings.off || {}
				}, payload, status, xhr);
			}).fail(function (xhr, status, error) {
				inner.fire({
					name: 'error',
					off: settings.off || {}
				}, xhr, status, error);
			}).always(function (xhr, status) {
				inner.fire({
					name: 'complete',
					off: settings.off || {}
				}, xhr, status);
			});
			inner.fire({
				name: 'start',
				off: settings.off || {}
			}, xhr, settings);
			if (settings.start) {
				settings.start(xhr, settings);
			}
		}
		return xhr;
	};
};

$.nette = new ($.extend(nette, $.nette ? $.nette : {}));

$.fn.netteAjax = function (e, options) {
	return $.nette.ajax(options || {}, this[0], e);
};

$.fn.netteAjaxOff = function () {
	return this.off('.nette');
};

$.nette.ext('validation', {
	before: function (xhr, settings) {
		if (!settings.nette) return true;
		else var analyze = settings.nette;
		var e = analyze.e;

		var validate = $.extend({
			keys: true,
			url: true,
			form: true
		}, settings.validate || (function () {
			if (!analyze.el.is('[data-ajax-validate]')) return;
			var attr = analyze.el.data('ajaxValidate');
			if (attr === false) return {
				keys: false,
				url: false,
				form: false
			}; else if (typeof attr === 'object') return attr;
		})() || {});

		var passEvent = false;
		if (analyze.el.attr('data-ajax-pass') !== undefined) {
			passEvent = analyze.el.data('ajaxPass');
			passEvent = typeof passEvent === 'bool' ? passEvent : true;
		}

		if (validate.keys) {
			// thx to @vrana
			var explicitNoAjax = e.button || e.ctrlKey || e.shiftKey || e.altKey || e.metaKey;

			if (analyze.form) {
				if (explicitNoAjax && analyze.isSubmit) {
					this.explicitNoAjax = true;
					return false;
				} else if (analyze.isForm && this.explicitNoAjax) {
					this.explicitNoAjax = false;
					return false;
				}
			} else if (explicitNoAjax) return false;
		}

		if (validate.form && analyze.form && !((analyze.isSubmit || analyze.isImage) && analyze.el.attr('formnovalidate') !== undefined)) {
			if (analyze.form.get(0).onsubmit && analyze.form.get(0).onsubmit(e) === false) {
				e.stopImmediatePropagation();
				e.preventDefault();
				return false;
			}
		}

		if (validate.url) {
			// thx to @vrana
			if (/:|^#/.test(analyze.form ? settings.url : analyze.el.attr('href'))) return false;
		}

		if (!passEvent) {
			e.stopPropagation();
			e.preventDefault();
		}
		return true;
	}
}, {
	explicitNoAjax: false
});

$.nette.ext('forms', {
	init: function () {
		var snippets;
		if (!window.Nette || !(snippets = this.ext('snippets'))) return;

		snippets.after(function ($el) {
			$el.find('form').each(function() {
				window.Nette.initForm(this);
			});
		});
	},
	prepare: function (settings) {
		var analyze = settings.nette;
		if (!analyze || !analyze.form) return;
		var e = analyze.e;
		var originalData = settings.data || {};
		var formData = {};

		if (analyze.isSubmit) {
			formData[analyze.el.attr('name')] = analyze.el.val() || '';
		} else if (analyze.isImage) {
			var offset = analyze.el.offset();
			var name = analyze.el.attr('name');
			var dataOffset = [ Math.max(0, e.pageX - offset.left), Math.max(0, e.pageY - offset.top) ];

			if (name.indexOf('[', 0) !== -1) { // inside a container
				formData[name] = dataOffset;
			} else {
				formData[name + '.x'] = dataOffset[0];
				formData[name + '.y'] = dataOffset[1];
			}
		}

		if (typeof originalData !== 'string') {
			originalData = $.param(originalData);
		}
		formData = $.param(formData);
		settings.data = analyze.form.serialize() + (formData ? '&' + formData : '') + '&' + originalData;
	}
});

// default snippet handler
$.nette.ext('snippets', {
	success: function (payload) {
		var snippets = [];
		var elements = [];
		if (payload.snippets) {
			for (var i in payload.snippets) {
				var $el = this.getElement(i);
				if ($el.get(0)) {
					elements.push($el.get(0));
				}
				$.each(this.beforeQueue, function (index, callback) {
					if (typeof callback === 'function') {
						callback($el);
					}
				});
				this.updateSnippet($el, payload.snippets[i]);
				$.each(this.afterQueue, function (index, callback) {
					if (typeof callback === 'function') {
						callback($el);
					}
				});
			}
			var defer = $(elements).promise();
			$.each(this.completeQueue, function (index, callback) {
				if (typeof callback === 'function') {
					defer.done(callback);
				}
			});
		}
	}
}, {
	beforeQueue: [],
	afterQueue: [],
	completeQueue: [],
	before: function (callback) {
		this.beforeQueue.push(callback);
	},
	after: function (callback) {
		this.afterQueue.push(callback);
	},
	complete: function (callback) {
		this.completeQueue.push(callback);
	},
	updateSnippet: function ($el, html, back) {
		if (typeof $el === 'string') {
			$el = this.getElement($el);
		}
		// Fix for setting document title in IE
		if ($el.is('title')) {
			document.title = html;
		} else {
			this.applySnippet($el, html, back);
		}
	},
	getElement: function (id) {
		return $('#' + this.escapeSelector(id));
	},
	applySnippet: function ($el, html, back) {
		if (!back && $el.is('[data-ajax-append]')) {
			$el.append(html);
		} else {
			$el.html(html);
		}
	},
	escapeSelector: function (selector) {
		// thx to @uestla (https://github.com/uestla)
		return selector.replace(/[\!"#\$%&'\(\)\*\+,\.\/:;<=>\?@\[\\\]\^`\{\|\}~]/g, '\\$&');
	}
});

// support $this->redirect()
$.nette.ext('redirect', {
	success: function (payload) {
		if (payload.redirect) {
			window.setTimeout( function() { $.nette.ajax(payload.redirect); }, 25 );
		}
	}
});

// current page state
$.nette.ext('state', {
	success: function (payload) {
		if (payload.state) {
			this.state = payload.state;
		}
	}
}, {state: null});

// abort last request if new started
$.nette.ext('unique', {
	start: function (xhr) {
		if (this.xhr) {
			this.xhr.abort();
		}
		this.xhr = xhr;
	},
	complete: function () {
		this.xhr = null;
	}
}, {xhr: null});

// option to abort by ESC (thx to @vrana)
$.nette.ext('abort', {
	init: function () {
		$('body').keydown($.proxy(function (e) {
			if (this.xhr && (e.keyCode.toString() === '27' // Esc
			&& !(e.ctrlKey || e.shiftKey || e.altKey || e.metaKey))
			) {
				this.xhr.abort();
			}
		}, this));
	},
	start: function (xhr) {
		this.xhr = xhr;
	},
	complete: function () {
		this.xhr = null;
	}
}, {xhr: null});

$.nette.ext('load', {
	success: function () {
		$.nette.load();
	}
});

// default ajaxification (can be overridden in init())
$.nette.ext('init', {
	load: function (rh) {
		$(this.linkSelector).off('click.nette', rh).on('click.nette', rh);
		$(this.formSelector).off('submit.nette', rh).on('submit.nette', rh)
			.off('click.nette', ':image', rh).on('click.nette', ':image', rh)
			.off('click.nette', ':submit', rh).on('click.nette', ':submit', rh);
		$(this.buttonSelector).closest('form')
			.off('click.nette', this.buttonSelector, rh).on('click.nette', this.buttonSelector, rh);
	}
}, {
	linkSelector: 'a.ajax, ',
	formSelector: 'form.ajax',
	buttonSelector: 'input.ajax[type="submit"], button.ajax[type="submit"], input.ajax[type="image"]'
});

})(window, window.jQuery);

(function($, undefined) {

// Is History API reliably supported? (based on Modernizr & PJAX)
if (!(window.history && history.pushState && window.history.replaceState && !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/))) return;

var findSnippets = function () {
	var result = [];
	$('[id^="snippet--"]').each(function () {
		var $el = $(this);
		result.push({
			id: $el.attr('id'),
			html: $el.html()
		});
	});
	return result;
};
var handleState = function (context, name, args) {
	var handler = context['handle' + name.substring(0, 1).toUpperCase() + name.substring(1)];
	if (handler) {
		handler.apply(context, args);
	}
};

(function($, undefined) {

// Is History API reliably supported? (based on Modernizr & PJAX)
if (!(window.history && history.pushState && window.history.replaceState && !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/))) return;

// thx to @ic (http://forum.nette.org/cs/profile.php?id=1985, http://forum.nette.org/cs/4405-flash-zpravicky-bez-fid-v-url#p43713)

$.nette.ext('fidRemove', {
	init: function () {
		var that = this;
		setTimeout(function () {
			var url = window.location.toString();
			var pos = url.indexOf('_fid=');
			if (pos !== -1) {
				window.history.replaceState({}, null, that.removeFid(url, pos));
			}
		}, this.timeout);
	}
}, {
	timeout: 2000,
	removeFid: function (url, pos) {
		url = url.substr(0, pos) + url.substr(pos + 9);
		if ((url.substr(pos - 1, 1) === '?') || (url.substr(pos - 1, 1) === '&')) {
			url = url.substr(0, pos - 1) + url.substr(pos);
		}
		return url;
	}
});

})(jQuery);

$.nette.ext('history', {
	init: function () {
		var snippetsExt;
		if (this.cache && (snippetsExt = $.nette.ext('snippets'))) {
			this.handleUI = function (domCache) {
				$.each(domCache, function () {
					snippetsExt.updateSnippet(this.id, this.html, true);
				});
				$.nette.load();
			};
		}

		history.replaceState(this.initialState = {
			nette: true,
			href: window.location.href,
			title: document.title,
			ui: findSnippets()
		}, document.title, window.location.href);

		setTimeout(function() {
			$(window).on('popstate.nette', $.proxy(function (e) {
				var initialPop = !popped && location.href == initialURL;
				popped = true;
				if (initialPop) {
					return;
				}

				var state = e.originalEvent.state || this.initialState;
				if (window.history.ready || !state || !state.nette) return;
				if (this.cache && state.ui) {
					handleState(this, 'UI', [state.ui]);
					handleState(this, 'title', [state.title]);
				} else {
					$.nette.ajax({
						url: state.href,
						off: ['history']
					});
				}
			}, this));
		}, 200);

	},
	before: function (xhr, settings) {
		if (!settings.nette) {
			this.href = null;
		} else if (!settings.nette.form) {
			this.href = settings.nette.ui.href;
		} else if (settings.nette.form.method == 'get') {
			this.href = settings.nette.ui.action || window.location.href;
		} else {
			this.href = null;
		}
	},
	success: function (payload) {
		var redirect = payload.redirect || payload.url; // backwards compatibility for 'url'
		if (redirect) {
			var regexp = new RegExp('//' + window.location.host + '($|/)');
			if ((redirect.substring(0,4) === 'http') ? regexp.test(redirect) : true) {
				this.href = redirect;
			} else {
				window.location.href = redirect;
			}
		}
		if (this.href && this.href != window.location.href) {
			history.pushState({
				nette: true,
				href: this.href,
				title: document.title,
				ui: findSnippets()
			}, document.title, this.href);
		}
		this.href = null;
	}
}, {
	href: null,
	cache: true,
	handleTitle: function (title) {
		document.title = title;
	}
});

})(jQuery);

