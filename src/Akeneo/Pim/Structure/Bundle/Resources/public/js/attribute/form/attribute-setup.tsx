import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeSetupApp} from './AttributeSetupApp';
import {Attribute} from '../models/Attribute';

const FetcherRegistry = require('pim/fetcher-registry');

class AttributeSetup extends BaseView {
  mainIdentifierAttribute: Attribute;

  configure(): JQueryPromise<any> {
    return FetcherRegistry.getFetcher('attribute')
      .getIdentifierAttribute()
      .then((mainIdentifierAttribute: Attribute) => {
        this.mainIdentifierAttribute = mainIdentifierAttribute;

        return super.configure();
      });
  }

  render(): any {
    const attribute = this.getFormData();
    const handleMainIdentifierChange = () => {
      FetcherRegistry.getFetcher('attribute').clear();
    };

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeSetupApp
            attribute={attribute}
            originalMainIdentifierAttribute={this.mainIdentifierAttribute}
            onMainIdentifierChange={handleMainIdentifierChange}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
    return this;
  }

  remove(): any {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = AttributeSetup;
