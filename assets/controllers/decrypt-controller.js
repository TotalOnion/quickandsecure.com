import { base64 } from "rfc4648";
import * as utils from './utils.js';

const state = {
  outputElement: document.querySelector('[data-source="secret"]')
};

export function init(secret) {
  let rawKey = base64.parse(location.hash.substr(1));

  state.iv = base64.parse(secret.iv);
  state.ciphertext = base64.parse(secret.data);
  
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
