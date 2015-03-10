"use strict";

define(['pim/field', 'underscore'], function (Field, _) {
    return Field.extend({
        template: _.template([
            '<label><%= label %></label>',
            '<input type="text" data-locale="<%= value.locale %>" data-scope="<%= value.scope %>" value="<%= value.value %>"/>'
        ].join('')),
        events: {
            'change input': 'updateModel'
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
            console.log(this.model.attributes);
        }
    });
});
