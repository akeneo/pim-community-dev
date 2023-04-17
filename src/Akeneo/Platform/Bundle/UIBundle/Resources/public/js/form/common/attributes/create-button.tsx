import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {AttributeData, CreateAttributeButtonApp} from '../../../attribute/form/CreateAttributeButtonApp';
const translate = require('oro/translator');
const router = require('pim/router');
const analytics = require('pim/analytics');

type CreateButtonConfig = {
  buttonTitle: string;
};

class CreateButton extends BaseView {
  private config: CreateButtonConfig;

  initialize(config: any): void {
    this.config = config.config as CreateButtonConfig;
    BaseView.prototype.initialize.apply(this, arguments);
  }

  getQueryParam(paramName: string): any {
    const urlString = window.location.href;
    const index = urlString.indexOf('?');
    if (index < 0) {
      return null;
    }
    const params = new URLSearchParams(urlString.substring(index + 1));

    return params.get(paramName);
  }

  onClick(data: AttributeData): void {
    analytics.appcuesTrack('attribute:create:type-selected', {type: data.attribute_type});

    router.redirectToRoute('pim_enrich_attribute_create', data);
  }

  getAttributeIcons() {
    const moduleConfig = __moduleConfig;

    return moduleConfig.attribute_icons;
  }

  getSteps() {
    const moduleConfig = __moduleConfig;

    return moduleConfig.steps;
  }

  render(): any {
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CreateAttributeButtonApp
            steps={this.getSteps()}
            iconsMap={this.getAttributeIcons()}
            isModalOpen={!!this.getQueryParam('open_create_attribute_modal')}
            buttonTitle={translate(this.config.buttonTitle)}
            onClick={this.onClick.bind(this)}
            initialData={{code: this.getQueryParam('code')}}
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

export = CreateButton;
