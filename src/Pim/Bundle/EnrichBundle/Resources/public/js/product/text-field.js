"use strict";

define(['underscore'], function (_) {
    return {
        attribute: null,
        data: null,
        template: _.template('<label><%= label %></label><input type="text" value="<%= value %>">'),
        init: function(attribute)
        {
            this.attribute = attribute;

            return this;
        },
        render: function()
        {
            return this.template({label: this.attribute.label, value: this.data[0].value});
        },
        getData: function()
        {
            return this.data;
        },
        setData: function(data)
        {
            this.data = data;
        },
        validate: function()
        {
            return true;
        }
    };
});
