import './styles/variables.scss';
import './styles/typography.scss';
import './styles/forms.scss';
import './styles/shared.scss';
import './styles/modals.scss';
import './styles/homepage.scss';
import * as encryptController from './controllers/encrypt-controller.js';
import * as modalController from './controllers/modal-controller.js';
import * as backgroundImageController from './controllers/background-image-controller.js';

window.addEventListener('DOMContentLoaded', () => {
    modalController.init();
	encryptController.init();
    backgroundImageController.init();
});

