'use strict';

/**
 * Create attribute button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'backbone',
  'pim/form',
  'pim/template/form/tab/attribute/create-button',
  'pim/template/common/modal-centered',
  'pim/template/form/tab/attribute/create-modal-content',
  'routing',
  'pim/fetcher-registry',
  'pim/router',
  'pim/analytics',
  'bootstrap-modal',
], function (
  $,
  _,
  __,
  Backbone,
  BaseForm,
  template,
  templateModal,
  innerTemplateModal,
  Routing,
  FetcherRegistry,
  router,
  analytics
) {
  return BaseForm.extend({
    template: _.template(template),
    innerTemplateModal: _.template(innerTemplateModal),
    templateModal: _.template(templateModal),

    /**
     * {@inheritdoc}
     */
    initialize: function (config) {
      this.config = config.config;

      BaseForm.prototype.initialize.apply(this, arguments);
    },

    /**
     * Extracts the value of a given parameter from the actual url.
     *
     * @param {String} paramName
     * @return  {String}
     */
    getQueryParam: function (paramName) {
      var url = location.href;
      if (-1 === url.lastIndexOf('?')) {
        return '';
      }
      var params = url.substring(url.lastIndexOf('?') + 1);
      if (!params) {
        return null;
      }

      var stringParams = params.split('&');
      var objectParams = {};
      stringParams.forEach(function (stringParam) {
        var tab = stringParam.split('=');
        if (tab.length === 2) {
          objectParams[tab[0]] = tab[1];
        }
      });

      return objectParams[paramName] ?? '';
    },

    getAttributeType: function (dataRoute) {
      if (!dataRoute.includes('attribute_type')) {
        return null;
      }

      const stringParam = dataRoute.substring(dataRoute.indexOf('attribute_type'));
      const params = stringParam.split('=');
      if (params.length !== 2) {
        return null;
      }

      const tab = params[1].split('_');
      if (tab.length !== 3) {
        return null;
      }

      return tab[2];
    },

    /**
     * Create the dialog modal and bind clicks
     */
    createModal: function (attributeTypesMap) {
      var attributeTypes = this.formatAndSortAttributeTypesByLabel(attributeTypesMap);

      var moduleConfig = __moduleConfig;

      var modal = null;
      var self = this;

      var openModal = function () {
        if (modal) {
          modal.open();
        } else {
          modal = new Backbone.BootstrapModal({
            // TODO translate this
            title: __('pim_enrich.entity.attribute.property.type.choose'),
            subtitle: __('pim_enrich.entity.attribute.module.create.button'),
            content: this.innerTemplateModal({
              attributeTypes: attributeTypes,
              iconsMap: moduleConfig.attribute_icons,
              generateRoute: function (route, params) {
                var code = self.getQueryParam('code');
                if (code) {
                  params = {code: code, ...params};
                }

                return Routing.generate(route, params);
              },
            }),
            okText: '',
            template: this.templateModal,
          });
          modal.open();
        }

        modal.$el.on('click', '.attribute-choice', function () {
          const dataRoute = $(this).attr('data-route');

          modal.close();
          modal.$el.remove();

          analytics.track('attribute:create:type-selected', {
            name: self.getAttributeType(dataRoute),
          });

          router.redirect(dataRoute, {trigger: true});
        });

        modal.$el.on('click', '.cancel', () => {
          modal.close();
          modal.$el.remove();
        });
      }.bind(this);

      $('#attribute-create-button').on('click', openModal);

      if (this.getQueryParam('open_create_attribute_modal')) {
        openModal();
      }
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      FetcherRegistry.getFetcher('attribute-type')
        .fetchAll()
        .then(
          function (attributeTypes) {
            this.$el.html(
              this.template({
                buttonTitle: __(this.config.buttonTitle),
              })
            );

            this.createModal(attributeTypes);
          }.bind(this)
        );

      return this;
    },

    /**
     * Format the map to an array and sort attributeTypes by label
     * @param attributeTypesMap
     * @returns {Array}
     */
    formatAndSortAttributeTypesByLabel: function (attributeTypesMap) {
      var sortedAttributeTypesByLabel = [];
      for (var key in attributeTypesMap) {
        if (attributeTypesMap.hasOwnProperty(key)) {
          sortedAttributeTypesByLabel.push({
            code: key,
            label: __('pim_enrich.entity.attribute.property.type.' + attributeTypesMap[key]),
          });
        }
      }

      sortedAttributeTypesByLabel.sort(function (a, b) {
        return a.label.localeCompare(b.label);
      });

      return sortedAttributeTypesByLabel;
    },
  });
});
