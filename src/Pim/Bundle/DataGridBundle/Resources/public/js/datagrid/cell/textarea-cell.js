/* global define */

'use strict';

define(
    [
        'underscore',
        'oro/datagrid/string-cell',
        'text!pim/template/datagrid/cell/textarea-cell',
        'bootstrap'
    ],
    function(
        _,
        StringCell,
        template
    ) {
        return StringCell.extend({
            /** @property {Integer} Max length the content should have in the grid before being truncated */
            datagridMaxLength: 40,

            /** @property {Integer} Max length the content should have in the popover before being truncated */
            popoverMaxLength: 300,

            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function() {
                var content = this.formatter.fromRaw(this.model.get(this.column.get('name')));

                if (content.length <= this.datagridMaxLength) {
                    StringCell.prototype.render.call(this);

                    return this;
                }

                this.$el.html(
                    this.template({
                        id: this.getPopoverID(this.model),
                        content: this.truncateContent(content, this.datagridMaxLength)
                    })
                );

                this.delegateEvents();

                this.$el.popover({
                    title: this.formatter.fromRaw(this.column.get('label')),
                    html: true,
                    content: this.truncateContent(content, this.popoverMaxLength),
                    delay: {
                        show: 500,
                        hide: 100
                    },
                    container: '#' + this.getPopoverID(this.model),
                    trigger: 'hover',
                    // Automatic placement for popover
                    // Source: https://github.com/twbs/bootstrap/issues/1833#issuecomment-4102195
                    placement: function (tip, element) {
                        var offset = $(element).offset();
                        var width = $(document).outerWidth();
                        var horiz = 0.5 * width - offset.left;

                        return horiz > 0 ? 'right' : 'left';
                    }
                });

                return this;
            },

            /**
             * Return the unique popover DOM ID with the given model
             *
             * @param {Object} model
             *
             * @returns {String}
             */
            getPopoverID: function (model) {
                return 'textarea-popover-' + model.get('id');
            },

            /**
             * Truncate the given content depending on the given max length.
             *
             * @param {String}  content
             * @param {Integer} maxLength
             *
             * @returns {String}
             */
            truncateContent: function (content, maxLength) {
                if (content.length > maxLength) {
                    return content.substring(0, maxLength) + ' ...';
                }

                return content;
            }
        });
    }
);
