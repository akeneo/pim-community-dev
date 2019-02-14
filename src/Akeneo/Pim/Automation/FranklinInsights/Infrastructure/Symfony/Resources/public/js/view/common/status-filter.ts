/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {EventsHash} from 'backbone';
import BaseForm = require('pimui/js/view/base');
import * as _ from 'underscore';
import {Filter, FilterValue} from '../../common/filterable';

const __ = require('oro/translator');
const template = require('akeneo/franklin-insights/template/common/status-filter');

interface FilterLabel {
  value: number | string;
  label: string;
}

/**
 * This module will display a filter for the attributes-mapping grid.
 * It filters by the status of the attribute.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class StatusFilter extends BaseForm {
  /**
   * Returns the available filters
   *
   * @returns {{value: number|string, label: string}[]}
   */
  private static getFilters(): FilterLabel[] {
    return [
      {value: '', label: __('pim_common.all')},
      {value: 0, label: __('akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_insights.pending')},
      {value: 1, label: __('akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_insights.active')},
      {value: 2, label: __('akeneo_franklin_insights.entity.attributes_mapping.fields.franklin_insights.inactive')},
    ];
  }

  public readonly template = _.template(template);

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: object }) {
    super({...options, ...{className: 'AknDropdown AknFilterBox-filterContainer'}});
  }

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
      filters: StatusFilter.getFilters(),
    }));

    return this;
  }

  /**
   * Send an event to the datagrid to filter the right rows, then refresh the Dropdown label.
   *
   * @param {{currentTarget: any}} event
   */
  private filter(event: { currentTarget: any }): void {
    const value = $(event.currentTarget).data('value') as string;
    const filter: Filter = {
      value,
      type: FilterValue.Equals,
      field: 'status',
    };
    this.trigger('pim_datagrid:filter-front', filter);

    this.$el.find('.filter-criteria-hint').html(
      (StatusFilter.getFilters().find((filterLabel: FilterLabel) => {
        return filterLabel.value === value;
      }) as FilterLabel).label,
    );
  }
}

export = StatusFilter;
