'use strict';

/**
 * Extension to display breadcrumbItems on every page
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'jquery',
  'oro/translator',
  'pim/form',
  'oro/mediator',
  'pim/form-registry',
  'pim/common/property',
  'react',
  'akeneo-design-system',
], function ($, __, {BaseForm}, mediator, FormRegistry, propertyAccessor, React, {Breadcrumb}) {
  return BaseForm.extend({
    events: {
      'click .breadcrumb-tab': 'redirectTab',
      'click .breadcrumb-item': 'redirectItem',
    },

    /**
     * {@inheritdoc}
     *
     * @param {string} config.tab The main tab to highlight
     * @param {string} [config.item] The sub item to highlight (optional)
     */
    initialize: function (config) {
      this.config = config.config;

      return BaseForm.prototype.initialize.apply(this, arguments);
    },

    /**
     * This method will configure the breadcrumb. The configuration of this module contains backbone extension
     * codes related to the menu. To avoid duplication of the labels, we load the configuration of these modules
     * to bring back the labels into this module.
     *
     * {@inheritdoc}
     */
    configure: function () {
      mediator.trigger('pim_menu:highlight:tab', {extension: this.config.tab});
      mediator.trigger('pim_menu:highlight:item', {extension: this.config.item});

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      return $.when(FormRegistry.getFormMeta(this.config.tab), FormRegistry.getFormMeta(this.config.item)).then(
        function (metaTab, metaItem) {
          var breadcrumbTab = {code: this.config.tab, label: __(metaTab.config.title)};
          var breadcrumbItem = null;
          if (undefined !== metaItem) {
            breadcrumbItem = {code: this.config.item, label: __(metaItem.config.title), active: true};
          }
          if (
            undefined !== this.config.itemPath &&
            null !== propertyAccessor.accessProperty(this.getFormData(), this.config.itemPath)
          ) {
            const item = propertyAccessor.accessProperty(this.getFormData(), this.config.itemPath);

            breadcrumbItem = {code: item, label: item, active: false};
          }

          const tab = React.createElement(Breadcrumb.Step, {className: 'breadcrumb-tab'}, breadcrumbTab.label);
          const children = [tab];

          if (null !== breadcrumbItem) {
            children.push(React.createElement(Breadcrumb.Step, {className: 'breadcrumb-item'}, breadcrumbItem.label));
          }

          this.renderReact(Breadcrumb, {children}, this.el);

          this.delegateEvents();
        }.bind(this)
      );
    },

    /**
     * Redirects to the linked tab
     */
    redirectTab: function () {
      mediator.trigger('pim_menu:redirect:tab', {extension: this.config.tab});
    },

    /**
     * Redirects to the linked item
     */
    redirectItem: function () {
      mediator.trigger('pim_menu:redirect:item', {extension: this.config.item});
    },
  });
});
