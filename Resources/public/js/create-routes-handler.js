jQuery(document).ready(function() {

    (function(){

        var needRouteCreation = false; //internal trigger for savedentity event

        //an entity has been saved and the response of the backend received
        $('body').bind('midgardstoragesavedentity', function (event, options) {

            var createdType = options.entity.attributes["@type"];
            var createRoutes = false;
            for(var i in cmfCreateCreateRoutesTypes) {
                if (createdType == "<" + cmfCreateCreateRoutesTypes[i] + ">") {
                    createRoutes = true;
                    break;
                }
            }

            if (!createRoutes || !needRouteCreation) {
                return;
            }

            var vie = options.entity.vie;

            /**
             * Common request content
             */
            var trimmedSubject = options.entity.id.substr(1, options.entity.id.length - 2);
            var lastSlashPos = trimmedSubject.lastIndexOf("/") + 1;
            var contentName = trimmedSubject.substr(lastSlashPos, trimmedSubject.length - lastSlashPos);
            var partOf = options.entity.attributes["<http://purl.org/dc/terms/partOf>"].models[0]["@subject"];
            var trimmedPartOf = partOf.substr(1, partOf.length - 2); // "/cms/content/news"
            var lastSlashPos = trimmedPartOf.lastIndexOf("/") + 1;
            var parentName = trimmedPartOf.substr(lastSlashPos, trimmedPartOf.length - lastSlashPos);

            /**
             * Request types
             */
            var parentType =  "<" + cmfCreateRouteRdfType + "/Parent" + ">";
            var nameType =  "<" + cmfCreateRouteRdfType + "/Name" + ">";
            var routeContentType =  "<" + cmfCreateRouteRdfType + "/RouteContent" + ">";
            var localeType =  "<" + cmfCreateRouteRdfType + "/Locale" + ">";
            var partOfType = "<http://purl.org/dc/terms/partOf>";

            for(var i in cmfCreateLocales) {
                var parentPath = cmfCreateRoutesPrefix + "/" + cmfCreateLocales[i] + "/" + parentName;

                var routeRequest = {};
                routeRequest["@type"] = "<" + cmfCreateRouteRdfType + ">";
                routeRequest[nameType] = contentName;
                routeRequest[routeContentType] = trimmedSubject;
                routeRequest[partOfType] = [parentPath];
                routeRequest[localeType] = cmfCreateLocales[i];
                routeRequest[parentType] = parentPath;

                var routeEntity = new vie.Entity();
                routeEntity.set(routeRequest);
                vie.entities.add(routeEntity);
                jQuery('body').midgardStorage('saveRemote', routeEntity, options);
            }
        });

        //an entity will be saved and sent to the backend
        $('body').bind('midgardstoragesaveentity', function (event, options) {
            //TODO: handle the case where new content and updated content is saved at the same time
            needRouteCreation = options.entity.isNew();
        });
    })()
});
