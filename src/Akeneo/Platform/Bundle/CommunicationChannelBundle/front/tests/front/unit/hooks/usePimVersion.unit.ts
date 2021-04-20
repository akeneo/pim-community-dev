import '@testing-library/jest-dom/extend-expect';
import {usePimVersion} from '@akeneo-pim-community/communication-channel/src/hooks/usePimVersion';
import {
  renderHookWithProviders,
  fetchMockResponseOnce,
} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

afterEach(() => {
  fetchMock.resetMocks();
});

test('It gets the PimVersion from the pim analytics data', async () => {
  const expectedPimAnalyticsData = {pim_version: '4.0', pim_edition: 'CE', other_properties: 'other_value'};
  const expectedPimVersion = {
    edition: 'CE',
    version: '4.0',
  };
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));

  const {result, waitForNextUpdate} = renderHookWithProviders(usePimVersion);

  expect(result.current.data).toEqual(null);

  await waitForNextUpdate();

  expect(result.current.data).toEqual(expectedPimVersion);
  expect(result.current.hasError).toEqual(false);
  expect(fetchMock).toHaveBeenCalledWith('pim_analytics_data_collect');
});

test('It can validate the pim analytics data', async () => {
  const expectedPimAnalyticsData = {pim_version: '1384859', pim_edition: true};
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));
  console.error = jest.fn();

  const {result, waitForNextUpdate} = renderHookWithProviders(usePimVersion);

  await waitForNextUpdate();

  expect(result.current.data).toEqual(null);
  expect(result.current.hasError).toEqual(true);
  expect(console.error).toHaveBeenCalledTimes(1);
});
