import '@testing-library/jest-dom/extend-expect';
import {renderHook, act} from '@testing-library/react-hooks';
import {useCampaign} from 'akeneocommunicationchannel/hooks/useCampaign';

const expectedCampaign = 'Serenity';
const campaignFetcher = {
  fetch: () =>
    new Promise(resolve => {
      act(() => {
        setTimeout(() => resolve(expectedCampaign), 100);
      });
    }),
};

test('It can get all the campaign', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useCampaign(campaignFetcher));

  expect(result.current.campaign).toEqual(null);

  await waitForNextUpdate();

  expect(result.current.campaign).toEqual(expectedCampaign);
  expect(typeof result.current.fetchCampaign).toBe('function');
});
