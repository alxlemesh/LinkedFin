/* Client-side preview & validation helpers */

(function () {
    'use strict';

    // ── Image preview before upload ────────────────────────────────────────────
    function setupPreview(inputId, previewWrapperId, previewImgId, filenameId, shape) {
        const input       = document.getElementById(inputId);
        const wrap        = document.getElementById(previewWrapperId);
        const previewImg  = document.getElementById(previewImgId);
        const filenameEl  = document.getElementById(filenameId);
        if (!input) return;

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                filenameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                wrap.classList.add('visible');
            };
            reader.readAsDataURL(file);
        });
    }

    setupPreview('avatar_file',  'avatar-preview-wrap',  'avatar-preview-img',  'avatar-preview-name',  'circle');
    setupPreview('banner_file',  'banner-preview-wrap',  'banner-preview-img',  'banner-preview-name',  'rect');

    // ── Auto-dismiss alerts after 5 s ─────────────────────────────────────────
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 500);
        }, 5000);
    });

    // ── Like button toggle ──────────────────────────────────────────────────────
    document.querySelectorAll('.post-like-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const countEl = this.querySelector('.like-count');
            const liked   = this.dataset.liked === '1';
            const count   = parseInt(countEl.textContent, 10);
            if (liked) {
                countEl.textContent = count - 1;
                this.dataset.liked  = '0';
                this.style.color    = '';
            } else {
                countEl.textContent = count + 1;
                this.dataset.liked  = '1';
                this.style.color    = '#0a66c2';
            }
        });
    });
}());
