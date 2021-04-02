'use strict';

define([
  'jquery',
  'underscore',
  'pim/router',
  'pim/dashboard/abstract-widget',
  'pimee/templates/dashboard/proposal-widget',
  'pimee/templates/dashboard/view-all-btn',
], function($, _, router, AbstractWidget, widgetTemplate, widgetTemplateHeader) {
  return AbstractWidget.extend({
    tagName: 'table',
    id: 'proposal-widget',
    viewAllTitle: 'View all proposals',
    className: 'table table-condensed table-light groups unspaced',
    template: _.template(widgetTemplate),
    viewAllLinkTemplate: _.template(widgetTemplateHeader),

    options: {
      contentLoaded: false,
    },

    events: {
      'click .product-label': 'followLink',
      'click .product-review': 'productReview',
    },

    /**
     * Redirect to the product the draft corresponds too.
     *
     * @param {Object} event
     */
    followLink: function(event) {
      event.preventDefault();

      if ($(event.currentTarget).data('redirecttab')) {
        sessionStorage.setItem('redirectTab', $(event.currentTarget).data('redirecttab'));
      }

      router.redirect($(event.currentTarget).data('product-view-url'));
    },

    /**
     * Redirect to the review page of the draft.
     *
     * @param {Object} event
     */
    productReview: function(event) {
      event.preventDefault();
      router.redirect($(event.currentTarget).data('product-review-url'));
    },

    /**
     * {@inheritdoc}
     */
    setElement: function() {
      AbstractWidget.prototype.setElement.apply(this, arguments);

      this._addViewAllLink();

      return this;
    },

    /**
     * {@inheritdoc}
     */
    _processResponse: function(data) {
      this.options.contentLoaded = true;

      return data;
    },

    /**
     * {@inheritdoc}
     */
    _afterLoad: function() {
      AbstractWidget.prototype._afterLoad.apply(this, arguments);

      var btn = this._getViewAllBtn();

      if (_.isEmpty(this.data)) {
        btn.hide();
      } else {
        btn.show();
      }
    },

    /**
     * Add the link that redirects to the proposal main page, hidden by
     * default, and only if there is proposals to review.
     */
    _addViewAllLink: function() {
      var $btn = this._getViewAllBtn();

      if (0 < $btn.length) {
        return;
      }

      var $viewAllBtn = $(this.viewAllLinkTemplate({title: this.viewAllTitle}));

      this.$el
        .parent()
        .siblings('.widget-header')
        .append($viewAllBtn);
      $viewAllBtn.on('click', _.bind(this._viewAll, this));
    },

    /**
     * Creates a link that redirects to the proposal main page.
     */
    _viewAll: function(event) {
      event.preventDefault();

      router.redirectToRoute('pimee_workflow_proposal_index');
    },

    /**
     * Returns the view all button
     *
     * @return {jQuery}
     */
    _getViewAllBtn: function() {
      return $('.view-all-btn[title="' + this.viewAllTitle + '"]');
    },
  });
});
