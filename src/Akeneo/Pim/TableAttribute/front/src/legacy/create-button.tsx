import React from 'react';
import ReactDOM from 'react-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {SelectTemplateApp} from '../attribute/SelectTemplateApp';
import {Template, TEMPLATES} from '../models/Template';
import {AttributeType} from '../models/Attribute';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const BaseCreateButton = require('pim/form/common/attributes/create-button');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const router = require('pim/router');

class CreateButton extends BaseCreateButton {
  onClick(data: {attribute_type: AttributeType, code: string, label: string}): void {
    if (data.attribute_type !== 'pim_catalog_table') {
      return BaseCreateButton.prototype.onClick.apply(this, [data]);
    }

    const handleClick = (template: Template) => {
      router.redirectToRoute('pim_enrich_attribute_create', {
        attribute_type: data.attribute_type,
        code: data.code,
        table_template: template.code,
      });
    };

    const handleClose = () => {
      ReactDOM.unmountComponentAtNode(this.el);
      this.render();
    };

    ReactDOM.unmountComponentAtNode(this.el);
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <SelectTemplateApp onClick={handleClick} onClose={handleClose} templates={TEMPLATES} />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
  }
}

export = CreateButton;
