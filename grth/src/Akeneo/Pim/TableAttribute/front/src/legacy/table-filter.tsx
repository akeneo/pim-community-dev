import AbstractFilter = require('oro/datafilter/abstract-filter');
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

class TableFilter extends AbstractFilter {
  render(): any {
    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <div>TODO This is the table filter</div>
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

export = TableFilter;
