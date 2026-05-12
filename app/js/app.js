import createCropModal from './cropModal.js';
import { initAutoDismissAlerts } from './features/alerts.js';
import { initLikeButtons } from './features/likes.js';
import { initImagePickers } from './features/imagePicker.js?v=20260512-3to1';

const init = () => {
    const cropModal = createCropModal();
    initImagePickers(cropModal, window.LinkedFinConfig || {});
    initAutoDismissAlerts();
    initLikeButtons();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
