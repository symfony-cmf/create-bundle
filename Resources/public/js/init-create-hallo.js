jQuery(document).ready(function() {
    jQuery('body').midgardCreate({
        url: function() {
            if (this.id) {
                if (this.id.charAt(0) == "<") {
                    return cmfCreatePutDocument + this.id.substring(1, this.id.length - 1);
                }
                return cmfCreatePutDocument +  this.id;
            }
            return cmfCreatePutDocument;
        },
        workflows: {
            url: function(model) {
                return cmfCreateWorkflows + model.getSubjectUri();
            }
        },
        stanbolUrl: cmfCreateStanbolUrl,
        tags: true
    });

    jQuery('body').midgardCreate('configureEditor', 'default', 'halloWidget', {
        plugins: {
            'halloformat': {'formattings': {'strikeThrough': false, 'underline': false}},
            'halloblock': {},
            'hallolists': {'lists': {'ordered': false}},
            'hallojustify': {},
            'halloimage': {
                search: function (query, limit, offset, successCallback) {
                    limit = limit || 8;
                    offset = offset || 0;
                    jQuery.ajax({
                        type: "GET",
                        url: cmfCreateImageSearch,
                        data: "query="+query+"&offset="+offset+"&limit="+limit,
                        success: successCallback
                    });
                },
                // TODO: this only brings an empty suggestions tab instead of calling the function
                // suggestions: function(tags, limit, offset, successCallback) {
                //     limit = limit || 8;
                //     offset = offset || 0;
                //     return jQuery.ajax({
                //         type: "GET",
                //         url: "/app_dev.php/symfony-cmf/vie/assets/list/",
                //         data: "tags=" + tags + "&offset=" + offset + "&limit=" + limit,
                //         success: successCallback
                //     });
                // },
                uploadUrl: cmfCreateImageUpload,
                'vie': this.vie
            },
            'hallolink': { 'relatedUrl': cmfCreateLinkRelatedPath },
            'hallooverlay': {},
            'halloindicator': {}
        },
        toolbarState: cmfCreateHalloFixedToolbar,
        parentElement: cmfCreateHalloParentElement
    });
    
    
    jQuery('body').bind('halloenabled', null, function () {
        $('.create-ui-toolbar-wrapper')
            .addClass('editing');
    });

    jQuery('body').bind('hallodisabled', null, function () {
        $('.create-ui-toolbar-wrapper')
            .removeClass('editing');
    });
});
