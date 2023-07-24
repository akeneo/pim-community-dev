'use strict';

/**
 * This extension allows user to display a fullscreen item picker.
 * It overrides the default item picker because we have to manage 2 types of entities:
 * - products (identified by their identifier)
 * - product models (identifier by their code)
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'pim/common/item-picker', 'pim/media-url-generator'], function (
  _,
  ItemPicker,
  MediaUrlGenerator
) {
  return ItemPicker.extend({
    items: [],

    initialize: function () {
      this.items = [];

      return ItemPicker.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    selectModel: function (model) {
      this.addItem({
        id: model.get('identifier') ?? `[${model.get('technical_id')}]`,
        itemCode: `${model.get('document_type')};${model.get('id')}`,
        document_type: model.get('document_type'),
        technical_id: model.get('technical_id'),
        label: model.get('label'),
        image: model.get('image'),
      });
    },

    /**
     * {@inheritdoc}
     */
    unselectModel: function (model) {
      this.removeItem(`${model.get('document_type')};${model.get('id')}`);
    },

    /**
     * {@inheritdoc}
     */
    updateBasket: function () {
      this.renderBasket(this.items);
      this.delegateEvents();
    },

    /**
     * Add an item to the basket
     *
     * @param {Object} item
     *
     * @return this
     */
    addItem: function (item) {
      const items = this.items;
      items.push(item);
      this.setItems(items);

      return this;
    },

    /**
     * Remove an item from the collection
     *
     * @param {string} itemCode
     *
     * @return this
     */
    removeItem: function (itemCode) {
      let items = _.filter(this.items, item => item.itemCode !== itemCode);

      this.setItems(items);

      return this;
    },

    /**
     * Get all items in the collection
     *
     * @return {Array}
     */
    getItems: function () {
      return this.items;
    },

    /**
     * Set items
     *
     * @param {Array} items
     *
     * @return this
     */
    setItems: function (items) {
      this.items = _.uniq(items);
      this.updateBasket();

      return this;
    },

    /**
     * Remove an item from the basket (triggered by 'click .remove-item')
     *
     * @param {Event} event
     */
    removeItemFromBasket: function (event) {
      this.removeItem(event.currentTarget.dataset.itemcode);
      if (this.datagridModel) {
        this.updateChecked(this.datagridModel);
      }
    },

    /**
     * Update the checked rows in the grid according to the current model
     *
     * @param {Object} datagrid
     */
    updateChecked: function (datagrid) {
      if (datagrid.inputName !== this.datagrid.name) {
        return;
      }

      const items = this.getItems();

      datagrid.models.forEach(row => {
        if (_.some(items, item => item.itemCode === `${row.get('document_type')};${row.get('id')}`)) {
          row.set('is_checked', true);
        } else {
          row.set('is_checked', null);
        }
      });

      this.setItems(items);
    },

    /**
     * {@inheritdoc}
     */
    imagePathMethod: item => MediaUrlGenerator.getMediaShowUrl(item.image?.filePath, 'thumbnail_small'),

    /**
     * {@inheritdoc}
     */
    labelMethod: item => item.label ?? `[${item.id}]`,

    /**
     * {@inheritdoc}
     */
    itemCodeMethod: item => item.itemCode,
  });
});
