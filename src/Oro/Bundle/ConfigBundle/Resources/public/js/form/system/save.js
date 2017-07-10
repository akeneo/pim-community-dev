

import _ from 'underscore';
import $ from 'jquery';
import Routing from 'routing';
import SaveForm from 'pim/form/common/save';
import template from 'pim/template/form/save';
        export default SaveForm.extend({
            template: _.template(template),
            events: {
                'click .save': 'save'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    label: _.__('pim_enrich.entity.save.label')
                }));
            },

            /**
             * {@inheritdoc}
             */
            save: function () {
                this.getRoot().trigger('pim_enrich:form:entity:pre_save', this.getFormData());
                this.showLoadingMask();

                $.ajax({
                    method: 'POST',
                    url: this.getSaveUrl(),
                    contentType: 'application/json',
                    data: JSON.stringify(this.getFormData())
                })
                .then(this.postSave.bind(this))
                .fail(this.fail.bind(this))
                .always(this.hideLoadingMask.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            getSaveUrl: function () {
                return Routing.generate(__moduleConfig.route);
            },

            /**
             * {@inheritdoc}
             */
            postSave: function (data) {
                this.setData(data);
                this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);

                SaveForm.prototype.postSave.apply(this, arguments);
            }
        });
    
