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
const template = require('pim/template/datagrid/filter/search-filter');

const TIMEOUT_DELAY = 250;

interface Config {
  fieldName: string;
}

/**
 * This module is a search filter but not related to a datagrid.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class FrontSearchFilter extends BaseForm {
  private readonly template = _.template(template);
  private timer: number | null = null;
  private config: Config;
  private value: string = '';

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
    super({...options, ...{ className: 'AknFilterBox-searchContainer' }});

    this.config = options.config;

    if (!this.config.hasOwnProperty('fieldName')) {
      throw new Error('fieldName should be declared as config in this module');
    }
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'keydown input[name="value"]': this.runTimeout,
      'keypress input[name="value"]': this.runTimeout,
    };
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    this.$el.html(this.template({
      label: __('pim_datagrid.search', {
        label: __('akeneo_suggest_data.entity.attributes_mapping.fields.franklin_attribute'),
      }),
      value: this.value,
    }));
    this.delegateEvents();

    this.doSearch();

    return this;
  }

  /**
   * Runs a timer to wait some time. When the time is done, it execute the search.
   * If the user types another time in the search box, it resets the timer and restart one.
   *
   * @param {Event} event
   */
  private runTimeout(event: any) {
    if (null !== this.timer) {
      clearTimeout(this.timer);
    }

    if (13 === event.keyCode) { // Enter key
      this.storeValueAndDoSearch();
    } else {
      this.timer = setTimeout(this.storeValueAndDoSearch.bind(this), TIMEOUT_DELAY);
    }
  }

  private storeValueAndDoSearch() {
    this.value = (this.$el.find('input').val() as string);

    this.doSearch();
  }

  /**
   * Trigger an event to the grid to execute the search.
   */
  private doSearch() {
    const filter: Filter = {
      value: this.value,
      type: FilterValue.Search,
      field: this.config.fieldName,
    };
    this.trigger('pim_datagrid:filter-front', filter);
  }
}

export = FrontSearchFilter;
