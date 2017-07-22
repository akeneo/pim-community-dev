import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import Routing from 'routing'
import LoadingMask from 'oro/loading-mask'
import mediator from 'oro/mediator'
import 'pim/router'

export default Backbone.View.extend({
  defaults: {
    delayedLoadTimeout: 1000,
    minRefreshInterval: 20000,
    alias: null
  },

  options: {},

  data: {},

  loadingMask: null,

  $refreshBtn: null,

  loadTimeout: null,

  needsData: true,

  refreshBtnTemplate: _.template(
    '<span class="AknButtonList-item AknIconButton AknIconButton--grey btn-refresh">' +
    '<i class="icon-refresh"></i>' +
    '</span>'
  ),

  initialize: function (options) {
    this.options = _.extend({}, this.defaults, this.options, options)

    mediator.on('route_complete', function (loadedRoute) {
      if (loadedRoute === 'pim_dashboard_index') {
        this.needsData = true
        this.delayedLoad()
      }
    }, this)
  },

  render: function () {
    this.$el.html(this.template({
      data: this.data,
      options: this.options,
      __: __
    }))

    return this
  },

  setElement: function () {
    Backbone.View.prototype.setElement.apply(this, arguments)

    this._createLoadingMask()
    this._createRefreshBtn()

    return this
  },

  loadData: function () {
    if (!this.needsData) {
      this.loadTimeout = null

      return
    }
    this.needsData = false
    this._beforeLoad()

    $.get(Routing.generate('pim_dashboard_widget_data', {
      alias: this.options.alias
    }))
      .then(_.bind(function (resp) {
        this.data = this._processResponse(resp)
        this.render()
        this._afterLoad()
      }, this))
  },

  reload: function () {
    this.needsData = true

    this.loadData()
  },

  delayedLoad: function () {
    if (!this.loadTimeout) {
      this.loadTimeout = setTimeout(_.bind(function () {
        this.loadData()
      }, this), this.options.delayedLoadTimeout)
    }
  },

  _beforeLoad: function () {
    this.$el.parent().addClass('loading')
    this.$refreshBtn.prop('disabled', true).find('i').addClass('icon-spin')
    this.loadingMask.show()
  },

  _afterLoad: function () {
    this.$el.parent().removeClass('loading')
    this.loadingMask.hide()
    this.$refreshBtn.prop('disabled', false).find('i').removeClass('icon-spin')
    this.loadTimeout = null
    setTimeout(_.bind(function () {
      this.needsData = true
    }, this), this.options.minRefreshInterval)
  },

  _createLoadingMask: function () {
    if (this.loadingMask) {
      this.loadingMask.remove()
    }
    this.loadingMask = new LoadingMask()
    this.loadingMask.render().$el.insertAfter(this.$el)
  },

  _createRefreshBtn: function () {
    if (this.$refreshBtn) {
      this.$refreshBtn.remove()
    }

    this.$refreshBtn = $(this.refreshBtnTemplate())
    this.$refreshBtn.on('click', _.bind(this.reload, this))

    this.$el.closest('.AknWidget').find('.widget-actions').append(this.$refreshBtn)
  },

  _processResponse: function (data) {
    return data
  }
})
