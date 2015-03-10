"use strict";

define(['pim/field', 'underscore'], function (Field, _) {
    return Field.extend({
        template: _.template([
            '<label><%= label %></label>',
            '<% if (!value.value) { %>',
                '<% _.each(config.currency, function (currency) { %><input type="text" data-locale="<%= value.locale %>" data-scope="<%= value.scope %>" data-currency="<%= currency %>" value=""><% }) %>',
            '<% } else { %>',
                '<% _.each(value.value, function (price) { %><input type="text" data-locale="<%= value.locale %>" data-scope="<%= value.scope %>" data-currency="<%= price.currency %>" value="<%= price.data %>"><% }) %>',
            '<% } %>'
        ].join('')),
    });
});
