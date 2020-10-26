import {saveAttributeGroupsOrder} from '@akeneo-pim-community/settings-ui/src/infrastructure/savers';
import {aCollectionOfAttributeGroups} from '../../../utils/provideAttributeGroupHelper';

const FetcherRegistry = require('pim/fetcher-registry');

jest.mock('pim/fetcher-registry');

describe('saveAttributeGroupsOrder', () => {
  const clearAttributeGroupFetcher = jest.fn();

  beforeAll(() => {
    // @ts-ignore
    global.fetch = jest.fn();
    FetcherRegistry.getFetcher = jest.fn(() => ({
      clear: clearAttributeGroupFetcher,
    }));
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  afterAll(() => {
    // @ts-ignore
    global.fetch.mockRestore();
  });

  test('it saves the order of attribute groups list', async () => {
    const collection = aCollectionOfAttributeGroups([
      {code: 'groupC', id: 3, order: 0},
      {code: 'groupB', id: 2, order: 1},
      {code: 'groupA', id: 1, order: 2},
    ]);
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockImplementation(() => {
      return Promise.resolve({
        status: 200,
        json: () => Promise.resolve(collection),
      });
    });

    const sortOrder = {
      groupA: 2,
      groupB: 1,
      groupC: 0,
    };
    const result = await saveAttributeGroupsOrder(sortOrder);

    expect(result).toEqual(collection);
    expect(clearAttributeGroupFetcher).toBeCalled();
  });
});
