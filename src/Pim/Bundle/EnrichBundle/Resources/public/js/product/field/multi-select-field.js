"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/multi-select', 'jquery.select2'], function (Field, _, fieldTemplate) {
    return Field.extend({
        template: _.template(fieldTemplate),
        events: {
            'change input': 'updateModel'
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);


            // setTimeout(_.bind(function() {
                var $elem = this.$('input');

                $elem.select2({
                    ajax: {
                        url: '/app_dev.php' + $elem.attr('data-url'),
                        cache: true,
                        data: function(term) {
                            return {search: term};
                        },
                        results: function(data) {
                            return data;
                        }
                    },
                    placeholder: 'choose an option'
                }
                );
            // }, this), 0);
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
