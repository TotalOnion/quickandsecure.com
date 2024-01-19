import './styles/variables.scss';
import './styles/typography.scss';
import './styles/forms.scss';
import './styles/shared.scss';
import './styles/modals.scss';
import './styles/decrypt.scss';
import * as modalController from './controllers/modal-controller.js';
import * as decryptController from './controllers/decrypt-controller.js';
import * as backgroundImageController from './controllers/background-image-controller.js';

window.addEventListener('DOMContentLoaded', () => {
    modalController.init();
	decryptController.init();
    backgroundImageController.init();
});
