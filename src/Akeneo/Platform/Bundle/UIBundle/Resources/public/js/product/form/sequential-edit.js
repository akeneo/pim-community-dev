'use strict';
/**
 * Sequential edit extension
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
  'backbone',
  'oro/mediator',
  'pim/form',
  'pim/template/product/sequential-edit',
  'routing',
  'pim/router',
  'pim/fetcher-registry',
  'pim/user-context',
  'pim/provider/sequential-edit-provider',
  'bootstrap',
], function (
  $,
  _,
  __,
  Backbone,
  mediator,
  BaseForm,
  template,
  Routing,
  router,
  FetcherRegistry,
  UserContext,
  sequentialEditProvider
) {
  const findObjectIndex = (objects, id, type) => {
    return objects.findIndex(item => item.id + '' === id + '' && item.type === type);
  };

  const getObjectViewParams = object => {
    const label = object.meta.label[UserContext.get('catalogLocale')];

    return {
      id: object.meta.id,
      type: object.meta.model_type,
      label: label,
      shortLabel: label.length > 25 ? label.slice(0, 22) + '...' : label,
    };
  };

  return BaseForm.extend({
    id: 'sequentialEdit',
    className: 'AknSequentialEdit AknDefault-bottomPanel',
    template: _.template(template),
    events: {
      'click .next, .previous': 'followLink',
    },
    initialize: function () {
      this.model = new Backbone.Model();

      BaseForm.prototype.initialize.apply(this, arguments);
    },
    configure: function () {
      const objectSet = sequentialEditProvider.get();
      const sequentialEditCurrentIndex = sequentialEditProvider.getIndex();

      this.model.set({objectSet});
      this.model.set({sequentialEditCurrentIndex});

      return BaseForm.prototype.configure.apply(this, arguments);
    },
    addSaveButton: function () {
      this.trigger('save-buttons:register-button', {
        className: 'save-and-continue',
        priority: 250,
        label:
          undefined !== this.getNextObject()
            ? __('pim_enrich.entity.product.module.sequential_edit.save_and_next')
            : __('pim_enrich.entity.product.module.sequential_edit.save_and_finish'),
        events: {
          'click .save-and-continue': this.saveAndContinue.bind(this),
        },
      });
    },
    render: function () {
      if (!this.configured || !this.model.get('objectSet') || 0 === this.model.get('objectSet').length) {
        this.$el.addClass('AknDefault-bottomPanel--hidden');

        return this;
      } else {
        this.$el.removeClass('AknDefault-bottomPanel--hidden');
      }

      this.addSaveButton();

      this.getRoot().trigger('pim_enrich:form:sequential-edit', {isLast: undefined === this.getNextObject()});

      this.getTemplateParameters().done(
        function (templateParameters) {
          this.$el.html(this.template(templateParameters));
          this.$('[data-toggle="tooltip"]').tooltip();
          this.delegateEvents();
          this.preloadNext();
        }.bind(this)
      );

      return this;
    },
    getTemplateParameters: function () {
      const objectSet = this.model.get('objectSet');
      const index = this.getCurrentIndex();
      const previous = objectSet[index - 1];
      const next = objectSet[index + 1];

      var previousObject = null;
      var nextObject = null;

      var promises = [];
      if (previous) {
        promises.push(
          this.getEntity(previous.type, previous.id).then(function (product) {
            previousObject = getObjectViewParams(product);
          })
        );
      }
      if (next) {
        promises.push(
          this.getEntity(next.type, next.id).then(function (product) {
            nextObject = getObjectViewParams(product);
          })
        );
      }

      return $.when.apply($, promises).then(function () {
        return {
          objectCount: objectSet.length,
          currentIndex: index + 1,
          previousObject: previousObject,
          nextObject: nextObject,
          ratio: ((index + 1) / objectSet.length) * 100,
        };
      });
    },
    preloadNext: function () {
      var objectSet = this.model.get('objectSet');
      var currentIndex = this.getCurrentIndex();
      var pending = objectSet[currentIndex + 2];
      if (pending) {
        setTimeout(() => {
          this.getEntity(pending.type, pending.id);
        }, 2000);
      }
    },
    saveAndContinue: function () {
      this.parent.getExtension('save').save({silent: true}).done(this.continue.bind(this));
    },
    continue: function () {
      var nextObject = this.getNextObject();
      if (nextObject) {
        this.goToProduct(nextObject.type, nextObject.id);
      } else {
        this.finish();
      }
    },
    followLink: function (event) {
      this.getRoot().trigger('pim_enrich:form:state:confirm', {
        action: function () {
          this.goToProduct(event.currentTarget.dataset.type, event.currentTarget.dataset.id);
        }.bind(this),
      });
    },
    goToProduct: function (type, id) {
      const objectSet = this.model.get('objectSet');
      const sequentialEditCurrentIndex = findObjectIndex(objectSet, id, type);
      if (sequentialEditCurrentIndex !== -1) {
        sequentialEditProvider.setIndex(sequentialEditCurrentIndex);
      }

      if (type === 'product') {
        router.redirectToRoute('pim_enrich_product_edit', {uuid: id});
      } else {
        router.redirectToRoute('pim_enrich_product_model_edit', {id});
      }
    },
    finish: function () {
      router.redirectToRoute('pim_enrich_product_index');
    },
    getEntity(type, id) {
      return FetcherRegistry.getFetcher(type.replace('_', '-')).fetch(id);
    },
    getNextObject: function () {
      const objectSet = this.model.get('objectSet');
      const currentIndex = this.getCurrentIndex();

      return objectSet[currentIndex + 1];
    },
    getCurrentIndex: function () {
      return this.model.get('sequentialEditCurrentIndex');
    },
  });
});
