import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {Helper, pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');

class PropertiesHelper extends BaseView {
  render(): any {
    if (this.getFormData().type !== 'pim_catalog_table') {
      return this;
    }

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <Helper level='info'>{translate('pim_table_attribute.form.attribute.properties_helper')}</Helper>
      </ThemeProvider>,
      this.el
    );
    return this;
  }

  remove(): any {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = PropertiesHelper;
