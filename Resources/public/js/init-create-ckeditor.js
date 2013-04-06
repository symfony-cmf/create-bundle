jQuery(document).ready(function() {
    jQuery('body').midgardCreate({
        url: function() {
            if (this.id) {
                if (this.id.charAt(0) == "<") {
                    return cmfCreatePutDocument + this.id.substring(1, this.id.length - 1);
                }
                return cmfCreatePutDocument + "/" + this.id;
            }
            return cmfCreatePutDocument;
        },
        stanbolUrl: cmfCreateStanbolUrl,
        tags: true,
        editorWidgets: {
            'default': 'ckeditor',
            'dcterms:description': null
        },
        editorOptions: {
            ckeditor: {
                widget: 'ckeditorWidget'
            }
        },
        collectionWidgets: {
            'default': null,
            'feature': 'midgardCollectionAdd'
        }
    });

    window.CKEDITOR.basePath = window.CKEDITOR_BASEPATH;
    window.CKEDITOR.plugins.basePath = window.CKEDITOR_BASEPATH + "/plugins/";
    window.CKEDITOR.config.skin = "moono," + window.CKEDITOR_BASEPATH + "/skins/moono/";
    window.CKEDITOR.config.customConfig = window.CKEDITOR_BASEPATH + "/config.js";
    window.CKEDITOR.config.removePlugins = 'smiley,flash,horizontalrule,magicline,pagebreak,iframe,wsc';
    window.CKEDITOR.config.toolbarGroups = [
        { name: 'clipboard' },
        { name: 'undo' },
        { name: 'links' },
        { name: 'insert' },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'align' ] },
        '/',
        { name: 'styles' },
        { name: 'colors' }
    ];
});
