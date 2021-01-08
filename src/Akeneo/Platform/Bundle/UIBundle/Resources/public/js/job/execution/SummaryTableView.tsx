import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {SummaryTable} from './summary/SummaryTable';

class SummaryTableView extends ReactView {
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

    return super.configure();
  }

  reactElementToMount() {
    const data = this.getRoot().getFormData();

    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <SummaryTable jobExecution={data} />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  remove() {
    this.stopListening();

    return super.remove();
  }
}

export = SummaryTableView;
