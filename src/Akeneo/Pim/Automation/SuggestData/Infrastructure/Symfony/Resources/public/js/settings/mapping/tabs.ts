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
  tabs: { label: string, route: string, checkAllowed: boolean }[];
  selected: number|null;
}

class Tabs extends BaseView {
  readonly template = _.template(template);
  readonly config: Config = {
    tabs: [],
    selected: null
  };
  stateFullAllowed: boolean[];

  /**
   * {@inheritdoc}
   */
  public events() {
    return {
      'click .tab-link': (event: { currentTarget: any }) => {
        const index = parseInt($(event.currentTarget).data('index') + '');
        if (this.checkAllowed(index)) {
          const tabConfig = this.config.tabs[index];
          Router.redirectToRoute(tabConfig.route);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: Config}) {
    super(options);
    this.stateFullAllowed = [];

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  configure(): JQueryPromise<any> {
    return $.when(
      BaseView.prototype.configure.apply(this, arguments)
    ).then(() => {
      this.listenTo(
        this.getRoot(),
        'pim_enrich:form:entity:post_save',
        () => {
          this.stateFullAllowed = [];
          this.render();
        }
      );
    })
  }

  /**
   * Check if the user can click on the tab.
   * He can click on a tab only if there is at least 1 not null field.
   * This method is stateful to prevent refresh during form filling.
   *
   * @param {number} index
   *
   * @returns {boolean}
   */
  public checkAllowed(index: number): boolean {
    if (this.stateFullAllowed[index] === undefined) {
      this.stateFullAllowed[index] = this.checkAllowedInner(index);
    }

    return this.stateFullAllowed[index];
  }

  /**
   * @param {number} index
   * @returns {boolean}
   */
  private checkAllowedInner(index: number): boolean {
    if (this.config.tabs[index].checkAllowed !== true) {
      return true;
    }

    const formData = this.getFormData();
    for (let i = 0; i < Object.keys(formData).length; i++) {
      if (formData[Object.keys(formData)[i]] !== null
        && formData[Object.keys(formData)[i]] !== '') {
        return true;
      }
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.html(this.template({
      tabs: this.config.tabs,
      selected: this.config.selected,
      checkAllowed: this.checkAllowed.bind(this),
      __
    }));
    this.delegateEvents();

    return BaseView.prototype.render.apply(this, arguments);
  }
}

export = Tabs
