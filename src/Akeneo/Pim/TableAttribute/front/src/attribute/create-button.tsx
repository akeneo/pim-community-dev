import React from 'react';
import ReactDOM from "react-dom";
const BaseCreateButton = require('pim/form/common/attributes/create-button');
import { DependenciesProvider } from "@akeneo-pim-community/legacy-bridge";
import { pimTheme } from "akeneo-design-system";
import { ThemeProvider } from "styled-components";
import { SelectTemplateApp } from "./SelectTemplateApp";
import { Template } from "../models/Template";
const router = require('pim/router');

class CreateButton extends BaseCreateButton {
  onClick(attributeType: string): void {
    if (attributeType !== 'pim_catalog_table') {
      return BaseCreateButton.prototype.onClick.apply(this, arguments);
    }

    const handleClick = (template: Template) => {
      router.redirectToRoute('pim_enrich_attribute_create', {
        attribute_type: attributeType,
        code: this.getQueryParam('code'),
        table_template: template.code,
      });
    }

    const handleClose = () => {
      ReactDOM.unmountComponentAtNode(this.el);
      this.render();
    }

    ReactDOM.unmountComponentAtNode(this.el);
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <SelectTemplateApp onClick={handleClick} onClose={handleClose}/>
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
  };
}

export = CreateButton;
