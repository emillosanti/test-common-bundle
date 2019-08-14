jQuery(function ($) {
    'use strict';

    let $wrapper = $('.documents-wrapper'),
        $prototype = $wrapper.find('#prototype'),
        $noResult = $wrapper.find('.no-result');

    // remove document
    $wrapper.on('submit', 'form[data-remove]', function (event) {
        event.preventDefault();
        let $form = $(this);

        $.ajax({
            url : $form.attr('action'),
            method: 'DELETE',
            success: function() {
                let $category = $form.closest('.category');
                $form.closest('.document').remove();

                if (!$category.find('.document').length) {
                    $category.find($noResult).show();
                }
            },
            error: function() {
                alert('something went wrong');
            }
        });
    });

    // add document
    $wrapper.on('submit', 'form[data-create]', function(event) {
        event.preventDefault();
        let $form = $(this),
            $category = $(this).closest('.category');

        Dropbox.choose({
            linkType: "preview",
            folderselect: false,
            multiselect: true,
            success: function(files) {
                $.ajax({
                    url : $form.attr('action'),
                    method: 'POST',
                    data: {category: $form.data('category'), files: files},
                    success: function(ids) {
                        files.map(function(file, i) {
                            let template = $prototype.html()
                                .replace(/__document_id__/g, ids[i])
                                .replace(/__document_url__/g, file.link)
                                .replace(/__document_name__/g, file.name)
                                .replace(/__document_created_at__/g, moment().format('DD/MM/Y'))
                                .replace(/__document_user__/g, '')
                                .replace(/__document_user_picture__/g, '');

                            $category.find('.documents').append(template);
                        });

                        $category.find($noResult).hide();
                    },
                    error: function() {
                        alert('something went wrong');
                    }
                });
            },
        });
    });
});