import { base64 } from "rfc4648";
import * as utils from './utils.js';

const state = {
  outputElement: document.querySelector('[data-source="secret"]')
};

export function init() {
  
  // TODO: ensure the browser can do the decryption, and display a message if not.

  // Attach the copy to clipboard handler
  document
    .querySelectorAll('[data-action="copy-to-clipboard"]')
    .forEach(
      (clipboardButton) => {
        clipboardButton.addEventListener(
          'click',
          (event) => {
            let sourceCssSelector = '[data-source="'+ clipboardButton.getAttribute('data-copy-source') +'"]';

            utils
              .copyTextToClipboard(
                document
                  .querySelector(sourceCssSelector)
                  .value
              )
            ;

            clipboardButton.innerHTML = clipboardButton.getAttribute('data-success-message');
          },
          {
            passive: true
          }
        )
      }
    )
  ;

  loadSecret( location.pathname.substring(1) );
}

function loadSecret( slug ) {
  let xhr = new XMLHttpRequest();
  xhr.open('GET', '/api/v1/secret/' + slug, true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.onreadystatechange = function() {
    if(xhr.readyState === XMLHttpRequest.DONE) {
      switch(xhr.status) {
        case 200:
          const payload = JSON.parse(xhr.response);
          state.iv = base64.parse( payload.iv );
          state.ciphertext = base64.parse( payload.data );

          const rawKey = base64.parse(location.hash.substring(1));
  
          window.crypto.subtle
            .importKey(
              'raw',
              rawKey,
              "AES-GCM",
              true,
              ["encrypt", "decrypt"]
            ).then(
              key => {
                state.key = key;
                decryptMessage();
              },
              error => {
                // TODO: error handling
                console.log('nay');
                console.log(error);
              }
            )
          ;
          
          break;

        case 500:
        default:
          utils.displayErrorMessage(state.textareaElement.getAttribute('error-server-error'));
          break;
      }
    }
  };
  
  xhr.send();
}

async function decryptMessage() {
  let decrypted = await window.crypto.subtle.decrypt(
    {
      name: "AES-GCM",
      iv: state.iv
    },
    state.key,
    state.ciphertext
  );

  let dec = new TextDecoder();
  state.outputElement.value = dec.decode(decrypted);
}
