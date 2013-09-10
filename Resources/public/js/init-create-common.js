jQuery(document).ready(function() {
    jQuery('body').midgardCreate('configureEditor', 'title', 'editWidget', {
    });

    jQuery(cmfCreatePlainTextTypes).each(function(index, value) {
        jQuery('body').midgardCreate('setEditorForProperty', value, 'title');
    });
});
