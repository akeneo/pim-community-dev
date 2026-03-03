import {Locale, LocaleSelector} from '@akeneo-pim-community/shared';
import React from 'react';
import ReactDOM from 'react-dom';
import BaseView = require('pimui/js/view/base');
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const _ = require('underscore');
const userContext = require('pim/user-context');
const FetcherRegistry = require('../../fetcher/fetcher-registry');
const localeFetcher = FetcherRegistry.getFetcher('locale');
const router = require('pim/router');
const BaseForm = require('pim/form');

class LocaleSwitcher extends BaseView {
  private config: any;

  initialize(config: any): void {
    this.config = config.config;

    BaseView.prototype.initialize.apply(this, arguments);
  }

  configure(): JQueryPromise<any> {
    BaseForm.prototype.configure.apply(this, arguments);
    return super.configure();
  }

  render(): any {
    this.fetchLocales().then((locales: Locale[]) => {
      const currentLocaleCode = userContext.get('catalogLocale');
      let currentLocale = _.find(locales, {code: currentLocaleCode});
      if (undefined === currentLocale) {
        currentLocale = _.first(locales);
        userContext.set('catalogLocale', currentLocale.code);
      }

      ReactDOM.render(
        <DependenciesProvider>
          <ThemeProvider theme={pimTheme}>
            <LocaleSelector
              value={currentLocale.code}
              values={locales}
              onChange={this.changeLocale.bind(this)}
              inline={false}
            />
          </ThemeProvider>
        </DependenciesProvider>,
        this.el
      );
    });

    return this;
  }

  /**
   * Fetch the activated locales to render in the list
   * @return {Array} An array of activated locales
   */
  fetchLocales() {
    return localeFetcher.fetchActivated();
  }

  /**
   * Switches locales by visiting the product grid route
   */
  changeLocale(localeCode: string): void {
    const {localeParamName} = this.config;
    router.redirectToRoute(this.config.routeName, {[localeParamName]: localeCode});
  }
}

export = LocaleSwitcher;
