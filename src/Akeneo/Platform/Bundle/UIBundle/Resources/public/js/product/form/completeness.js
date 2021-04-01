'use strict';
/**
 * Completeness panel extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'pim/form',
  'pim/template/product/completeness',
  'pim/fetcher-registry',
  'pim/i18n',
  'pim/user-context',
  '@akeneo-pim-community/activity/src/components/ChannelLocalesCompletenesses',
  'pim/product/completeness/format-product-completeness',
], function (
  $,
  _,
  __,
  BaseForm,
  template,
  FetcherRegistry,
  i18n,
  UserContext,
  {ChannelLocalesCompletenesses},
  {formatProductCompleteness}
) {
  return BaseForm.extend({
    template: _.template(template),
    className: 'panel-pane completeness-panel AknCompletenessPanel',
    initialFamily: null,

    /**
     * {@inheritdoc}
     */
    initialize: function () {
      this.initialFamily = null;

      BaseForm.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    configure: function () {
      this.trigger('tab:register', {
        code: this.code,
        label: __('pim_enrich.entity.product.module.completeness.title'),
      });

      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);
      this.listenTo(UserContext, 'change:catalogLocale', this.render);

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      if (!this.configured || this.code !== this.getParent().getCurrentTab()) {
        return this;
      }

      if (this.getFormData().meta) {
        const catalogLocale = UserContext.get('catalogLocale');
        const sortedCompleteness = this.sortCompleteness(this.getFormData().meta.completenesses);
        const completenessList = formatProductCompleteness(sortedCompleteness, catalogLocale);

        this.renderReact(ChannelLocalesCompletenesses, {data: completenessList}, this.el);
      }

      return this;
    },

    /**
     * Sort completenesses. Put the user current catalog scope first.
     *
     * @param completenesses
     *
     * @returns {Array}
     */
    sortCompleteness: function (completenesses) {
      if (_.isEmpty(completenesses)) {
        return [];
      }
      var sortedCompleteness = [_.findWhere(completenesses, {channel: UserContext.get('catalogScope')})];

      return _.union(sortedCompleteness, completenesses);
    },

    /**
     * On family change listener
     */
    onChangeFamily: function () {
      if (!_.isEmpty(this.getRoot().model._previousAttributes)) {
        var data = this.getFormData();
        data.meta.completenesses = [];
        this.setData(data);

        this.render();
      }
    },
  });
});
