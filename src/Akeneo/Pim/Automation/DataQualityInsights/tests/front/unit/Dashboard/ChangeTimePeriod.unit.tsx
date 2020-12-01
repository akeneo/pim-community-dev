import React from 'react';

import '@testing-library/jest-dom/extend-expect';
import {fireEvent, waitFor} from '@testing-library/react';

import {TimePeriodFilter} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/Filters/TimePeriodFilter';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD} from '@akeneo-pim-community/data-quality-insights/src';
import {renderDashboardWithProvider} from '../../utils/render/renderDashboardWithProvider';

beforeEach(() => {
  jest.resetModules();
});

window.dispatchEvent = jest.fn();

describe('Dashboard > change time period', () => {
  test('time period can be changed to weekly on the dashboard', async () => {
    const {getByText} = renderDashboardWithProvider(<TimePeriodFilter timePeriod={'daily'} />);
    const filter = await waitFor(() => getByText('akeneo_data_quality_insights.dqi_dashboard.time_period.weekly'));
    fireEvent.click(filter);
    assertTimePeriodFilterEventHasBeenDispatched();
  });
});

function assertTimePeriodFilterEventHasBeenDispatched() {
  const customEvents = window.dispatchEvent.mock.calls.filter(event => event[0].constructor.name === 'CustomEvent')[0];
  expect(customEvents.length).toBe(1);
  expect(customEvents[0].type).toBe(DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD);
  expect(customEvents[0].detail.timePeriod).toBe('weekly');
}
