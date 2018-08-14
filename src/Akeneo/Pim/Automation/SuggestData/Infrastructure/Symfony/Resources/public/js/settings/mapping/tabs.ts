import * as _ from 'underscore';
import * as $ from 'jquery';
import BaseView = require('pimenrich/js/view/base');
const __ = require('oro/translator');
const Router = require('pim/router');
const template = require('pimee/template/settings/mapping/tabs');

/**
 * This module will display tabs. Contrary to the other 'tabs' module, this one does not load
 * the tab content in the render. Each click on a tab will call the Router to load a new URL.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
interface Config {
  tabs: string[];
  selected: number|null;
}

class Tabs extends BaseView {
  readonly template = _.template(template);
  readonly config: Config = {
    tabs: [],
    selected: null
  };

  /**
   * {@inheritdoc}
   */
  public events() {
    return {
      'click .tab-link': (event: { currentTarget: any }) => {
        Router.redirectToRoute($(event.currentTarget).data('route'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: Config}) {
    super(options);

    this.config = {...this.config, ...options.config};
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

    return BaseView.prototype.render.apply(this, arguments);
  }
}

export = Tabs
