'use strict';

define(
    ['backbone', 'routing'],
    function (Backbone, Routing) {
        var DateContext = Backbone.Model.extend({
            url: Routing.generate('pim_localization_format_date')
        });

        var instance = new DateContext();

        instance.fetch({async: false});

        return instance;
    }
);
