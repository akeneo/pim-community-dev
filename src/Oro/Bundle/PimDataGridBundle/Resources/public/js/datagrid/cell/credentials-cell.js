/* global define */
define(['oro/datagrid/string-cell', 'underscore', 'oro/translator', 'pim/template/datagrid/cell/credentials-cell'],
    function (StringCell, _, __, CredentialsTemplate) {
        'use strict';

        /**
         * Credentials column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            className: 'AknGrid-bodyCell AknGrid-bodyCell--credentials',

            template: _.template(CredentialsTemplate),
            /**
             * Render the API credentials.
             */
            render: function () {
                var value = this.formatter.fromRaw(this.model.get(this.column.get("name")));
                var credentials = _.object(['public_id', 'secret'], value.split('|'));

                if (null === credentials || '' === credentials) {
                    return this;
                }

                this.$el.empty().html(
                    this.template({
                        clientIdLabel: __('pim_datagrid.cells.credientials.client_id'),
                        secretLabel: __('pim_datagrid.cells.credientials.secret'),
                        publicId: credentials.public_id,
                        secret: credentials.secret
                    })
                );

                return this;
            }
        });
    }
);
