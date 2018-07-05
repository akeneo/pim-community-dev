import {dataReceived} from 'akeneoenrichedentity/domain/event/search';

describe('akeneo > enriched entity > domain > event --- search', () => {
  test('I can create a dataReceived event', () => {
    expect(dataReceived([], 0, false)).toEqual({type: 'DATA_RECEIVED', data: {items: []}, total: 0, append: false});
    expect(dataReceived(['item'], 1, true)).toEqual({
      type: 'DATA_RECEIVED',
      data: {items: ['item']},
      total: 1,
      append: true,
    });
  });
});
