/*!
 * Simple Menu Order Column
 *
 * https://github.com/ChillCode/simple-menu-order-column/
 *
 * Copyright (C) 2024 ChillCode
 *
 * @license Released under the General Public License v3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */
(function ($) {
	$.fn.smocDoReorder = function (currentObject) {

		const reorderCurrentProduct = this;

		if (!reorderCurrentProduct || $(currentObject).prop('disabled')) {
			return false;
		}

		function disableInput(errorContainer, errorMessage, disable) {
			/** Reset input value. */
			currentObject.value = currentObject.defaultValue;
			/** Show error icon. */
			errorContainer && errorContainer.css('display', 'inline-block');
			/** Disable input field. */
			$(currentObject).prop('disabled', disable).prop('title', errorMessage);
			/** Output message to widow console. */
			window.console.warn('[Simple Menu Order Column] ' + errorMessage);
		};

		let reorderPostID = $(reorderCurrentProduct).data('post-id');

		if (!reorderPostID || isNaN(reorderPostID)) {
			disableInput(null, 'Invalid Post ID.', true);
			return false;
		}

		reorderPostID = parseInt(reorderPostID);

		/**
		 * Create loader and result containers.
		 */
		const reorderLoaderID = 'smoc-' + reorderPostID.toString();

		const reorderResultContainer = $(reorderCurrentProduct).closest('.smoc-container');

		/**
		 * Create loader.
		 */
		let reorderLoaderSelector = $('#' + reorderLoaderID + '-loader');

		if (!reorderLoaderSelector.length) {
			reorderLoaderSelector = $('<span>')
				.attr({
					id: reorderLoaderID + '-loader',
					class: 'smoc-loader dashicons dashicons-update',
					role: 'img',
					'aria-label': 'Updating Menu Order',
				})
				.css({
					color: '#2ea2cc',
					animation: 'iconrotation 2s infinite linear',
					display: 'inline-block',
				});
		}

		let reorderLoaderSelectorContainer = $(
			'#' + reorderLoaderID + '-loader-container'
		);

		if (!reorderLoaderSelectorContainer.length) {
			reorderLoaderSelectorContainer = $('<div>')
				.attr({
					id: reorderLoaderID + '-loader-container',
				})
				.css({
					'padding-top': '5px',
					display: 'none',
				});

			reorderLoaderSelectorContainer.append(reorderLoaderSelector);

			reorderResultContainer.append(reorderLoaderSelectorContainer);
		} else {
			reorderLoaderSelectorContainer.css({ display: 'none' });
		}

		/**
		 * Create Success result.
		 */

		let reorderSuccessSelector = $('#' + reorderLoaderID + '-success');

		if (!reorderSuccessSelector.length) {
			reorderSuccessSelector = $('<span>')
				.attr({
					id: reorderLoaderID + '-success',
					class: 'smoc-success dashicons dashicons-yes-alt',
					role: 'img',
					'aria-label': 'Success',
				})
				.css({
					'padding-top': '5px',
					color: '#7ad03a',
					display: 'none',
				});

			reorderResultContainer.append(reorderSuccessSelector);
		} else {
			reorderSuccessSelector.css({ display: 'none' });
		}

		/**
		 * Create Error result.
		 */

		let reorderErrorSelector = $('#' + reorderLoaderID + '-error');

		if (!reorderErrorSelector.length) {
			reorderErrorSelector = $('<span>')
				.attr({
					id: reorderLoaderID + '-error',
					class: 'smoc-error dashicons dashicons-dismiss',
					role: 'img',
					'aria-label': 'Error',
				})
				.css({
					'padding-top': '5px',
					color: '#a00',
					display: 'none',
				});

			reorderResultContainer.append(reorderErrorSelector);
		} else {
			reorderErrorSelector.css({ display: 'none' });
		}

		/**
		 * Check WP configuration.
		 */
		if (!typenow || !ajaxurl) {
			disableInput(reorderErrorSelector, 'Invalid WP installation, variables typenow or ajaxurl not initialized.', true);
			return false;
		}

		let reorderPostMenuOrder = $(reorderCurrentProduct).val();

		/**
		 * Populate and validate Product Order
		 */
		reorderPostMenuOrder = $(reorderCurrentProduct).val();

		if (!reorderPostMenuOrder || isNaN(reorderPostMenuOrder)) {
			disableInput(reorderErrorSelector, 'Invalid menu order value.', false);
			return false;
		}

		reorderPostMenuOrder = parseInt(reorderPostMenuOrder);

		/**
		 * Populate wpnonce
		 */
		let postNonce = $(reorderCurrentProduct).data('wpnonce');

		if (!postNonce) {
			disableInput(reorderErrorSelector, 'Invalid field postNonce.', true);
			return false;
		}

		/**
		 * Disable INPUT while doing ajax
		 */
		$(currentObject).prop('disabled', true);

		reorderLoaderSelectorContainer.css({ display: 'inline-block' });

		/**
		 * Format POST URL.
		 */
		const searchParams = new URLSearchParams();

		searchParams.set('action', 'smoc_reorder');
		searchParams.set('_wpnonce', postNonce);

		const request = jQuery.ajax({
			url: ajaxurl + '?' + searchParams,
			type: 'POST',
			data: {
				post_type: typenow,
				post_id: reorderPostID,
				post_menu_order: reorderPostMenuOrder,
			},
		});

		request.done(function (response) {
			if (response.success) {
				reorderSuccessSelector.css('display', 'inline-block');

				$(currentObject).prop('title', reorderPostMenuOrder);

				currentObject.currentValue = reorderPostMenuOrder;
				currentObject.defaultValue = reorderPostMenuOrder;
				// If success go to next product.
				const currentObjectPosition = $(':input[id^=smoc]').index(currentObject);
				$(':input[id^=smoc]').eq(currentObjectPosition + 1).trigger('select');
			} else {
				currentObject.value = currentObject.defaultValue;

				reorderErrorSelector.css('display', 'inline-block');
			}
		});

		request.fail(function () {
			currentObject.value = currentObject.defaultValue;

			reorderLoaderSelectorContainer.css('display', 'none');
			reorderSuccessSelector.css('display', 'none');
			reorderErrorSelector.css('display', 'inline-block');
		});

		request.always(function () {
			reorderLoaderSelectorContainer.css({ display: 'none' });

			/** Enable INPUT after doing Ajax */
			$(currentObject).prop('disabled', false);
		});
	};

	$('input[id^=smoc]').on('focus', function () {
		this.currentValue = this.value;

		$(this).prop('title', parseInt(this.value));

		const reorderLoaderID = 'smoc-' + $(this).data('post-id').toString();

		$('#' + reorderLoaderID + '-loader-container').css({ display: 'none' });
		$('#' + reorderLoaderID + '-success').css({ display: 'none' });
		$('#' + reorderLoaderID + '-error').css({ display: 'none' });
	});

	$('input[id^=smoc]').on('focusout', function (e) {
		if ($(this).prop('disabled')) {
			return false;
		}

		if (this.currentValue !== this.value) {
			if (window.confirm('Do you want to save the menu order?')) {
				$(this).smocDoReorder(this);
			} else {
				this.value = this.defaultValue;
			}
		}
	});

	$('input[id^=smoc]').on('keypress', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault();

			$(this).smocDoReorder(this);
		}
	});
})(jQuery);
