function generateHtml(element, children) {
	children = children || false

	let html = `<span class="select2-custom-element">`;

	if ('url' in element && null !== element.url) {
		if ('attr' in element && null !== element.attr) {
			let attributes = []
			Object.keys(element.attr).map(function(key, index) {
				attributes.push(key + '="' + element.attr[key] + '"')
			})
			html += `<a href="${element.url}" ${attributes.join(' ')}>`
		} else {	
			html += `<a href="${element.url}">`
		}
	}

	if ('picture' in element && element.picture !== null) {
	    html += `<img src="${element.picture}" alt="${element.text}" />`
	}

	if ('url' in element && null !== element.url) {
		html += `</a>`
	}

	return html + element.text + '</span>'
}

export default (elements) => elements.map(element => {
    element.text = generateHtml(element)

    return element;
});
