/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';
import * as _ from 'underscore';
const noDataTemplate = require('pim/template/common/no-data');
const __ = require('oro/translator');
import BaseForm = require('pimui/js/view/base');

export enum FilterValue {
  Equals = 'equals',
  Search = 'search',
}

export interface Filter {
  value: string;
  type: FilterValue;
  field: string;
}

/**
 * Allow to filter a front-end grid.
 * Usage: in the main module, use `Filterable.set(this)`.
 * Then, once the render is done, use `Filterable.afterRender(this)`.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
export class Filterable {
  /**
   * Enable the filtering
   *
   * @param { BaseForm } baseForm
   */
  public static set(baseForm: BaseForm) {
    return $.when(
      baseForm.onExtensions('pim_datagrid:filter-front', (filter: Filter) => {
        return this.filter(baseForm, filter);
      }),
    );
  }

  /**
   * Method to call after the render.
   *
   * @param { BaseForm } baseForm
   * @param { string } entityHint
   */
  public static afterRender(baseForm: BaseForm, entityHint: string = __('pim_datagrid.entity_hint')): void {
    const template = _.template(noDataTemplate);
    const noDataHtml = template({
      __,
      imageClass: '',
      hint: __('pim_datagrid.no_results', { entityHint }),
      subHint: 'pim_datagrid.no_results_subtitle',
    });

    baseForm.$el.append(noDataHtml);

    this.toggleNoDataMessage(baseForm);
  }

  /**
   * Filters the rows with a filter.
   * Each row contains a 'data' element called 'active-filters'. This element contains a list of filters. A filter is
   * contained in this row if it is hidden by this filter. The row is displayed if there is no active filters in it,
   * i.e. the active filters are empty.
   *
   * @param { BaseForm } baseForm
   * @param { Filter } filter
   */
  private static filter(baseForm: BaseForm, filter: Filter): void {
    baseForm.$el.find('.searchable-row').each((_i: number, row: any) => {
      const value = $(row).data(filter.field);
      let filteredByThisFilter = false;

      switch (filter.type) {
        case FilterValue.Equals: filteredByThisFilter = !this.filterEquals(filter.value, value); break;
        case FilterValue.Search: filteredByThisFilter = !this.filterSearch(filter.value, value); break;
      }

      let filters = $(row).data('active-filters');
      if (undefined === filters) {
        filters = [];
      }
      if ((filters.indexOf(filter.field) < 0) && filteredByThisFilter) {
        filters.push(filter.field);
      } else if ((filters.indexOf(filter.field) >= 0) && !filteredByThisFilter) {
        filters.splice(filters.indexOf(filter.field), 1);
      }
      $(row).data('active-filters', filters);

      filters.length > 0 ? $(row).hide() : $(row).show();

      this.toggleNoDataMessage(baseForm);
    });
  }

  /**
   * Toggle the "there is no data" message regarding the number of visible rows.
   *
   * @param { BaseForm } baseForm
   */
  private static toggleNoDataMessage(baseForm: BaseForm): void {
    baseForm.$el.find('.searchable-row:visible').length ?
      baseForm.$el.find('.no-data-inner').hide() :
      baseForm.$el.find('.no-data-inner').show();
  }

  /**
   * Returns true if the values are the same.
   *
   * @param {string} filterValue
   * @param {string} rowValue
   * @returns {boolean}
   */
  private static filterEquals(filterValue: string, rowValue: string): boolean {
    return filterValue === '' || filterValue === rowValue;
  }

  /**
   * Return if the row matches the search filter by words. If the user types 'foo bar', it will look for every row
   * containing the strings 'foo' and 'bar', no matter the order of the words.
   *
   * @param {string} filterValue
   * @param {string} rowValue
   * @returns {boolean}
   */
  private static filterSearch(filterValue: string, rowValue: string): boolean {
    const words: string[] = filterValue.split(' ').map((word: string) => {
      return word.toLowerCase();
    });

    return words.reduce((acc, word) => {
      return acc && rowValue.toLowerCase().indexOf(word) >= 0;
    }, true);
  }
}
