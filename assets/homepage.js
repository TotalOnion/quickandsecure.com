import './styles/shared/variables.scss';
import './styles/shared/typography.scss';
import './styles/shared/forms.scss';
import './styles/shared/shared.scss';
import './styles/shared/modals.scss';
import './styles/pages/homepage.scss';
import * as encryptController from './controllers/encrypt-controller.js';
import * as modalController from './controllers/modal-controller.js';
import * as backgroundImageController from './controllers/background-image-controller.js';

window.addEventListener('DOMContentLoaded', () => {
    modalController.init();
	encryptController.init();
    backgroundImageController.init();
});

