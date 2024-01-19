
const state = {};

export function init() {
    state.modalElement = document.querySelector('.modal');
    if ( ! state.modalElement ) {
        console.error('No modal element found.');

    }
    // Add the event listeners to the buttons
    initModal();
    document.addEventListener('modal:display', renderModal );
}

function initModal() {
    state.modalElement.querySelectorAll('[data-modal-action="close"]').forEach(
        (closeBtn) => {
            closeBtn.addEventListener(
                'click',
                (event) => {
                    document.querySelector('body').classList.remove('modal-active');
                }
            );
        }
    );

    state.titleElement = state.modalElement.querySelector('[data-modal-title]');
    state.textElement = state.modalElement.querySelector('[data-modal-text]');
}

function renderModal( event )
{
    // Set the title
    state.titleElement.innerText = event.detail.title;

    // ensure we have an array of messages
    if ( typeof event.detail.text == 'string' ) {
        event.detail.text = [event.detail.text];
    }

    state.textElement.innerHTML = '';
    event.detail.text.forEach(
        (message) => {
            const p = document.createElement('p');
            p.innerText = message;
            state.textElement.appendChild(p);
        }
    )
    
    document.querySelector('body').classList.add('modal-active');
}