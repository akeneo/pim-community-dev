import React from 'react';
import ReactDOM from 'react-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {CreateAttributeButtonApp} from '../attribute/CreateAttributeButtonApp';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const BaseCreateButton = require('pim/form/common/attributes/create-button');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');

class CreateButton extends BaseCreateButton {
  render(): any {
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CreateAttributeButtonApp
            buttonTitle={translate(this.config.buttonTitle)}
            iconsMap={this.getAttributeIcons()}
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
}

export = CreateButton;
