/* Hjuviks Marina – Prislista: flikar och momsreglage för [hjuviks_prislista stil="flikar"].
 * Utan JavaScript visas alla grupper efter varandra med båda priserna. */
(function () {
	'use strict';

	function starta(el) {
		if (el.classList.contains('hjp-aktiv')) {
			return;
		}
		var flikar = [].slice.call(el.querySelectorAll('[role="tab"]'));
		var paneler = [].slice.call(el.querySelectorAll('[role="tabpanel"]'));
		var knappar = [].slice.call(el.querySelectorAll('[data-hjp-moms]'));
		var texter = [].slice.call(el.querySelectorAll('.hjp-f-momstext'));

		function valj(index, fokus) {
			flikar.forEach(function (flik, i) {
				var vald = i === index;
				flik.setAttribute('aria-selected', vald ? 'true' : 'false');
				flik.tabIndex = vald ? 0 : -1;
				if (paneler[i]) {
					paneler[i].hidden = !vald;
				}
			});
			if (fokus) {
				flikar[index].focus();
			}
		}

		function moms(lage) {
			el.setAttribute('data-moms', lage);
			knappar.forEach(function (knapp) {
				knapp.setAttribute('aria-pressed', knapp.getAttribute('data-hjp-moms') === lage ? 'true' : 'false');
			});
			texter.forEach(function (text) {
				text.textContent = text.getAttribute(lage === 'exkl' ? 'data-exkl' : 'data-inkl');
			});
		}

		flikar.forEach(function (flik, i) {
			flik.addEventListener('click', function () {
				valj(i, false);
			});
			flik.addEventListener('keydown', function (e) {
				var antal = flikar.length;
				var ny = null;
				if (e.key === 'ArrowRight') {
					ny = (i + 1) % antal;
				} else if (e.key === 'ArrowLeft') {
					ny = (i - 1 + antal) % antal;
				} else if (e.key === 'Home') {
					ny = 0;
				} else if (e.key === 'End') {
					ny = antal - 1;
				}
				if (ny !== null) {
					e.preventDefault();
					valj(ny, true);
				}
			});
		});

		knappar.forEach(function (knapp) {
			knapp.addEventListener('click', function () {
				moms(knapp.getAttribute('data-hjp-moms'));
			});
		});

		el.classList.add('hjp-aktiv');
		valj(0, false);
		moms('inkl');
	}

	function allt() {
		[].forEach.call(document.querySelectorAll('.hjp-flikar'), starta);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', allt);
	} else {
		allt();
	}
})();
