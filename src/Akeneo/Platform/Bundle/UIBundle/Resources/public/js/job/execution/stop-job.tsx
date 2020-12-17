import React from 'react';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {pimTheme} from 'akeneo-design-system';
import {StopJobAction} from './StopJobAction';

class StopJob extends ReactView {
  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.getRoot(), 'pim-job-execution-form:auto-update', this.render);

    return ReactView.prototype.configure.apply(this, arguments);
  }

  reactElementToMount() {
    const data = this.getFormData();
    const props = {
      id: data.meta.id,
      jobLabel: data.jobInstance.label,
      isStoppable: Boolean(data.isStoppable),
      onStop: () => this.getRoot().trigger('pim-job-execution-form:request-fetch-data'),
    };

    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <StopJobAction {...props} />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export default StopJob;
