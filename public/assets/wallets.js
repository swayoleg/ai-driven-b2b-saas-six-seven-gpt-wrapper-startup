/**
 * Copy-to-clipboard for the support page wallet rows.
 *
 * The site is served over plain http:// on a custom domain, which is not a
 * secure context, so navigator.clipboard is undefined there. The hidden
 * textarea + execCommand path is therefore the normal one, not the fallback
 * of last resort. Delegated from the document so wallets added in the admin
 * work without re-binding.
 */
(function () {
    'use strict';

    var REVERT_MS = 2000;

    function copyViaTextarea(text) {
        var area = document.createElement('textarea');

        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.top = '0';
        area.style.left = '-9999px';
        area.style.opacity = '0';

        document.body.appendChild(area);
        area.focus();
        area.select();
        area.setSelectionRange(0, text.length);

        var copied = false;

        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        document.body.removeChild(area);

        return copied;
    }

    function copy(text) {
        if (window.navigator && navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).then(
                function () {
                    return true;
                },
                function () {
                    return copyViaTextarea(text);
                }
            );
        }

        return Promise.resolve(copyViaTextarea(text));
    }

    function flash(button, label) {
        if (button.dataset.walletTimer) {
            window.clearTimeout(Number(button.dataset.walletTimer));
        }

        button.textContent = label;
        button.dataset.walletTimer = String(window.setTimeout(function () {
            button.textContent = button.dataset.copyLabel || 'Copy';
            delete button.dataset.walletTimer;
        }, REVERT_MS));
    }

    document.addEventListener('click', function (event) {
        var target = event.target;

        if (!target || typeof target.closest !== 'function') {
            return;
        }

        var button = target.closest('[data-wallet-copy]');

        if (!button) {
            return;
        }

        event.preventDefault();

        var address = button.getAttribute('data-wallet-copy');

        if (!address) {
            return;
        }

        copy(address).then(function (copied) {
            flash(button, copied
                ? (button.dataset.copiedLabel || 'Copied')
                : (button.dataset.copyLabel || 'Copy'));

            if (!copied) {
                button.title = address;
            }
        });
    });
})();
