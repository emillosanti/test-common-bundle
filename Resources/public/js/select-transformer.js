export default (elements) => elements.map(element => {
    let html = `<span class="select2-custom-element">`;

    if (typeof element.transform === 'undefined' || element.transform === true) {
	    if ('picture' in element && element.picture !== null) {
	        html += `<img src="${element.picture}" alt="${element.text}" />`
	    }
	}

    element.text = html + element.text + '</span>';

    return element;
});
