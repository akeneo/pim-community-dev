// eslint-disable-next-line @typescript-eslint/no-var-requires
const AbstractFilter = require('oro/datafilter/abstract-filter');
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DatagridTableFilter} from '../datagrid';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

class TableFilter extends AbstractFilter {
  private initialDataFilter = {};

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  private show() {
    this.render();
  }

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  private hide() {
    this.remove();
  }

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  private reset() {
    this.initialDataFilter = {};
    return super.reset();
  }

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  private _writeDOMValue(value) {
    this.initialDataFilter = value;
    ReactDOM.unmountComponentAtNode(this.el);
    this.render();
    return this;
  }

  // eslint-disable-next-line @typescript-eslint/ban-ts-comment
  // @ts-ignore
  // eslint-disable-next-line @typescript-eslint/no-empty-function
  private _readDOMValue() {}

  render(): any {
    const onDisable = this.disable.bind(this);
    const onChange = this.setValue.bind(this);

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <DatagridTableFilter
            showLabel={this.showLabel}
            canDisable={this.canDisable}
            onDisable={onDisable}
            attributeCode={this.name}
            onChange={onChange}
            initialDataFilter={this.initialDataFilter}
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

export = TableFilter;
