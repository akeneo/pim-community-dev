'use strict';

import {ViewOptions} from 'backbone';
import {NavigationEntry, PimNavigation, SubNavigationSection, SubNavigationType} from '@akeneo-pim-community/shared';
import View from '../view/base-interface';
import React from 'react';
import * as DSM from 'akeneo-design-system';

const BaseForm = require('pim/form');
const _ = require('underscore');
const template = require('pim/template/menu/menu');
const mediator = require('oro/mediator');
const featureFlags = require('pim/feature-flags');

type SubEntry = {
  code: string;
  position: number;
  title: string;
  route: string;
  routeParams?: {[key: string]: any};
  target: string;
  sectionCode: string;
};

type EntryView = View & {
  config: {
    title: string;
    to?: string;
    isLandingSectionPage?: boolean;
    tab?: string;
    icon: string;
    align?: 'bottom';
    disabled?: boolean;
  };
  items: SubEntry[];
  sections: any[];
  hasChildren?: () => boolean;
};

// @fixme Define what is an entry column
type EntryColumnView = View & {
  config: {
    title: string;
    to?: string;
    tab?: string;
    navigationTitle?: string;
    backLink: {
      title: string;
      route: string;
    };
    stateCode?: string;
  };
  navigationItems: SubEntry[];
  sections: any[];
};

class Menu extends BaseForm {
  template = _.template(template);
  activeEntryCode: string | null;
  activeSubEntryCode: string | null;

  constructor(options?: ViewOptions<any>) {
    super({
      ...options,
      className: 'AknHeader',
    });

    this.activeEntryCode = null;
    this.activeSubEntryCode = null;
  }

  configure() {
    mediator.on('pim_menu:highlight:tab', this.highlightTab, this);
    mediator.on('pim_menu:highlight:item', this.highlightItem, this);
    mediator.on('pim_menu:hide', this.hideSubNavigation, this);

    return super.configure();
  }

  /**is
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
        activeEntryCode: this.activeEntryCode,
        activeSubEntryCode: this.activeSubEntryCode,
        freeTrialEnabled: featureFlags.isEnabled('free_trial'),
      },
      this.el
    );

    return this;
  }

  findMainEntries(): NavigationEntry[] {
    const navigationEntriesExtensions: any = Object.values(this.extensions).filter((extension: View) => {
      if (extension.targetZone !== 'mainMenu') {
        return false;
      }

      return extension.code !== 'pim-menu-logo';
    });

    navigationEntriesExtensions.sort((entryA: any, entryB: any) => {
      return entryA.position - entryB.position;
    });

    return this.buildMainMenuEntries(navigationEntriesExtensions);
  }

  buildMainMenuEntries(navigationEntriesExtensions: EntryView[]): NavigationEntry[] {
    return navigationEntriesExtensions
      .filter((extension: EntryView) => {
        return (
          extension.config.disabled ||
          !(
            typeof extension.config === 'object' &&
            (!extension.config.to || extension.config.isLandingSectionPage) &&
            typeof extension.hasChildren === 'function' &&
            !extension.hasChildren()
          )
        );
      })
      .map((extension: EntryView) => {
        const {title, isLandingSectionPage, icon, align} = extension.config;

        return {
          code: extension.code,
          title: title,
          disabled: extension.config.disabled || false,
          route: this.findEntryRoute(extension),
          // @ts-ignore
          icon: React.createElement(DSM[icon] ? DSM[icon] : 'span', {
            shapeRendering: icon === 'CardIcon' ? 'crispEdges' : undefined,
          }),
          subNavigations: this.findMainEntrySubNavigations(extension.code),
          isLandingSectionPage: isLandingSectionPage ?? false,
          align: align,
        };
      });
  }

  findMainEntrySubNavigations(entryCode: string): SubNavigationType[] {
    const columns = Object.values(this.extensions).filter((extension: EntryView) => {
      return extension.targetZone === 'column' && extension.config.tab === entryCode;
    });

    return columns.map((column: EntryColumnView) => {
      column.sections.sort((sectionA: any, sectionB: any) => {
        return sectionA.position - sectionB.position;
      });

      const sections: SubNavigationSection[] = column.sections.map(section => {
        return {
          code: section.code,
          title: section.title,
        };
      });

      let backLink;
      if (column.config.backLink) {
        backLink = {
          title: column.config.backLink.title,
          route: column.config.backLink.route,
        };
      }

      return {
        title: column.config.navigationTitle,
        sections: sections,
        entries: column.navigationItems,
        backLink: backLink,
        stateCode: column.config.stateCode,
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
