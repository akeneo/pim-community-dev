import '@testing-library/jest-dom/extend-expect';
import {renderHook, act} from '@testing-library/react-hooks';
import {useCards} from 'akeneocommunicationchannel/hooks/useCards';

const expectedCards = [
  {
    title: 'Title card',
    description: 'Description card',
    img: '/path/img/card.png',
    link: 'http://link-card.com',
  },
  {
    title: 'Title card 2',
    description: 'Description card 2',
    img: '/path/img/card-2.png',
    link: 'http://link-card-2.com',
  },
];
const cardFetcher = {
  fetchAll: () =>
    new Promise(resolve => {
      act(() => {
        setTimeout(() => resolve(expectedCards), 100);
      });
    }),
};

test('It can get all the cards', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useCards(cardFetcher));

  expect(result.current.cards).toEqual(null);

  await waitForNextUpdate();

  expect(result.current.cards).toEqual(expectedCards);
  expect(typeof result.current.fetchCards).toBe('function');
});
