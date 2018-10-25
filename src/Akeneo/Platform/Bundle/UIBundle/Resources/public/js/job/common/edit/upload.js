'use strict';

/**
 * Displays a drop zone to upload a file.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/template/export/common/edit/upload'
], function ($, _, __, BaseForm, template) {
    return BaseForm.extend({
        template: _.template(template),
        events: {
            'change input[type="file"]': 'addFile',
            'click .clear-field': 'removeFile'
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            this.config = config.config;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            this.$el.html(this.template({
                file: this.getFormData().file,
                type: this.config.type,
                __
            }));

            this.delegateEvents();

            return this;
        },

        /**
         * When a file is added to the dom input
         */
        addFile: function () {
            var input = this.$('input[type="file"]').get(0);
            if (!input || 0 === input.files.length) {
                return;
            }

            this.setData({file: input.files[0]});

            this.getRoot().trigger('pim_enrich:form:job:file_updated');

            this.render();
        },

        /**
         * When the user remove the file from the input
         */
        removeFile: function () {
            this.setData({file: null});

            this.getRoot().trigger('pim_enrich:form:job:file_updated');

            this.render();
        }
    });
});
