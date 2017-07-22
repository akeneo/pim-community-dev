/**
 * Attribute selector
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import i18n from 'pim/i18n'
import userContext from 'pim/user-context'
import fetcherRegistry from 'pim/fetcher-registry'
import template from 'pim/template/export/product/edit/content/structure/attributes-selector'
import attributeListTemplate from 'pim/template/export/product/edit/content/structure/attribute-list'

export default Backbone.View.extend({
  events: {
    'click .attribute-groups li': 'changeAttributeGroup',
    'keyup .search-field': 'updateSearch',
    'click .clear': 'clear',
    'click .remove': 'removeAttribute'
  },
  search: '',
  curentFetchId: 0,
  attributeListPage: 1,
  isFetching: false,
  selected: [],
  currentGroup: null,
  template: _.template(template),
  attributeListTemplate: _.template(attributeListTemplate),

  initialize: function () {
    this.listenTo(this, 'selected:update:after', function (selected) {
      this.$('.empty-message')
        .addClass(selected.length === 0 ? '' : 'AknMessageBox--hide')
        .removeClass(selected.length === 0 ? 'AknMessageBox--hide' : '')
    })
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    $.when(
      fetcherRegistry.getFetcher('attribute-group').fetchAll(),
      fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(this.getSelected())
    ).then(function (attributeGroups, selectedAttributes) {
      var scrollPositions = {}
      _.each(this.$('[data-scroll-container]'), function (element) {
        scrollPositions[element.dataset.scrollContainer] = element.scrollTop
      })
      this.attributeListPage = 1
      this.isFetching = false

      var attributeCount
      if (this.currentGroup === null) {
        attributeCount = _.reduce(attributeGroups, function (count, attributeGroup) {
          return count + attributeGroup.attributes.length
        }, 0)
      } else {
        attributeCount = _.findWhere(attributeGroups, {
          code: this.currentGroup
        }).attributes.length
      }

      this.$el.empty().append(this.template({
        __: __,
        i18n: i18n,
        userContext: userContext,
        attributeGroups: attributeGroups,
        attributeCount: attributeCount,
        currentGroup: this.currentGroup,
        selectedAttributes: selectedAttributes
      }))

      this.initializeSortable()

      this.updateAttributeList().then(function () {
        _.each(scrollPositions, function (scrollPosition, key) {
          this.$('[data-scroll-container="' + key + '"]').scrollTop(scrollPosition)
        }.bind(this))
      }.bind(this))

      this.$('.attributes div').on('scroll', this.updateAttributeList.bind(this))
    }.bind(this))
  },

  /**
   * Set currently selected attributes
   *
   * @param {array} selected
   */
  setSelected: function (selected) {
    this.selected = selected

    this.trigger('selected:update:after', this.selected)
  },

  /**
   * Get currently selected attributes
   *
   * @return {array}
   */
  getSelected: function () {
    return this.selected
  },

  /**
   * Change the current attribute group
   *
   * @param {event} event
   */
  changeAttributeGroup: function (event) {
    var newGroup = event.currentTarget.dataset.attributeGroupCode
    newGroup = newGroup !== '' ? newGroup : null
    this.search = ''

    this.currentGroup = newGroup

    this.render()
  },

  /**
   * Called each time the user type a letter on the search field
   */
  updateSearch: function () {
    this.search = this.$('.search-field').val()
    this.attributeListPage = 1
    this.isFetching = false
    this.$('.attributes ul').empty()
    this.updateAttributeList()
  },

  /**
   * Clear the selected attributes
   */
  clear: function () {
    this.setSelected([])
    this.search = ''

    this.render()
  },

  /**
   * Called on each render, each, search event and each scroll event
   */
  updateAttributeList: function () {
    var attributeContainer = this.$('.attributes > .AknColumnConfigurator-listContainer')
    var attributeList = attributeContainer.children('.AknVerticalList')

    var needFetching = (
      attributeList.height() - attributeContainer.scrollTop() - 2 * attributeContainer.height()
      ) < 0

    if (needFetching && !this.isFetching) {
      this.curentFetchId++
      var fetchId = Number(this.curentFetchId)
      var searchOptions = {
        search: this.search,
        options: {
          excluded_identifiers: this.getSelected(),
          page: this.attributeListPage,
          limit: 20
        }
      }

      if (this.currentGroup !== null) {
        searchOptions.options.attribute_groups = [this.currentGroup]
      }

      this.isFetching = true

      return fetcherRegistry
        .getFetcher('attribute')
        .search(searchOptions)
        .then(function (attributes) {
          attributes = _.filter(attributes, function (attribute) {
            return attribute.type !== 'pim_catalog_identifier'
          })

          if (fetchId !== this.curentFetchId) {
            return
          }
          attributeList.append(this.attributeListTemplate({
            attributes: attributes,
            i18n: i18n,
            userContext: userContext
          })
          )

          if (attributes.length !== 0) {
            this.attributeListPage++
            this.isFetching = false
          }
        }.bind(this))
    }
  },

  /**
   * Called to initialize the dropzones
   */
  initializeSortable: function () {
    this.$('.attributes ul, .selected-attributes ul').sortable({
      connectWith: '.attributes ul, .selected-attributes ul',
      containment: this.$el,
      tolerance: 'pointer',
      cursor: 'move',
      stop: function () {
        this.setSelected(_.map(this.$('.selected-attributes li'), function (element) {
          return element.dataset.attributeCode
        }))
      }.bind(this)
    }).disableSelection()
  },

  /**
   * Remove the clicked row from the selected attributes
   *
   * @param {event} event
   */
  removeAttribute: function (event) {
    var element = $(event.currentTarget).parents('li')
    var selectedAttributes = this.getSelected()

    selectedAttributes = _.without(selectedAttributes, element.data('attributeCode'))

    this.setSelected(selectedAttributes)

    this.$('.attributes > div ul').append(element)
  }
})
