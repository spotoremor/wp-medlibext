jQuery(document).ready(function ($) {
    dc_update_media_list(); // initial page load

    $('#media-attachment-filters').change(function () {
        dc_update_media_list();
    });

    function dc_update_media_list() {
        selected = $('#media-attachment-filters').val();
        $("li.show").removeClass('show').addClass('hide');
        $("li[data-types*='" + selected + "']").removeClass('hide').addClass('show');
    }

    $('#dc_imalibext_form').submit(function(){
        
    });
});

function xDCInsertImage(imageUrl) {
    var win = window.dialogArguments || opener || parent || top;
    if (win.document.location.search.indexOf("huge_it") > 0) {
        win.wp.media.editor.send.attachment({}, {url: imageUrl});
    } else {
        win.send_to_editor("<img src=\"" + imageUrl + "\" />");
    }
}

function DCInsertImage(imageUrl) {
    jQuery('#image_filename').val(imageUrl);
}