'use strict';

import {ViewOptions} from 'backbone';
import {NavigationEntry, PimNavigation} from '../PimNavigation';
import View from '../view/base-interface';
import React from 'react';
import {CardIcon} from 'akeneo-design-system';

const BaseForm = require('pim/form');
const _ = require('underscore');
const template = require('pim/template/menu/menu');
const mediator = require('oro/mediator');

type SubEntry = {
  code: string;
  position: number;
  label: string;
  route: string;
  routeParams?: {[key: string]: any};
  target: string;
};

type SubEntryColumn = {
  entries: SubEntry[];
  title?: string;
}

type EntryView = View & {
  config: {
    title: string;
    to?: string;
    isLandingSectionPage?: boolean;
    tab?: string;
  };
  items: SubEntry[];
  sections: any[];
};

// @fixme Define what is an entry column
type EntryColumnView = View & {
  config: {
    title: string;
    to?: string;
    tab?: string;
    navigationTitle?: string;
  };
  navigationItems: SubEntry[];
  sections: any[];
};

/**
 * Base extension for menu
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Menu extends BaseForm {
  template = _.template(template);
  activeEntryCode;
  activeSubEntryCode;

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
      className: 'AknHeader',
    });

    this.activeEntryCode = '';
    this.activeSubEntryCode = '';
  }

  configure() {
    mediator.on('pim_menu:highlight:tab', this.highlightTab, this);
    mediator.on('pim_menu:highlight:item', this.highlightItem, this);
    mediator.on('pim_menu:hide', this.hideSubNavigation, this);

    return super.configure();
  }

  /**
   * {@inheritdoc}
   */
  render() {
    if (!this.configured) {
      return this;
    }

    this.renderReact(
      PimNavigation,
      {
        entries: this.findMainEntries(),
      },
      this.el
    );

    return this;
  }

  /**
   * {@inheritdoc}
   */
  renderExtension(extension: any) {
    if (
      !_.isEmpty(extension.options.config) &&
      (!extension.options.config.to || extension.options.config.isLandingSectionPage) &&
      _.isFunction(extension.hasChildren) &&
      !extension.hasChildren()
    ) {
      return;
    }

    super.renderExtension(extension);
  }

  findMainEntries(): NavigationEntry[] {
    console.log(this.extensions);

    const extensions = Object.values(this.extensions).filter((extension: View) => {
      if (extension.targetZone !== 'mainMenu') {
        return false;
      }

      return extension.code !== 'pim-menu-logo';
    });

    const entries: NavigationEntry[] = extensions.map((extension: EntryView, index: number) => {
      const {title, isLandingSectionPage} = extension.config;
      return {
        code: extension.code,
        label: title,
        active: extension.code === this.activeEntryCode,
        // @fixme: Find a better way to determine what is the active sub-navigation entry
        activeSubEntryCode: this.activeSubEntryCode,
        disabled: false,
        route: this.findEntryRoute(extension),
        icon: React.createElement(CardIcon),
        position: index,
        columns: this.findMainEntryColumns(extension.code),
        sections: extension.sections,
        isLandingSectionPage: isLandingSectionPage ?? false,
      };
    });

    entries.sort((entryA: NavigationEntry, entryB: NavigationEntry) => {
      return entryA.position - entryB.position;
    });

    return entries;
  }

  findMainEntryColumns(entryCode: string): SubEntryColumn[] {
    const columns = Object.values(this.extensions).filter((extension: EntryView) => {
      // @todo Ensure that we should use "tab" (it's not always defined. ex: pim-menu-connection-column)
      return extension.targetZone === 'column' && extension.config.tab === entryCode;
    });

    return columns.map((column: EntryColumnView) => {
      return {
        entries: column.navigationItems,
        // @fixme Handle columns without title
        title: column.config.navigationTitle,
      };
    });
  }

  findEntryRoute(entry: EntryView): string {
    if (entry.config.to !== undefined) {
      return entry.config.to;
    }

    if (entry.items.length > 0) {
      entry.items.sort((itemA: SubEntry, itemB: SubEntry) => {
        return itemA.position - itemB.position;
      });

      return entry.items[0].route;
    }

    return 'pim_settings_index';
  }

  highlightTab(event: any) {
    this.activeEntryCode = event.extension;

    this.render();
  }

  highlightItem(event: any) {
    this.activeSubEntryCode = event.extension || '';

    this.render();
  }

  hideSubNavigation() {
    this.activeSubEntryCode = '';

    this.render();
  }
}

export = Menu;
