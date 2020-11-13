import React from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {default as JobExecutionStatusBadge} from 'pimimportexport/js/JobExecutionStatus';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge/src';

const __ = require('oro/translator');

type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';
type JobExecutionTracking = {
  error: boolean;
  warning: boolean;
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
};

const Label = styled.span`
  display: inline-block;
  vertical-align: top;
  margin: 0 5px 0 0;
`;

class JobExecutionStatus extends ReactView {
  /* istanbul ignore next */
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

    return super.configure();
  }

  reactElementToMount() {
    const data = this.getRoot().getFormData();
    const tracking: JobExecutionTracking = data.tracking;

    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <Label>{__('pim_common.status')}</Label>
          <JobExecutionStatusBadge
            data-test-id="job-status"
            status={tracking.status}
            currentStep={tracking.currentStep}
            totalSteps={tracking.totalSteps}
            hasWarning={tracking.warning}
            hasError={tracking.error}
          />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  /* istanbul ignore next */
  remove() {
    this.stopListening();

    return super.remove();
  }
}

export = JobExecutionStatus;
