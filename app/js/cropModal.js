const createCropModal = () => {
    let modal;
    let titleEl;
    let frame;
    let stage;
    let zoom;
    let cancelBtn;
    let saveBtn;
    let errorEl;
    let state = null;

    function build() {
        if (modal) return;

        modal = document.createElement('div');
        modal.className = 'crop-modal';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');

        const card = document.createElement('div');
        card.className = 'crop-card';

        titleEl = document.createElement('div');
        titleEl.className = 'crop-title';

        frame = document.createElement('div');
        frame.className = 'crop-frame';

        stage = document.createElement('div');
        stage.className = 'crop-stage';
        frame.appendChild(stage);

        const controls = document.createElement('div');
        controls.className = 'crop-controls';
        const zoomLabel = document.createElement('span');
        zoomLabel.textContent = 'Zoom';
        zoom = document.createElement('input');
        zoom.type = 'range';
        zoom.min = '1';
        zoom.max = '3';
        zoom.step = '0.01';
        zoom.value = '1';
        zoom.className = 'crop-zoom';
        controls.appendChild(zoomLabel);
        controls.appendChild(zoom);

        errorEl = document.createElement('div');
        errorEl.className = 'crop-error';

        const actions = document.createElement('div');
        actions.className = 'crop-actions';
        cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'btn-outline';
        cancelBtn.textContent = 'Cancel';
        saveBtn = document.createElement('button');
        saveBtn.type = 'button';
        saveBtn.className = 'btn-primary';
        saveBtn.textContent = 'Crop & save';
        actions.appendChild(cancelBtn);
        actions.appendChild(saveBtn);

        card.appendChild(titleEl);
        card.appendChild(frame);
        card.appendChild(controls);
        card.appendChild(errorEl);
        card.appendChild(actions);
        modal.appendChild(card);
        document.body.appendChild(modal);
    }

    function setError(message) {
        errorEl.textContent = message || '';
        errorEl.style.display = message ? 'block' : 'none';
    }

    function clampOffsets() {
        if (!state) return;
        const minX = state.frameW - state.drawW;
        const minY = state.frameH - state.drawH;
        state.offsetX = Math.min(0, Math.max(minX, state.offsetX));
        state.offsetY = Math.min(0, Math.max(minY, state.offsetY));
    }

    function updateStage() {
        if (!state) return;
        stage.style.backgroundSize = state.drawW + 'px ' + state.drawH + 'px';
        stage.style.backgroundPosition = state.offsetX + 'px ' + state.offsetY + 'px';
    }

    function updateTransform() {
        if (!state) return;
        const newZoom = parseFloat(zoom.value);
        const oldScale = state.scale;
        const newScale = state.baseScale * newZoom;
        const centerX = (state.frameW / 2 - state.offsetX) / oldScale;
        const centerY = (state.frameH / 2 - state.offsetY) / oldScale;
        state.zoom = newZoom;
        state.scale = newScale;
        state.drawW = state.imgW * newScale;
        state.drawH = state.imgH * newScale;
        state.offsetX = state.frameW / 2 - centerX * newScale;
        state.offsetY = state.frameH / 2 - centerY * newScale;
        clampOffsets();
        updateStage();
    }

    function close({ clearInput } = { clearInput: true }) {
        if (!modal) return;
        modal.classList.remove('visible');
        setError('');
        stage.classList.remove('dragging');

        if (state) {
            if (state.objectUrl) URL.revokeObjectURL(state.objectUrl);
            if (clearInput && state.input) state.input.value = '';

            if (state.box) {
                state.box.classList.remove('selected');
                const existingThumb = state.box.querySelector('.box-thumb');
                if (existingThumb) existingThumb.remove();
            }
            if (state.hintStrong) {
                state.hintStrong.textContent = state.defaultHintText;
            }
        }
        state = null;
    }

    async function uploadBlob(blob) {
        const form = new FormData();
        form.append('action', state.crop.action);
        form.append('cropped_upload', '1');
        form.append(state.crop.fileField, blob, state.crop.filename);
        const response = await fetch('process_upload.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json'
            }
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await response.json() : null;
        if (!response.ok || !data?.ok) {
            throw new Error(data?.error || 'Upload failed. Please try again.');
        }

        return data;
    }

    function open(opts) {
        build();
        setError('');

        state = {
            input: opts.input,
            box: opts.box,
            hintStrong: opts.hintStrong,
            defaultHintText: opts.defaultHintText,
            file: opts.file,
            image: opts.image,
            objectUrl: opts.objectUrl,
            crop: opts.crop,
            imgW: opts.image.naturalWidth,
            imgH: opts.image.naturalHeight,
            frameW: 0,
            frameH: 0,
            baseScale: 1,
            scale: 1,
            zoom: 1,
            drawW: 0,
            drawH: 0,
            offsetX: 0,
            offsetY: 0,
            dragging: false,
            dragStartX: 0,
            dragStartY: 0,
            dragOffsetX: 0,
            dragOffsetY: 0
        };

        titleEl.textContent = opts.crop.title;
        const maxW = Math.min(560, window.innerWidth - 48);
        const frameW = Math.max(260, maxW);
        const frameH = Math.round(frameW * (opts.crop.aspectH / opts.crop.aspectW));
        state.frameW = frameW;
        state.frameH = frameH;

        frame.style.width = frameW + 'px';
        frame.style.height = frameH + 'px';
        stage.style.width = frameW + 'px';
        stage.style.height = frameH + 'px';
        stage.style.backgroundImage = 'url("' + opts.objectUrl + '")';
        stage.style.backgroundRepeat = 'no-repeat';

        state.baseScale = Math.max(frameW / state.imgW, frameH / state.imgH);
        state.scale = state.baseScale;
        state.drawW = state.imgW * state.scale;
        state.drawH = state.imgH * state.scale;
        state.offsetX = (frameW - state.drawW) / 2;
        state.offsetY = (frameH - state.drawH) / 2;
        clampOffsets();

        zoom.value = '1';
        updateStage();

        if (state.box) state.box.classList.add('selected');
        if (state.hintStrong) state.hintStrong.textContent = 'Selected: ' + state.file.name;

        cancelBtn.disabled = false;
        saveBtn.disabled = false;
        saveBtn.textContent = 'Crop & save';
        modal.classList.add('visible');
    }

    build();

    modal.addEventListener('click', function (e) {
        if (e.target === modal) close({ clearInput: true });
    });
    document.addEventListener('keydown', function (e) {
        if (!modal.classList.contains('visible')) return;
        if (e.key === 'Escape') close({ clearInput: true });
    });
    zoom.addEventListener('input', function () {
        updateTransform();
    });
    stage.addEventListener('pointerdown', function (e) {
        if (!state) return;
        state.dragging = true;
        state.dragStartX = e.clientX;
        state.dragStartY = e.clientY;
        state.dragOffsetX = state.offsetX;
        state.dragOffsetY = state.offsetY;
        stage.setPointerCapture(e.pointerId);
        stage.classList.add('dragging');
    });
    stage.addEventListener('pointermove', function (e) {
        if (!state || !state.dragging) return;
        const dx = e.clientX - state.dragStartX;
        const dy = e.clientY - state.dragStartY;
        state.offsetX = state.dragOffsetX + dx;
        state.offsetY = state.dragOffsetY + dy;
        clampOffsets();
        updateStage();
    });
    stage.addEventListener('pointerup', function () {
        if (!state) return;
        state.dragging = false;
        stage.classList.remove('dragging');
    });
    cancelBtn.addEventListener('click', function () {
        close({ clearInput: true });
    });
    saveBtn.addEventListener('click', function () {
        if (!state) return;
        setError('');
        cancelBtn.disabled = true;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';

        const srcX = (0 - state.offsetX) / state.scale;
        const srcY = (0 - state.offsetY) / state.scale;
        const srcW = state.frameW / state.scale;
        const srcH = state.frameH / state.scale;

        const canvas = document.createElement('canvas');
        canvas.width = state.crop.outputW;
        canvas.height = state.crop.outputH;
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            setError('Failed to prepare image editor.');
            cancelBtn.disabled = false;
            saveBtn.disabled = false;
            saveBtn.textContent = 'Crop & save';
            return;
        }
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(state.image, srcX, srcY, srcW, srcH, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(async function (blob) {
            if (!blob) {
                setError('Could not export the cropped image.');
                cancelBtn.disabled = false;
                saveBtn.disabled = false;
                saveBtn.textContent = 'Crop & save';
                return;
            }
            try {
                const result = await uploadBlob(blob);
                window.location.href = result.redirect || state.crop.redirectUrl;
            } catch (err) {
                setError(err instanceof Error ? err.message : 'Upload failed. Please try again.');
                cancelBtn.disabled = false;
                saveBtn.disabled = false;
                saveBtn.textContent = 'Crop & save';
            }
        }, 'image/jpeg', 0.9);
    });

    return { open, close };
};

export default createCropModal;
