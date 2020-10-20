import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
import {Actions} from './Actions';
const StringCell = require('oro/datagrid/string-cell');

class ActionsCell extends StringCell {
  render() {
    const jobId = this.model.get('id');
    const isStoppable = '1' === this.model.get('isStoppable');

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <Actions jobId={jobId} isStoppable={isStoppable} />
        </DependenciesProvider>
      </ThemeProvider>,
      this.el
    );

    return this;
  }
}
export = ActionsCell;
