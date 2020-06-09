import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {useCampaign} from '@akeneo-pim-community/communication-channel/src/hooks/useCampaign';
import {getExpectedCampaign, getMockDataProvider} from '../../../test-utils';

const mockDataProvider = getMockDataProvider();

test('It can get all the campaign', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useCampaign(mockDataProvider.campaignFetcher));

  expect(result.current.campaign).toEqual(null);

  await waitForNextUpdate();

  expect(result.current.campaign).toEqual(getExpectedCampaign());
  expect(typeof result.current.fetchCampaign).toBe('function');
});
