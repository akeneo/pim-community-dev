import {EventsHash} from 'backbone';
import BaseForm = require('pimenrich/js/view/base');
import * as _ from 'underscore';
const __ = require('oro/translator');
const template = require('pim/template/datagrid/filter/search-filter');

const TIMEOUT_DELAY = 250;

/**
 * This module is a search filter but not related to a datagrid.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class FrontSearchFilter extends BaseForm {
  public readonly template = _.template(template);
  private timer: number|null = null;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Object }) {
    super({...options, ...{ className: 'AknFilterBox-searchContainer' }});
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
        label: __('akeneo_suggest_data.entity.attributes_mapping.fields.pim_ai_attribute'),
      }),
    }));

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
      this.doSearch();
    } else {
      this.timer = setTimeout(this.doSearch.bind(this), TIMEOUT_DELAY);
    }
  }

  /**
   * Trigger an event to the grid to execute the search.
   */
  private doSearch() {
    const value = (this.$el.find('input').val() as string);
    this.trigger('pim_datagrid:filter-front', {
      value,
      type: 'search',
      field: 'pim_ai_attribute',
    });
  }
}

export = FrontSearchFilter;
