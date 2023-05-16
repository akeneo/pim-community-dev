import {Locale, LocaleSelector} from '@akeneo-pim-community/shared';
import React from 'react';
import ReactDOM from 'react-dom';
import BaseView = require('pimui/js/view/base');
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const _ = require('underscore');
const __ = require('oro/translator');
const FetcherRegistry = require('../../fetcher/fetcher-registry');
const localeFetcher = FetcherRegistry.getFetcher('locale');
const userContext = require('pim/user-context');
const analytics = require('pim/analytics');

class LocaleSwitcher extends BaseView {
  private config: any;
  // @ts-ignore
  private displayInline: boolean;
  // @ts-ignore
  private displayLabel: boolean;

  initialize(config: any): void {
    this.config = config.config;
    BaseView.prototype.initialize.apply(this, arguments);
  }

  configure(): JQueryPromise<any> {
    this.trigger('pim_enrich:form:locale_switcher:change', {
      code: this.code,
      label: __(this.config.label),
    });

    return super.configure();
  }

  changeLocale(localeCode: string): void {
    const context = this.config.context;

    this.getRoot().trigger('pim_enrich:form:locale_switcher:change', {
      localeCode: localeCode,
      context: context,
    });

    analytics.appcuesTrack('product:form:locale-switched', {
      localeCode: localeCode,
      context: context,
    });

    this.render();
  }

  render(): any {
    this.getDisplayedLocales().done((locales: Locale[]) => {
      this.$el.removeClass('open');
      let defaultLocaleCode = _.first(locales).code;
      const catalogLocaleCode = locales.find(locale => {
        return locale.code === userContext.get('catalogLocale');
      });

      if (catalogLocaleCode) {
        defaultLocaleCode = catalogLocaleCode.code;
      }

      const params = {
        localeCode: defaultLocaleCode,
        context: this.config.context,
      };

      this.getRoot().trigger('pim_enrich:form:locale_switcher:pre_render', params);

      let currentLocale =
        params.localeCode &&
        locales.find(locale => {
          return locale.code === params.localeCode;
        });

      if (undefined === currentLocale || '' === currentLocale) {
        currentLocale = _.first(locales);
      }

      ReactDOM.render(
        <DependenciesProvider>
          <ThemeProvider theme={pimTheme}>
            <LocaleSelector value={currentLocale.code} values={locales} onChange={this.changeLocale.bind(this)} />
          </ThemeProvider>
        </DependenciesProvider>,
        this.el
      );
      this.delegateEvents();
    });

    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }

  private getDisplayedLocales() {
    return localeFetcher.fetchActivated();
  }

  /**
   * Updates the inline display value
   *
   */
  setDisplayInline(value: boolean) {
    this.displayInline = value;
  }

  /**
   * Updates the display label value
   *
   * @param {Boolean} value
   */
  setDisplayLabel(value: boolean) {
    this.displayLabel = value;
  }
}

export = LocaleSwitcher;
