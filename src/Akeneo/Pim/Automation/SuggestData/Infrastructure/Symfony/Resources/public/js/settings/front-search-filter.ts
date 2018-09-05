import * as _ from "underscore";
import * as $ from 'jquery';
import BaseForm = require('pimenrich/js/view/base');
const __ = require('oro/translator');
const template = require('pim/template/datagrid/filter/search-filter');

const TIMEOUT_DELAY = 250;

/**
 * This module is a search filter but not related to a datagrid.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class FrontSearchFilter extends BaseForm {
  readonly template = _.template(template);
  private timer: number|null = null;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Object }) {
    super({...options, ...{ className: 'AknTitleContainer-search' }});
  };

  /**
   * {@inheritdoc}
   */
  public events() {
    return {
      'keydown input[name="value"]': this.runTimeout,
      'keypress input[name="value"]': this.runTimeout
    }
  };

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
  };

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.html(this.template({
      label: __('pim_datagrid.search', {
        label: __('akeneo_suggest_data.entity.family_mapping.fields.pim_ai_attribute')
      })
    }));

    return this;
  }

  /**
   * Filter the items by words. If the user types 'foo bar', it will look for every row containing the strings
   * 'foo' and 'bar', no matter the order of the words.
   */
  private doSearch() {
    const search: string = (<string> this.$el.find('input').val());
    const words: string[] = search.split(' ');

    $('.searchable-row').each((_i: number, row: any) => {
      const value = $(row).find('.searchable-value').html().trim();
      const match = words.reduce((old, word) => {
        return old && value.indexOf(word) >= 0;
      }, true);
      if (match) {
        $(row).show();
      } else {
        $(row).hide();
      }
    });
  }
}

export = FrontSearchFilter;
