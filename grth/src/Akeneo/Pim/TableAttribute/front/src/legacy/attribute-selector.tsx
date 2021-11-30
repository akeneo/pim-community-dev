import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {AttributeSelector as InnerAttributeSelector} from '../jobs';
import {AttributeCode, AttributeType} from '../models';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const propertyAccessor = require('pim/common/property');

type AttributeSelectorConfig = {
  aclResource: null;
  code: string;
  config: {
    fieldCode: string;
    label: string;
    readOnly?: boolean;
    required?: boolean;
    types?: AttributeType[];
  };
  feature: null;
  loadedModule: any;
  module: string;
  parent: string;
  position: number;
  targetZone: string;
};

class AttributeSelector extends BaseView {
  private fieldCode: string;
  private label: string;
  private readOnly: boolean;
  private errorMessage: string | null = null;
  private required: boolean;
  private types: string[] | undefined;

  constructor(config: AttributeSelectorConfig) {
    super({...config, className: 'the classname'});

    this.fieldCode = config.config.fieldCode;
    this.label = config.config.label;
    this.readOnly = typeof config.config.readOnly === 'undefined' ? false : config.config.readOnly;
    this.required = typeof config.config.required === 'undefined' ? false : config.config.required;
    this.types = config.config.types;
  }

  onBadRequest(data: {response: any}) {
    this.errorMessage = propertyAccessor.accessProperty(data.response, this.fieldCode);
    this.unmount();
    this.render();
  }

  configure(): JQueryPromise<any> {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onBadRequest.bind(this));

    return super.configure();
  }

  render(): any {
    const data = this.getFormData();
    const initialValue = propertyAccessor.accessProperty(data, this.fieldCode, null);

    const onChange = (attributeCode: AttributeCode | null) => {
      const data = this.getFormData();
      propertyAccessor.updateProperty(data, this.fieldCode, attributeCode);
    };

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <InnerAttributeSelector
            label={translate(this.label)}
            readOnly={this.readOnly}
            initialValue={initialValue}
            onChange={onChange}
            errorMessage={this.errorMessage}
            required={this.required}
            types={this.types}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
    return this;
  }

  remove(): any {
    this.unmount();

    return super.remove();
  }

  unmount(): any {
    ReactDOM.unmountComponentAtNode(this.el);
  }
}

export = AttributeSelector;
