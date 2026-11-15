/* Hjuviks Marina – Prislista: flikar och momsreglage för [hjuviks_prislista stil="flikar"].
 * Utan JavaScript visas alla grupper efter varandra med båda priserna. */
(function () {
	'use strict';

	/* Flikbyte: panelens höjd glider från förra flikens höjd till den nyas, och raderna tonar in
	 * med en kort förskjutning. Bara transform, opacity och (som i ett dragspel) höjd.
	 * Minska rörelse: ingen höjd- eller rörelseanimering, bara en kort toning. */
	var EASE_OUT = 'cubic-bezier(0.23, 1, 0.32, 1)';
	var HOJD_MS = 240;
	var RAD_MS = 200;
	var RAD_STEG_MS = 25;
	var RAD_STEG_MAX_MS = 150;
	var minskaRorelse = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : { matches: false };

	function avbryt(panel) {
		(panel.hjpAnimationer || []).forEach(function (a) {
			a.cancel();
		});
		panel.hjpAnimationer = [];
		panel.style.overflow = '';
	}

	function tona(panel, franHojd) {
		if (typeof panel.animate !== 'function') {
			return;
		}
		var animationer = [];
		var rader = [].slice.call(panel.querySelectorAll('.hjp-f-kolumner, .hjp-f-rad'));

		if (minskaRorelse.matches) {
			animationer.push(panel.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 150, easing: 'ease' }));
			panel.hjpAnimationer = animationer;
			return;
		}

		var tillHojd = panel.getBoundingClientRect().height;
		if (franHojd && Math.abs(tillHojd - franHojd) > 1) {
			panel.style.overflow = 'hidden';
			var hojd = panel.animate(
				[{ height: franHojd + 'px' }, { height: tillHojd + 'px' }],
				{ duration: HOJD_MS, easing: EASE_OUT }
			);
			hojd.onfinish = function () {
				panel.style.overflow = '';
			};
			animationer.push(hojd);
		}

		rader.forEach(function (rad, i) {
			animationer.push(rad.animate(
				[{ opacity: 0, transform: 'translateY(4px)' }, { opacity: 1, transform: 'translateY(0)' }],
				{ duration: RAD_MS, delay: Math.min(i * RAD_STEG_MS, RAD_STEG_MAX_MS), easing: EASE_OUT, fill: 'backwards' }
			));
		});
		panel.hjpAnimationer = animationer;
	}

	function starta(el) {
		if (el.classList.contains('hjp-aktiv')) {
			return;
		}
		var flikar = [].slice.call(el.querySelectorAll('[role="tab"]'));
		var paneler = [].slice.call(el.querySelectorAll('[role="tabpanel"]'));
		var knappar = [].slice.call(el.querySelectorAll('[data-hjp-moms]'));
		var texter = [].slice.call(el.querySelectorAll('.hjp-f-momstext'));

		var aktivIndex = -1;

		function valj(index, fokus, animera) {
			if (index === aktivIndex) {
				if (fokus) {
					flikar[index].focus();
				}
				return;
			}
			var gammal = paneler[aktivIndex];
			// Nuvarande höjd, även mitt i en pågående animering, så att ett snabbt nytt klick fortsätter därifrån.
			var franHojd = animera && gammal ? gammal.getBoundingClientRect().height : 0;

			flikar.forEach(function (flik, i) {
				var vald = i === index;
				flik.setAttribute('aria-selected', vald ? 'true' : 'false');
				flik.tabIndex = vald ? 0 : -1;
				if (paneler[i]) {
					avbryt(paneler[i]);
					paneler[i].hidden = !vald;
				}
			});
			aktivIndex = index;

			if (animera && paneler[index]) {
				tona(paneler[index], franHojd);
			}
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
				valj(i, false, true);
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
					valj(ny, true, true);
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

	// Elementor-widgeten: starta om flikarna när widgeten ritas om i redigeraren.
	var kopplad = false;
	function koppla() {
		if (kopplad || !window.elementorFrontend || !window.elementorFrontend.hooks) {
			return;
		}
		kopplad = true;
		window.elementorFrontend.hooks.addAction('frontend/element_ready/hjuviks_prislista.default', function ($scope) {
			var rot = $scope && $scope[0] ? $scope[0] : null;
			if (rot) {
				[].forEach.call(rot.querySelectorAll('.hjp-flikar'), starta);
			}
		});
	}
	koppla();
	if (window.jQuery) {
		window.jQuery(window).on('elementor/frontend/init', koppla);
	}
})();
