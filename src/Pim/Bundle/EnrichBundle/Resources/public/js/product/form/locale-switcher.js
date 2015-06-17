'use strict';
/**
 * Locale switcher extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/locale-switcher',
        'pim/entity-manager',
        'pim/i18n'
    ],
    function (_, BaseForm, template, EntityManager, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn-group locale-switcher',
            events: {
                'click li a': 'changeLocale'
            },
            render: function () {
                EntityManager.getRepository('locale')
                    .findAll()
                    .done(_.bind(function (locales) {
                        this.$el.html(
                            this.template({
                                locales: locales,
                                currentLocale: _.findWhere(locales, {code: this.getParent().getLocale()}),
                                i18n: i18n
                            })
                        );
                        this.delegateEvents();
                    }, this)
                );

                return this;
            },
            changeLocale: function (event) {
                this.getParent().setLocale(event.currentTarget.dataset.locale);
                this.render();
            }
        });
    }
);
