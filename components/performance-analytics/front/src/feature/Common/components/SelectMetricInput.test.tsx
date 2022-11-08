import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {SelectMetricInput} from './SelectMetricInput';
import {Metric, Metrics} from '../../Common';

describe('SelectMetricInput', () => {
  it('renders its children properly', async () => {
    await renderWithProviders(
      <SelectMetricInput filters={Metrics} onChange={() => {}} value={Metric.TIME_TO_ENRICH} />
    );

    expect(
      screen.getByText('akeneo.performance_analytics.control_panel.select_input.metrics.' + Metric.TIME_TO_ENRICH)
    ).toBeInTheDocument();
  });
});
