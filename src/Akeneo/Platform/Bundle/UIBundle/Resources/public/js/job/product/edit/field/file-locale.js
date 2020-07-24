'use strict';

/**
 * Label locales field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/fetcher-registry',
    'pim/job/common/edit/field/select'
], function (
    $,
    _,
    __,
    FetcherRegistry,
    SelectField
) {
    return SelectField.extend({
        /**
         * {@inherit}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'job.with_label.change', () => {
                this.render();
            })

            return $.when(
                FetcherRegistry.getFetcher('locale').fetchActivated(),
                SelectField.prototype.configure.apply(this, arguments)
            ).then(function (locales) {
                this.config.options = locales.reduce((result, locale) => ({...result, [locale.code]: locale.label}), {});
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
          if (!this.getFormData().configuration.with_label) {
              this.$el.html('');

              return this;
          }

          SelectField.prototype.render.apply(this, arguments);
        },
    });
});
