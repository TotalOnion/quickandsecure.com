import { base64 } from "rfc4648";
import * as utils from './utils.js';

const state = {
  buttonElement: document.querySelector('[data-action="create"]'),
  textareaElement: document.querySelector('[data-source="secret"]'),
  slug: generateSlug()
};

export function init(args) {
  /*
  Generate an encryption key, then set up event listeners
  on the "Encrypt" and "Decrypt" buttons.
  */
  window.crypto.subtle.generateKey(
    {
        name: "AES-GCM",
        length: 256,
    },
    true,
    ["encrypt", "decrypt"]
  ).then((key) => {
    state.key = key;
    window.crypto.subtle.exportKey('raw', state.key)
      .then(
        rawKey => {
          window.test2 = rawKey;
          
          state.rawKey = base64.stringify(new Uint8Array(rawKey));
        }
      )
    ;

    state.buttonElement.addEventListener(
      'click',
      event => {
        go();
      },
      { passive: true }
    );
  });

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
                  .innerText
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
};

function generateSlug() {
  let slug = '';
  let characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let digitArray = new Uint8Array(7);
  window.crypto.getRandomValues(digitArray);
  digitArray.forEach(
    digit => {
      slug = slug + characters.charAt(digit % 62);
    }
  );

  return slug;
}

function go() {
  if (!state.textareaElement.value) {
    utils.displayErrorMessage(state.textareaElement.getAttribute('error-empty'));
    return;
  }

  encryptMessage(getMessageEncoding())
    .then(
      (ciphertext) => {
        state.ciphertext = ciphertext;
        transmit();
      }
    )
  ;
}

function transmit() {
  let xhr = new XMLHttpRequest();
  xhr.open('POST', '/api/v1/secret/'+state.slug, true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.onreadystatechange = function() {
    if(xhr.readyState === XMLHttpRequest.DONE) {
      switch(xhr.status) {
        case 201:
          displaySuccess();
          break;

        case 409:
          // miraculously we have a clashing slug. Create a new one and go again
          go();
          break;

        case 400:
          utils.displayErrorMessage(state.textareaElement.getAttribute('error-bad-request'));
          break;

        case 500:
          utils.displayErrorMessage(state.textareaElement.getAttribute('error-server-error'));
          break;
      }
    }
  };
  
  xhr.send(JSON.stringify({
      data: base64.stringify(new Uint8Array(state.ciphertext)),
      iv: base64.stringify(state.iv)
  }));
}



/*
Fetch the contents of the "message" textbox, and encode it
in a form we can use for the encrypt operation.
*/
function getMessageEncoding() {
  let message = state.textareaElement.value;
  let enc = new TextEncoder();
  return enc.encode(message);
}

/*
Get the encoded message, encrypt it and display a representation
of the ciphertext in the "Ciphertext" element.
*/
async function encryptMessage(message) {
  // The iv must never be reused with a given key.
  state.iv = window.crypto.getRandomValues(new Uint8Array(12));
  return window.crypto.subtle.encrypt(
    {
      name: "AES-GCM",
      iv: state.iv
    },
    state.key,
    message
  );
}

function displaySuccess() {
  document.querySelector('body').classList.add('success');
  document.querySelector('[data-source="full-link"]').innerHTML = 
    location.protocol + '//' + location.host + '/' + state.slug + '#' + state.rawKey;
  ;
}
