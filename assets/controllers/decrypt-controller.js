import { base64 } from "rfc4648";

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
