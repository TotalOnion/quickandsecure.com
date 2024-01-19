export function init() {
    if ( window.backgrounds == undefined ) {
        console.error('No backgrounds to pick from.');
        return;
    }

    const bylineElement = document.querySelector('[data-byline]');
    if ( !bylineElement ) {
        console.error('No byline element found to pick from.');
    }

    const imageData = window.backgrounds[Math.floor(Math.random() * window.backgrounds.length)];

    const authorElement = bylineElement.querySelector('[data-byline-artist]');
    authorElement.setAttribute('href', imageData.author_url);
    authorElement.innerText = imageData.author;

    const titleElement = bylineElement.querySelector('[data-byline-title]');
    titleElement.setAttribute('href', imageData.title_url);
    titleElement.innerText = imageData.title ? imageData.title : 'Untitled';

    document.querySelector('body').style.backgroundImage = `url(${imageData.image_url})`;

    if ( imageData.mix_blend_mode != undefined) {
        document.querySelector('body').classList.add(`background-image__blend_${imageData.mix_blend_mode}`);
    }

    document.querySelector('body').classList.remove('no-background-image');
}
