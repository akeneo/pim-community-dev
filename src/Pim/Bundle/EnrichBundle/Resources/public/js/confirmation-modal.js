define([
    'underscore',
    'backbone',
    'pim/form',
    'pim/template/grid/mass-actions-confirm'
], function(_, Backbone, BaseForm, template) {

    return BaseForm.extend({
        config: {
            type: '',
            title: '',
            content: '',
            okClass: '',
            okText: '',
            template: _.template(template)
        },

        initialize(options) {
            this.config = Object.assign(this.config, options || {});

            return BaseForm.prototype.initialize.apply(this, arguments);
        },

        render() {
            // Dialog.confirm(message, title, doAction);
            const modal = new Backbone.BootstrapModal(this.config)
            .on('ok', this.doAction);

            modal.open();

            modal.$el.addClass('modal--fullPage');

            this.$el.html(modal);
        }
    });
});
