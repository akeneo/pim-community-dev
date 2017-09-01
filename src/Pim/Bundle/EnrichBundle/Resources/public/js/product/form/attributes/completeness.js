'use strict';
/**
 * completeness filter extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/provider/to-fill-field-provider'
    ],
    function ($, _, BaseForm, fetcherRegistry, UserContext, toFillFieldProvider) {
        return BaseForm.extend({
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:to-fill-filter', this.addFieldFilter);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Add filter on field if the user doesn't have the right to edit it.
             *
             * @param {object} event
             */
            addFieldFilter: function (event) {
                event.filters.push($.Deferred().resolve({
                    completenesses: this.getFormData().meta.completenesses,
                    family: this.getFormData().family
                }).then(function (completenesses) {
                    if (null === completenesses.family) {
                        return $.Deferred().resolve([]);
                    }

                    var channelCompletenesses = _.findWhere(
                        completenesses.completenesses,
                        {channel: UserContext.get('catalogScope')}
                    );

                    if (undefined === channelCompletenesses ||
                        undefined === channelCompletenesses.locales[UserContext.get('catalogLocale')]
                    ) {
                        return $.Deferred().resolve([]);
                    }

                    var missingAttributeCodes = _.pluck(
                        channelCompletenesses.locales[UserContext.get('catalogLocale')].missing,
                        'code'
                    );

                    return fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(missingAttributeCodes);
                })
                .then(function (missingAttributes) {
                    return function (attributes) {
                        return _.filter(missingAttributes, function (missingAttribute) {
                            return _.contains(_.pluck(attributes, 'code'), missingAttribute.code);
                        });
                    };
                }));
            },

            /**
             * {@inheritDoc}
             */
            addFieldExtension: function (event) {
                event.promises.push(
                    toFillFieldProvider.getFields(this.getRoot(), this.getFormData()).then(function (fields) {
                        var field = event.field;

                        if (_.contains(fields, field.attribute.code)) {
                            field.addElement(
                                'badge',
                                'completeness',
                                '<span class="AknBadge AknBadge--round AknBadge--highlight"></span>'
                            );
                        }

                        return event;
                    }.bind(this))
                );

                return this;
            }
        });
    }
);
