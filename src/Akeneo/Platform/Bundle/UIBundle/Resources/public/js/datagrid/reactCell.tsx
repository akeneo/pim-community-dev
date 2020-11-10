import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
const StringCell = require('oro/datagrid/string-cell');
const requireContext = require('require-context');

const convertToType = (type: string, value: string) => {
  switch (type) {
    case 'boolean':
      return Boolean(value);
    case 'number':
      return Number(value);
    default:
      return value;
  }
};

<<<<<<< HEAD
type PropTypes = {[key: string]: string | number | boolean};

class ReactCell extends StringCell {
  render() {
    const extraOptions = this.options.column.attributes.extraOptions;
    const Component = requireContext(extraOptions.component).default;
    const props = Object.keys(extraOptions.props).reduce((result: PropTypes, key) => {
      result[key] = convertToType(extraOptions.props[key], this.model.get(key));

      return result;
    }, {});

=======
type PropTypes = {[key: string]: string | number | boolean | (() => void)};

class ReactCell extends StringCell {
  render() {
    const {props: optionsProps, component} = this.options.column.attributes.extraOptions;
    const Component = requireContext(component).default;

    if (undefined === Component) {
      throw new Error(`Module ${component} has no default export`);
    }

    const props = Object.keys(optionsProps).reduce((props: PropTypes, key) => {
      props[key] = convertToType(optionsProps[key], this.model.get(key));

      return props;
    }, {});

    props.refreshCollection = () => this.model.collection.fetch();

>>>>>>> origin/master
    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <Component {...props} />
        </DependenciesProvider>
      </ThemeProvider>,
      this.el
    );

    return this;
  }
}

export = ReactCell;
