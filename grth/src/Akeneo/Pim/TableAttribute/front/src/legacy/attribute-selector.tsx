import BaseView = require('pimui/js/view/base');
const propertyAccessor = require('pim/common/property');
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {AttributeSelector as InnerAttributeSelector} from "../jobs";
import {AttributeCode} from "../models";

type AttributeSelectorConfig = {
  aclResource: null;
  code: string;
  config: {
    fieldCode: string;
    label: string;
    readonly: boolean;
  };
  feature: null;
  loadedModule: any;
  module: string;
  parent: string;
  position: number;
  targetZone: string;
}

class AttributeSelector extends BaseView {
  private fieldCode: string;
  private label: string;
  private readOnly: boolean;

  constructor(config: AttributeSelectorConfig) {
    super({...config, className: 'the classname'});

    this.fieldCode = config.config.fieldCode;
    this.label = config.config.label;
    this.readOnly = config.config.readonly;
  }

  render(): any {
    const data = this.getFormData();
    const value = propertyAccessor.accessProperty(data, this.fieldCode, null);

    const onChange = (attributeCode: AttributeCode | null) => {
      const data = this.getFormData();
      propertyAccessor.updateProperty(data, this.fieldCode, attributeCode);
    }

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <InnerAttributeSelector
          label={this.label}
          readOnly={this.readOnly}
          value={value}
          onChange={onChange}
        />
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

export = AttributeSelector;
