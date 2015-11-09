"use strict";

define([
        'pim/form',
        'text!oro/template/system/tab/system'
    ],
    function(
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'tabbable tabs-left system',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__('oro_config.form.config.tab.system.title')
                });

                this.onExtensions('group:change', this.render.bind(this));
                this.getExtension('oro-system-config-group-selector').setElements(this.getGroups());

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                this.initializeDropZones();
                var groupSelector = this.getExtension('oro-system-config-group-selector');
                this.renderExtension(groupSelector.getCurrentElement().extension);
                this.renderExtension(groupSelector);
            },

            /**
             * {@inheritdoc}
             */
            getGroups: function () {
                var groups = {};
                _.each(_.filter(this.extensions, {isGroup: true}), function (extension) {
                    groups[extension.code] = {
                        'code':      extension.code,
                        'label':     extension.label,
                        'extension': extension
                    };
                });

                return groups;
            }
        });
    }
);
