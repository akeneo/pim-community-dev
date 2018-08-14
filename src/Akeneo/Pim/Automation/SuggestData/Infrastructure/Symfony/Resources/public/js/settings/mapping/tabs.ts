import * as _ from 'underscore';
import * as $ from 'jquery';
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const Router = require('pim/router');
const template = require('pimee/template/settings/mapping/tabs');

/**
 * This module will display tabs. Contrary to the other 'tabs' module, this one does not load
 * the tab content in the render. Each click on a tab will call the Router to load a new URL.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class Tabs extends BaseForm {
  readonly template = _.template(template);

  constructor(config: any) {
    super(config);
    this.events = {
      'click .tab-link': (event: { currentTarget: any }) => {
        Router.redirectToRoute($(event.currentTarget).data('route'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  initialize(meta: { config: { tabs: string[], selected: number } }) {
    BaseForm.prototype.initialize.apply(this, arguments);
    this.config = meta.config;
  }

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.html(this.template({
      tabs: this.config.tabs,
      selected: this.config.selected,
      __
    }));
    this.delegateEvents();
    
    return BaseForm.prototype.render.apply(this, arguments);
  }
}

export = Tabs
