import * as _ from "underscore";
import BaseForm = require('pimenrich/js/view/base');
import BaseView = require('pimenrich/js/view/base');
const __ = require('oro/translator');
const template = require('pimee/template/settings/mapping/status-filter');

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class StatusFilter extends BaseForm {
  readonly template = _.template(template);

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Object }) {
    super({...options, ...{ className: 'AknDropdown AknFilterBox-filterContainer' }});
  };

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    this.$el.html(this.template({
      label: __('pim_common.status'),
      currentValue: null,
      filters: [
        { value: null, label: __('pim_common.all') },
        { value: 'pending', label: __('akeneo_suggest_data.entity.attributes_mapping.fields.suggest_data.pending') },
        { value: 'mapped', label: __('akeneo_suggest_data.entity.attributes_mapping.fields.suggest_data.mapped') },
        { value: 'unmapped', label: __('akeneo_suggest_data.entity.attributes_mapping.fields.suggest_data.unmapped') },
      ]
    }));
    return this;
  }
}

export = StatusFilter
