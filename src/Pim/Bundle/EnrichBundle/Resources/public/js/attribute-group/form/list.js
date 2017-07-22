
/**
 * Display the list of attribute groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import BaseForm from 'pim/form'
import FetcherRegistry from 'pim/fetcher-registry'
import Routing from 'routing'
import router from 'pim/router'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import template from 'pim/template/form/attribute-group/list'

export default BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),
  attributeGroups: [],
  events: {
    'click .attribute-group-link': 'redirectToGroup'
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      FetcherRegistry.getFetcher('attribute-group').fetchAll(),
      BaseForm.prototype.configure.apply(this, arguments)
    ).then(function (attributeGroups) {
      this.attributeGroups = attributeGroups
    }.bind(this))
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(this.template({
      attributeGroups: _.sortBy(_.values(this.attributeGroups), 'sort_order'),
      i18n: i18n,
      uiLocale: UserContext.get('uiLocale')
    }))

    this.$('tbody').sortable({
      handle: '.handle',
      containment: 'parent',
      tolerance: 'pointer',
      update: this.updateAttributeOrders.bind(this),
      helper: function (e, tr) {
        var $originals = tr.children()
        var $helper = tr.clone()
        $helper.children().each(function (index) {
          $(this).width($originals.eq(index).width())
        })

        return $helper
      }
    })

    this.renderExtensions()
  },

  /**
   * Update the attribute order based on the dom
   */
  updateAttributeOrders: function () {
    var sortOrder = _.reduce(this.$('.attribute-group'), function (previous, current, order) {
      var next = _.extend({}, previous)
      next[current.dataset.attributeGroupCode] = order

      return next
    }, {})

    $.ajax({
      url: Routing.generate('pim_enrich_attributegroup_rest_sort'),
      type: 'PATCH',
      data: JSON.stringify(sortOrder)
    }).then(function (attributeGroups) {
      this.attributeGroups = attributeGroups

      this.render()
    }.bind(this))
  },

  /**
   * Redirect to attribute group page
   *
   * @param {event} event
   */
  redirectToGroup: function (event) {
    router.redirectToRoute(
      'pim_enrich_attributegroup_edit',
      {
        identifier: event.target.dataset.attributeGroupCode
      }
    )
  }
})
