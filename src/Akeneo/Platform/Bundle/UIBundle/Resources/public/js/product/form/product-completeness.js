'use strict';
/**
 * Product completeness extension
 * Displays the global completeness of the product.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'underscore',
  'oro/translator',
  'pim/router',
  'pim/form',
  'pim/i18n',
  'pim/user-context',
  'oro/mediator',
  '@akeneo-pim-community/enrichment',
], function (
  _,
  __,
  router,
  BaseForm,
  i18n,
  UserContext,
  mediator,
  {ProductCurrentCompleteness, formatCurrentCompleteness}
) {
  return BaseForm.extend({
    /**
     * {@inheritdoc}
     */
    configure: function () {
      this.listenTo(
        this.getRoot(),
        'pim_enrich:form:scope_switcher:change',
        function (scopeEvent) {
          if ('base_product' === scopeEvent.context) {
            this.renderCompleteness({scope: scopeEvent.scopeCode});
          }
        }.bind(this)
      );
      this.listenTo(
        this.getRoot(),
        'pim_enrich:form:locale_switcher:change',
        function (localeEvent) {
          if ('base_product' === localeEvent.context) {
            this.renderCompleteness({locale: localeEvent.localeCode});
          }
        }.bind(this)
      );

      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.renderCompleteness.bind(this));

      this.listenTo(this.getRoot(), 'pim_enrich:form:attributes:render:before', () => {
        mediator.trigger(new Event('ATTRIBUTES_LOADING'));
      });
      this.listenTo(this.getRoot(), 'pim_enrich:form:attributes:render:after', () => {
        mediator.trigger(new Event('ATTRIBUTES_LOADED'));
      });

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritDoc}
     */
    render: function () {
      this.renderCompleteness();

      return BaseForm.prototype.render.apply(this, arguments);
    },

    /**
     * {@inheritDoc}
     *
     * @param options Object
     * @param options.locale String
     * @param options.scope  String
     */
    renderCompleteness: function (event) {
      const options = Object.assign(
        {},
        {
          locale: UserContext.get('catalogLocale'),
          scope: UserContext.get('catalogScope'),
        },
        event
      );

      const currentLocale = options.locale;
      const rawCurrentCompleteness = this.getCurrentCompletenesses(options.scope);
      const currentCompleteness = rawCurrentCompleteness
        ? formatCurrentCompleteness(rawCurrentCompleteness, currentLocale)
        : null;

      const product = this.getFormData();

      const redirectToAttributeTab = () => {
        this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'all');
        const tab =
          product.meta.model_type === 'product_model'
            ? 'pim-product-model-edit-form-attributes'
            : 'pim-product-edit-form-attributes';

        this.getRoot().trigger('column-tab:change-tab', {
          currentTarget: {
            dataset: {
              tab: tab,
            },
          },
          target: {
            dataset: {
              tab: tab,
            },
          },
        });
      };

      const changeLocale = localeCode => {
        this.getRoot().trigger('pim_enrich:form:locale_switcher:change', {
          localeCode: localeCode,
          context: 'base_product',
        });
      };

      this.renderReact(
        ProductCurrentCompleteness,
        {currentCompleteness, product, changeLocale, redirectToAttributeTab},
        this.el
      );

      return this;
    },

    /**
     * Returns the completeness of the current scope
     *
     * @param scope String
     *
     * @return Object
     */
    getCurrentCompletenesses: function (scope) {
      return _.findWhere(this.getFormData().meta.completenesses, {channel: scope});
    },
  });
});
