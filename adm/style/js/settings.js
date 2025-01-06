/**
 * CloudFlare Turnstile extension for phpBB.
 * @author Maxim Maslich <mmaslich@gmail.com>
 * @copyright 2025 Maxim Maslich
 * @license GPL-2.0-only
 */

(function() {
	'use strict';

	// Element.closest() polifyll
	// https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#polyfill
	if (!Element.prototype.closest) {
		if (!Element.prototype.matches) {
			Element.prototype.matches = (Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector);
		}

		Element.prototype.closest = function(s) {
			let el = this;

			do {
				if (Element.prototype.matches.call(el, s)) {
					return el;
				}

				el = (el.parentElement || el.parentNode);
			} while (el !== null && el.nodeType === 1);

			return null;
		};
	}

	// Toggle Turnstile secret key
	document.body.addEventListener('click', function(e) {
		const toggle = e.target.closest('#toggle-turnstile-secret');

		if (!toggle) {
			return;
		}

		const field = document.body.querySelector('#turnstile-secret');
		const icon = toggle.querySelector('.icon');

		if (!field || !icon) {
			return;
		}

		const isHidden = (field.getAttribute('type').trim() === 'password');

		// Toggle field type
		field.setAttribute('type', (isHidden ? 'text' : 'password'));

		// Toggle icon
		icon.classList.toggle('fa-eye-slash');
		icon.classList.toggle('fa-eye');
	});
})();
