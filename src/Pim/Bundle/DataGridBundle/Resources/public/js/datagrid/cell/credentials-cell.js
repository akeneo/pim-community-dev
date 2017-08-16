/* global define */
define(['oro/datagrid/string-cell', 'underscore', 'oro/translator'],
    function (StringCell, _, __) {
        'use strict';

        /**
         * Credentials column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            /**
             * Render the API credentials.
             */
            render: function () {
                var value = this.formatter.fromRaw(this.model.get(this.column.get("name")));
                var credentials = _.object(['public_id', 'secret'], value.split('|'));

                if (null === credentials || '' === credentials) {
                    return this;
                }

                this.$el.empty().html("<div><span>" +
                    __('Client ID') +
                    ": </span>" +
                    credentials.public_id + "</div><div><span>" +
                    __('Secret') +
                    ": </span> " +
                    credentials.secret +
                    " </div>");

                return this;
            }
        });
    }
);
