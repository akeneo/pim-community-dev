'use strict';

import {ViewOptions} from 'backbone';
import {NavigationEntry, PimNavigation} from '../PimNavigation';
import View from '../view/base-interface';
import React from 'react';
import {CardIcon} from 'akeneo-design-system';

const BaseForm = require('pim/form');
const _ = require('underscore');
const template = require('pim/template/menu/menu');

type SubEntry = {
  position: number;
  label: string;
  route: string;
  routeParams?: any;
  target: string;
};

type EntryView = View & {
  config: {
    title: string;
    to?: string;
    isLandingSectionPage?: boolean;
  };
  items: SubEntry[];
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

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
      className: 'AknHeader',
    });
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
        active: false,
        disabled: false,
        route: this.findEntryRoute(extension),
        icon: React.createElement(CardIcon),
        position: index,
        items: extension.items,
        sections: extension.sections,
        isLandingSectionPage: isLandingSectionPage ?? false,
      };
    });

    entries.sort((entryA: NavigationEntry, entryB: NavigationEntry) => {
      return entryA.position - entryB.position;
    });

    return entries;
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
}

export = Menu;
