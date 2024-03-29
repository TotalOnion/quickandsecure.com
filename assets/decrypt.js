import './styles/shared/variables.scss';
import './styles/shared/typography.scss';
import './styles/shared/forms.scss';
import './styles/shared/shared.scss';
import './styles/shared/modals.scss';
import './styles/pages/decrypt.scss';
import * as modalController from './controllers/modal-controller.js';
import * as decryptController from './controllers/decrypt-controller.js';
import * as backgroundImageController from './controllers/background-image-controller.js';

window.addEventListener('DOMContentLoaded', () => {
    modalController.init();
	decryptController.init();
    backgroundImageController.init();
});
