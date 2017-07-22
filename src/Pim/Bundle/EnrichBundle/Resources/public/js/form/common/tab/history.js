import _ from 'underscore'
import BaseForm from 'pim/form'
import Grid from 'pim/common/grid'
import __ from 'oro/translator'

export default BaseForm.extend({
  className: 'tabbable tabs-left history',
  historyGrid: null,

  /**
   * @param {Object} meta
   */
  initialize: function (meta) {
    this.config = _.extend({}, meta.config)
    this.config.modelDependent = false
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.trigger('tab:register', {
      code: this.config.tabCode ? this.config.tabCode : this.code,
      label: __(this.config.title)
    })

    return BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.historyGrid) {
      this.historyGrid = new Grid(
        'history-grid',
        {
          object_class: this.config.class,
          object_id: this.getFormData().meta.id
        }
      )
    }

    this.$el.empty().append(this.historyGrid.render().$el)

    return this
  }
})
