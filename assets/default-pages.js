import './styles/shared/variables.scss';
import './styles/shared/typography.scss';
import './styles/shared/shared.scss';
import './styles/shared/modals.scss';
import * as backgroundImageController from './controllers/background-image-controller.js';

window.addEventListener('DOMContentLoaded', () => {
    backgroundImageController.init();
});

