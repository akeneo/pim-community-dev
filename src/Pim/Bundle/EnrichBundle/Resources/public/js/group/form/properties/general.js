
/**
 * Module used to display the generals properties of a group
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import FetcherRegistry from 'pim/fetcher-registry'
import template from 'pim/template/group/tab/properties/general'
import 'jquery.select2'
export default BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(this.template({
      model: this.getFormData(),
      sectionTitle: __('pim_enrich.form.group.tab.properties.general'),
      codeLabel: __('pim_enrich.form.group.tab.properties.code'),
      typeLabel: __('pim_enrich.form.group.tab.properties.type'),
      __: __
    }))

    this.$el.find('select.select2').select2({})

    this.renderExtensions()
  }
})
