/* Hjuviks Marina – Dokument (admin): välj fil från mediebiblioteket, lägg till, ta bort och sortera rader. */
(function ($) {
	'use strict';

	var $lista = $('[data-lista]');
	var mall = $('#hjp-dok-mall').html();
	var raknare = $lista.children('[data-rad]').length;
	var osparat = false;

	function andrad() {
		osparat = true;
	}

	function visaFil($rad, fil) {
		var $namn = $rad.find('[data-filnamn]').empty();
		if (fil) {
			$('<a target="_blank" rel="noopener"></a>').attr('href', fil.url).text(fil.filename || fil.url.split('/').pop()).appendTo($namn);
			if (fil.filesizeHumanReadable) {
				$('<span class="hjp-dok-info"></span>').text(fil.filesizeHumanReadable).appendTo($namn);
			}
		} else {
			$('<span class="hjp-dok-tom"></span>').text('Ingen fil vald').appendTo($namn);
		}
		$rad.find('[data-fil]').val(fil ? fil.id : 0);
		$rad.find('[data-valj]').text(fil ? 'Byt fil' : 'Välj fil');
		$rad.find('[data-rensa]').prop('hidden', !fil);
	}

	$lista.on('click', '[data-valj]', function () {
		var $rad = $(this).closest('[data-rad]');
		var ram = wp.media({
			title: 'Välj eller ladda upp en fil',
			button: { text: 'Använd filen' },
			multiple: false
		});
		ram.on('select', function () {
			var fil = ram.state().get('selection').first().toJSON();
			visaFil($rad, fil);
			// Tom rubrik: föreslå filens titel.
			var $titel = $rad.find('.hjp-dok-titel');
			if (!$titel.val() && fil.title) {
				$titel.val(fil.title);
			}
			andrad();
		});
		ram.open();
	});

	$lista.on('click', '[data-rensa]', function () {
		visaFil($(this).closest('[data-rad]'), null);
		andrad();
	});

	$lista.on('click', '[data-tabort]', function () {
		var $rad = $(this).closest('[data-rad]');
		var titel = $rad.find('.hjp-dok-titel').val();
		if (!titel && $rad.find('[data-fil]').val() === '0') {
			$rad.remove();
			return;
		}
		// Ingen dialogruta: raden tonas ned och kan ångras fram till att man sparar.
		$rad.addClass('hjp-dok-borttagen').find('input').prop('disabled', true);
		$rad.find('[data-valj], [data-rensa], [data-tabort]').prop('hidden', true);
		$('<button type="button" class="button-link" data-angra>Ångra</button>').appendTo($rad);
		andrad();
	});

	$lista.on('click', '[data-angra]', function () {
		var $rad = $(this).closest('[data-rad]');
		$rad.removeClass('hjp-dok-borttagen').find('input').prop('disabled', false);
		$rad.find('[data-valj], [data-tabort]').prop('hidden', false);
		$rad.find('[data-rensa]').prop('hidden', $rad.find('[data-fil]').val() === '0');
		$(this).remove();
	});

	$('[data-ny]').on('click', function () {
		var $rad = $(mall.replace(/__I__/g, 'ny' + raknare++).trim());
		$lista.append($rad);
		$rad.find('.hjp-dok-titel').trigger('focus');
		andrad();
	});

	$lista.on('input', 'input', andrad);

	if ($.fn.sortable) {
		$lista.sortable({ handle: '.hjp-dok-dra', axis: 'y', placeholder: 'hjp-dok-plats', forcePlaceholderSize: true, update: andrad });
	}

	$('#hjp-dok-form').on('submit', function () {
		osparat = false;
	});

	window.addEventListener('beforeunload', function (e) {
		if (osparat) {
			e.preventDefault();
			e.returnValue = '';
		}
	});
})(jQuery);
