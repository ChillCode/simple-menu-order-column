/*!
 * Simple Menu Order Column - Vanilla JS Version
 *
 * https://github.com/ChillCode/simple-menu-order-column/
 *
 * Copyright (C) 2003-2025 ChillCode
 *
 * @license Released under the General Public License v3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */
(function () {

    const { __ } = wp.i18n;

    function smocInit() {
        document.removeEventListener("DOMContentLoaded", smocInit);
        window.removeEventListener("load", smocInit);

        const smocInputs = document.querySelectorAll('input[id^=smoc]');

        smocInputs.forEach(smocInput => {
            smocInput.addEventListener('focus', () => {
                smocInput.currentValue = smocInput.value;
                smocInput.title = parseInt(smocInput.value);

                const { postId } = smocInput.dataset;
                if (!postId) { return; };

                const hideElement = id => {
                    const smocElement = document.getElementById(id);
                    if (smocElement) { smocElement.style.display = 'none'; }
                };

                const smocBaseId = `smoc-${postId}`;

                hideElement(`${smocBaseId}-loader-container`);
                hideElement(`${smocBaseId}-success`);
                hideElement(`${smocBaseId}-error`);
            });

            smocInput.addEventListener('focusout', () => {
                if (smocInput.disabled) { return; };

                if (smocInput.currentValue !== smocInput.value) {
                    if (window.confirm(__('Should the menu order value be updated?', 'simple-menu-order-column'))) {
                        smocDoReorder(smocInput);
                    } else {
                        smocInput.value = smocInput.defaultValue;
                    }
                }
            });

            smocInput.addEventListener('keydown', (smocKeydownEvent) => {
                const allowedKeys = [
                    'Backspace', 'Tab', 'ArrowLeft', 'ArrowRight',
                    'ArrowUp', 'ArrowDown', 'Delete', 'Home', 'End', 'Enter'
                ];

                // Allow: Ctrl/Cmd + A/C/V/X
                if ((smocKeydownEvent.ctrlKey || smocKeydownEvent.metaKey) && ['a', 'c', 'v', 'x'].includes(smocKeydownEvent.key.toLowerCase())) { return; }

                // Allow navigation/edit keys
                if (allowedKeys.includes(smocKeydownEvent.key)) { return; }

                // Block any key that is not a digit (0-9)
                if (!/^\d$/.test(smocKeydownEvent.key)) {
                    smocKeydownEvent.preventDefault();
                }
            });

            smocInput.addEventListener('paste', (smocPasteEvent) => {
                const pasted = smocPasteEvent.clipboardData.getData('text');
                if (!/^\d+$/.test(pasted)) {
                    smocPasteEvent.preventDefault();
                }
            });

            smocInput.addEventListener('keypress', smocKeypressEvent => {
                if (smocKeypressEvent.key === 'Enter') {
                    smocKeypressEvent.preventDefault();
                    smocDoReorder(smocInput);
                }
            });
        });
    }

    function smocDoReorder(smocInput) {
        if (!smocInput || smocInput.disabled) { return; }

        const smocContainer = smocInput.closest('.smoc-container'), postId = parseInt(smocInput.dataset.postId);

        if (!postId || isNaN(postId)) {
            disableInput(null, __('The post_id is invalid.', 'simple-menu-order-column'), true, smocInput);
            return;
        }

        const loaderId = `smoc-${postId}`, menuOrder = parseInt(smocInput.value);

        if (isNaN(menuOrder)) {
            const errorEl = document.getElementById(`${loaderId}-error`);
            disableInput(errorEl, __('The menu order value is invalid.', 'simple-menu-order-column'), false, smocInput);
            return;
        }

        const nonce = smocInput.dataset.wpnonce;
        if (!nonce) {
            const errorEl = document.getElementById(`${loaderId}-error`);
            disableInput(errorEl, __('The postNonce is invalid.', 'simple-menu-order-column'), true, smocInput);
            return;
        }

        if (typeof ajaxurl === 'undefined' || typeof typenow === 'undefined') {
            const errorEl = document.getElementById(`${loaderId}-error`);
            disableInput(errorEl, __('Invalid WP installation, variables typenow or ajaxurl are not initialized.', 'simple-menu-order-column'), true, smocInput);
            return;
        }

        smocInput.disabled = true;

        const showLoader = () => {
            let smocLoaderContainer = document.getElementById(`${loaderId}-loader-container`);
            if (!smocLoaderContainer) {
                const smocLoader = document.createElement('span');
                smocLoader.id = `${loaderId}-loader`;
                smocLoader.className = 'smoc-loader dashicons dashicons-update';
                smocLoader.setAttribute('role', 'img');
                smocLoader.setAttribute('aria-label', __('Updating menu order...', 'simple-menu-order-column'));
                smocLoader.style.cssText = 'color: #2ea2cc; animation: iconrotation 2s infinite linear; display: inline-block;';

                smocLoaderContainer = document.createElement('div');
                smocLoaderContainer.id = `${loaderId}-loader-container`;
                smocLoaderContainer.style.cssText = 'padding-top: 5px; display: inline-block;';
                smocLoaderContainer.appendChild(smocLoader);
                smocContainer.appendChild(smocLoaderContainer);
            } else {
                smocLoaderContainer.style.display = 'inline-block';
            }
        };

        const showSuccess = () => {
            let smocSuccess = document.getElementById(`${loaderId}-success`);
            if (!smocSuccess) {
                smocSuccess = document.createElement('span');
                smocSuccess.id = `${loaderId}-success`;
                smocSuccess.className = 'smoc-success dashicons dashicons-yes-alt';
                smocSuccess.setAttribute('role', 'img');
                smocSuccess.setAttribute('aria-label', __('The menu order has been updated successfully.', 'simple-menu-order-column'));
                smocSuccess.style.cssText = 'padding-top: 5px; color: #7ad03a; display: inline-block;';
                smocContainer.appendChild(smocSuccess);
            } else {
                smocSuccess.style.display = 'inline-block';
            }
        };

        const showError = () => {
            let smocError = document.getElementById(`${loaderId}-error`);
            if (!smocError) {
                smocError = document.createElement('span');
                smocError.id = `${loaderId}-error`;
                smocError.className = 'smoc-error dashicons dashicons-dismiss';
                smocError.setAttribute('role', 'img');
                smocError.setAttribute('aria-label', __('An error ocurred while updating menu order.', 'simple-menu-order-column'));
                smocError.style.cssText = 'padding-top: 5px; color: #a00; display: inline-block;';
                smocContainer.appendChild(smocError);
            } else {
                smocError.style.display = 'inline-block';
            }
        };

        const hideLoader = () => {
            const smocLoaderContainer = document.getElementById(`${loaderId}-loader-container`);
            if (smocLoaderContainer) { smocLoaderContainer.style.display = 'none'; }
        };

        showLoader();

        fetch(`${ajaxurl}?action=smoc_reorder&_wpnonce=${encodeURIComponent(nonce)}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                post_type: typenow,
                post_id: postId,
                post_menu_order: menuOrder,
            }),
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    showSuccess();
                    smocInput.title = menuOrder;
                    smocInput.currentValue = menuOrder;
                    smocInput.defaultValue = menuOrder;

                    const inputs = Array.from(document.querySelectorAll('input[id^=smoc]')), pos = inputs.indexOf(smocInput) + 1;
                    if (inputs[pos]) { inputs[pos].select(); }
                } else {
                    smocInput.value = smocInput.defaultValue;
                    showError();
                }
            })
            .catch(() => {
                smocInput.value = smocInput.defaultValue;
                hideLoader();
                showError();
            })
            .finally(() => {
                hideLoader();
                smocInput.disabled = false;
            });
    }

    function disableInput(errorContainer, message, disable, input) {
        input.value = input.defaultValue;
        if (errorContainer) { errorContainer.style.display = 'inline-block'; }
        input.disabled = disable;
        input.title = message;
        console.warn(`[Simple Menu Order Column] ${message}`);
    }

    /**
     * Initilize Script
     */
    if (document.readyState === "loading") {
        document.addEventListener('DOMContentLoaded', smocInit);
        window.addEventListener("load", smocInit);
    } else {
        window.setTimeout(smocInit);
    }
})();
