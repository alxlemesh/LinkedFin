const setupImagePicker = (cropModal, config, appConfig) => {
    const input = document.getElementById(config.inputId);
    if (!input) return;

    const box = input.closest('.file-upload-box');
    const hintStrong = box?.querySelector('.file-upload-hint strong') ?? null;
    const defaultHintText = hintStrong?.textContent ?? 'Click to choose';

    const ensureErrorEl = () => {
        let errorEl = input.parentElement.querySelector('.file-upload-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'file-upload-error';
            input.parentElement.insertAdjacentElement('afterend', errorEl);
        }
        return errorEl;
    };

    const showError = (message) => {
        const errorEl = ensureErrorEl();
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    };

    const clearError = () => {
        const errorEl = input.parentElement.parentElement.querySelector('.file-upload-error');
        if (!errorEl) return;
        errorEl.textContent = '';
        errorEl.style.display = 'none';
    };

    const resetSelectionState = () => {
        box?.classList.remove('selected');
        if (hintStrong) hintStrong.textContent = defaultHintText;
    };

    const rejectSelection = (message) => {
        input.value = '';
        resetSelectionState();
        showError(message);
    };

    const hasValidAspectRatio = (width, height) => {
        const actualRatio = width / height;
        const requiredRatio = config.crop.aspectW / config.crop.aspectH;
        return Math.abs(actualRatio - requiredRatio) <= 0.01;
    };

    input.addEventListener('change', (e) => {
        const target = e.target;
        const file = target?.files?.[0];
        if (!file) {
            resetSelectionState();
            clearError();
            return;
        }

        clearError();

        if (file.size > config.maxBytes) {
            rejectSelection(`File is too large. Maximum allowed size is ${config.maxSizeLabel}.`);
            return;
        }

        if (!config.allowedMime.includes(file.type)) {
            rejectSelection('Invalid file type. Please upload a JPEG, PNG, or GIF image.');
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const image = new Image();

        image.onload = () => {
            const tooSmall = image.naturalWidth < config.minWidth || image.naturalHeight < config.minHeight;
            if (tooSmall) {
                URL.revokeObjectURL(objectUrl);
                rejectSelection(
                    `Image is too small (${image.naturalWidth}×${image.naturalHeight} px). Minimum required is ${config.minWidth}×${config.minHeight} px.`
                );
                return;
            }

            if (!appConfig.imageCropEnabled) {
                const invalidAspect = !hasValidAspectRatio(image.naturalWidth, image.naturalHeight);
                URL.revokeObjectURL(objectUrl);

                if (invalidAspect) {
                    rejectSelection(
                        `Invalid image ratio (${image.naturalWidth}×${image.naturalHeight} px). Required ratio is ${config.crop.aspectW}:${config.crop.aspectH}.`
                    );
                    return;
                }

                if (box) box.classList.add('selected');
                if (hintStrong) hintStrong.textContent = 'Selected: ' + file.name;
                input.form?.submit();
                return;
            }

            cropModal.open({
                input,
                box,
                hintStrong,
                defaultHintText,
                file,
                image,
                objectUrl,
                crop: config.crop
            });
        };

        image.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            rejectSelection('The selected file is not a valid image.');
        };

        image.src = objectUrl;
    });
};

export const initImagePickers = (cropModal, appConfig = {}) => {
    const pickerConfig = {
        imageCropEnabled: appConfig.imageCropEnabled !== false
    };

    setupImagePicker(cropModal, {
        inputId: 'avatar_file',
        maxBytes: 2 * 1024 * 1024,
        maxSizeLabel: '2 MB',
        minWidth: 200,
        minHeight: 200,
        allowedMime: ['image/jpeg', 'image/png', 'image/gif'],
        crop: {
            title: 'Crop profile picture (1:1)',
            aspectW: 1,
            aspectH: 1,
            outputW: 800,
            outputH: 800,
            action: 'upload_avatar',
            fileField: 'avatar_file',
            filename: 'avatar.jpg',
            redirectUrl: 'update_profile.php'
        }
    }, pickerConfig);

    setupImagePicker(cropModal, {
        inputId: 'banner_file',
        maxBytes: 3 * 1024 * 1024,
        maxSizeLabel: '3 MB',
        minWidth: 600,
        minHeight: 200,
        allowedMime: ['image/jpeg', 'image/png', 'image/gif'],
        crop: {
            title: 'Crop banner photo (3:1)',
            aspectW: 3,
            aspectH: 1,
            outputW: 1500,
            outputH: 500,
            action: 'upload_banner',
            fileField: 'banner_file',
            filename: 'banner.jpg',
            redirectUrl: 'update_profile.php#banner'
        }
    }, pickerConfig);
};
