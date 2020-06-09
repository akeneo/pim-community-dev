import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {useCards} from '@akeneo-pim-community/communication-channel/src/hooks/useCards';
import {getExpectedCards, getMockDataProvider} from '../../../test-utils';

const mockDataProvider = getMockDataProvider();

test('It can get all the cards', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useCards(mockDataProvider.cardFetcher));

  expect(result.current.cards).toEqual(null);

  await waitForNextUpdate();

  expect(result.current.cards).toEqual(getExpectedCards());
  expect(typeof result.current.fetchCards).toBe('function');
});
