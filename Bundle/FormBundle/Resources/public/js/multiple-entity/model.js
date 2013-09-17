/* global define */
define(['backbone'],
    function(Backbone) {
        'use strict';

        /**
         * @export  oro/multiple-entity/model
         * @class   oro.MultipleEntity.Model
         * @extends Backbone.Model
         */
        return Backbone.Model.extend({
            defaults: {
                id: null,
                link: null,
                label: null,
                isDefault: false,
                extraData: []
            }
        });
    });
