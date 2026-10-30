/* Hjuviks Marina – Prislista (admin) */
(function ($) {
	'use strict';

	var $form = $('#hjp-form');
	if (!$form.length) {
		return;
	}

	var counter = 0;
	var rowTpl = $('#hjp-row-template').html();
	var groupTpl = $('#hjp-group-template').html();

	function uid() {
		counter += 1;
		return 'n' + Date.now().toString(36) + counter;
	}

	function moms() {
		var v = parseFloat($('#hjp-moms').val());
		return isNaN(v) ? 0 : v;
	}

	function formatKr(n) {
		return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' kr';
	}

	function updateRow($row) {
		var raw = $row.find('.hjp-pris').val();
		var ex = parseFloat(String(raw).replace(',', '.'));
		$row.find('.hjp-inkl').text(raw === '' || isNaN(ex) ? '' : formatKr(ex * (1 + moms() / 100)));
	}

	function updateAll() {
		$form.find('.hjp-row').each(function () {
			updateRow($(this));
		});
	}

	function makeSortable($scope) {
		$scope.find('.hjp-rows').sortable({
			handle: '.hjp-handle',
			axis: 'y',
			placeholder: 'hjp-row-placeholder',
			helper: function (e, tr) {
				var $orig = tr.children();
				var $helper = tr.clone();
				$helper.children().each(function (i) {
					$(this).width($orig.eq(i).outerWidth());
				});
				return $helper;
			},
			change: function () {
				dirty = true;
			}
		});
	}

	var dirty = false;

	$('#hjp-groups').sortable({
		handle: '.hjp-group-handle',
		axis: 'y',
		placeholder: 'hjp-group-placeholder',
		change: function () {
			dirty = true;
		}
	});
	makeSortable($form);

	$form.on('input change', '.hjp-pris', function () {
		updateRow($(this).closest('.hjp-row'));
	});

	$form.on('input change', '#hjp-moms', updateAll);

	$form.on('input change', 'input, textarea', function () {
		dirty = true;
	});

	$form.on('click', '.hjp-add-row', function () {
		var $group = $(this).closest('.hjp-group');
		var html = rowTpl.split('__G__').join($group.data('g')).split('__R__').join(uid());
		var $row = $(html);
		$group.find('.hjp-rows').append($row);
		$row.find('.hjp-namn').trigger('focus');
		dirty = true;
	});

	$form.on('click', '.hjp-remove-row', function () {
		var $row = $(this).closest('.hjp-row');
		var name = $row.find('.hjp-namn').val();
		if (name && !window.confirm('Ta bort "' + name + '" från prislistan?')) {
			return;
		}
		$row.remove();
		dirty = true;
	});

	$form.on('click', '.hjp-add-group', function () {
		var g = uid();
		var html = groupTpl.split('__G__').join(g).split('__R__').join(uid());
		var $group = $(html);
		$('#hjp-groups').append($group);
		makeSortable($group);
		$group.find('.hjp-group-title').trigger('focus');
		dirty = true;
	});

	$form.on('click', '.hjp-remove-group', function () {
		var $group = $(this).closest('.hjp-group');
		var title = $group.find('.hjp-group-title').val() || 'gruppen';
		if (!window.confirm('Ta bort ' + title + ' och alla dess rader?')) {
			return;
		}
		$group.remove();
		dirty = true;
	});

	$form.on('submit', function () {
		dirty = false;
	});

	$(window).on('beforeunload', function () {
		if (dirty) {
			return 'Du har osparade ändringar i prislistan.';
		}
	});
})(jQuery);
