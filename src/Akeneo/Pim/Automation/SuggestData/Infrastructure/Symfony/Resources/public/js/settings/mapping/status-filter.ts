import * as _ from "underscore";
import BaseForm = require('pimenrich/js/view/base');
import {EventsHash} from "backbone";
const __ = require('oro/translator');
const template = require('pimee/template/settings/mapping/status-filter');

/**
 * This module will display a filter for the attributes-mapping grid.
 * It filters by the status of the attribute.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */

interface Filter {
  value: number|string,
  label: string
}

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
  public events(): EventsHash {
    return {
      'click .option': this.filter.bind(this),
    };
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    this.$el.html(this.template({
      label: __('pim_common.status'),
      currentValue: '',
      filters: StatusFilter.getFilters()
    }));

    return this;
  }

  /**
   * Send an event to the datagrid to filter the right rows, then refresh the Dropdown label.
   *
   * @param {{currentTarget: any}} event
   */
  private filter(event: { currentTarget: any }): void {
    const value = <string> $(event.currentTarget).data('value');
    this.trigger('pim_datagrid:filter-front', {
      value,
      type: 'equals',
      field: 'status'
    });

    this.$el.find('.filter-criteria-hint').html(
      (<Filter> StatusFilter.getFilters().find((filter: Filter) => {
        return filter.value === value;
      })).label
    );
  }

  /**
   * Returns the available filters
   *
   * @returns {{value: number|string, label: string}[]}
   */
  private static getFilters(): Filter[] {
    return [
      { value: '', label: __('pim_common.all') },
      { value: 0, label: __('akeneo_suggest_data.entity.attributes_mapping.fields.suggest_data.pending') },
      { value: 1, label: __('akeneo_suggest_data.entity.attributes_mapping.fields.suggest_data.mapped') },
      { value: 2, label: __('akeneo_suggest_data.entity.attributes_mapping.fields.suggest_data.unmapped') },
    ]
  }
}

export = StatusFilter
