/* global define */
define(['backbone', 'oro/navigation/model'],
function(Backbone, NavigationModel) {
    'use strict';

    /**
     * @export  oro/navigation/collection
     * @class   oro.navigation.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        model: NavigationModel
    });
});