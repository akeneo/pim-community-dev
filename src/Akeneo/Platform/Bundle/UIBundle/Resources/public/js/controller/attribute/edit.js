/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'pim/controller/front',
    'pim/form-builder',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/page-title',
    'pim/i18n'
],
function (
    _,
    BaseController,
    FormBuilder,
    fetcherRegistry,
    UserContext,
    PageTitle,
    i18n
) {
    return BaseController.extend({
        /**
         * {@inheritdoc}
         */
        renderForm: function (route) {
            if (!this.active) {
                return;
            }

            fetcherRegistry.getFetcher('attribute-group').clear();
            fetcherRegistry.getFetcher('locale').clear();
            fetcherRegistry.getFetcher('measure').clear();

            return fetcherRegistry.getFetcher('attribute').fetch(route.params.code, {
                cached: false,
                apply_filters: false
            }).then((attribute) => {
                var label = _.escape(
                    i18n.getLabel(
                        attribute.labels,
                        UserContext.get('catalogLocale'),
                        attribute.code
                    )
                );

                PageTitle.set({'attribute.label': label});

                    return FormBuilder.getFormMeta('pim-attribute-edit-form')
                        .then(FormBuilder.buildForm)
                        .then((form) => {
                            form.setType(attribute.type);

                        return form.configure().then(() => {
                            return form;
                        });
                    })
                    .then((form) => {
                        this.on('pim:controller:can-leave', function (event) {
                            form.trigger('pim_enrich:form:can-leave', event);
                        });
                        form.setData(attribute);
                        form.trigger('pim_enrich:form:entity:post_fetch', attribute);
                        form.setElement(this.$el).render();

                        return form;
                    });
            });
        }
    });
});
