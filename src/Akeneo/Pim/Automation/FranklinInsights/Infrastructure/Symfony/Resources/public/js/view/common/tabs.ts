/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {EventsHash} from 'backbone';
import * as $ from 'jquery';
import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';

const __ = require('oro/translator');
const Router = require('pim/router');
const template = require('akeneo/franklin-insights/template/common/tabs');

interface Config {
  tabs: Array<{ label: string, route: string, checkAllowed: boolean }>;
  selected: number | null;
}

/**
 * This module will display tabs. Contrary to the other 'tabs' module, this one does not load
 * the tab content in the render. Each click on a tab will call the Router to load a new URL.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class Tabs extends BaseView {
  private readonly template = _.template(template);
  private readonly config: Config = {
    tabs: [],
    selected: null,
  };
  private stateFullAllowed: boolean[];

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
    super(options);
    this.stateFullAllowed = [];

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .tab-link': (event: { currentTarget: any }) => {
        const index = parseInt($(event.currentTarget).data('index') + '');
        if (this.checkAllowed(index)) {
          const tabConfig = this.config.tabs[index];
          Router.redirectToRoute(tabConfig.route);
        }
      },
    };
  }

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    return $.when(
      BaseView.prototype.configure.apply(this, arguments),
    ).then(() => {
      this.listenTo(
        this.getRoot(),
        'pim_enrich:form:entity:post_save',
        () => {
          this.stateFullAllowed = [];
          this.render();
        },
      );
    });
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
   * {@inheritdoc}
   */
  public render(): BaseView {
    this.$el.html(this.template({
      tabs: this.config.tabs,
      selected: this.config.selected,
      checkAllowed: this.checkAllowed.bind(this),
      __,
    }));
    this.delegateEvents();

    return BaseView.prototype.render.apply(this, arguments);
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
}

export = Tabs;
