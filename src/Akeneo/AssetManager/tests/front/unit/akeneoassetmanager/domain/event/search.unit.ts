import {dataReceived} from 'akeneoassetmanager/domain/event/search';

describe('akeneo > asset family > domain > event --- search', () => {
  test('I can create a dataReceived event', () => {
    expect(dataReceived([], 0, 0, false)).toEqual({
      type: 'GRID_DATA_RECEIVED',
      data: {items: []},
      matchesCount: 0,
      totalCount: 0,
      append: false,
    });
    expect(dataReceived(['item'], 1, 1, true)).toEqual({
      type: 'GRID_DATA_RECEIVED',
      data: {items: ['item']},
      matchesCount: 1,
      totalCount: 1,
      append: true,
    });
  });
});
