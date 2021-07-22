import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {CreateButtonApp} from './CreateButtonApp';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {LabelCollection} from "@akeneo-pim-community/shared";
const translate = require('oro/translator');
const router = require('pim/router');

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

  onClick(data: { attribute_type: string, code: string, label: string }): void {
    router.redirectToRoute('pim_enrich_attribute_create', data);
  }

  render(): any {
    const moduleConfig = __moduleConfig;
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CreateButtonApp
            buttonTitle={translate(this.config.buttonTitle)}
            iconsMap={moduleConfig.attribute_icons}
            isModalOpen={!!this.getQueryParam('open_create_attribute_modal')}
            onClick={this.onClick.bind(this)}
            defaultCode={this.getQueryParam('code')}
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
