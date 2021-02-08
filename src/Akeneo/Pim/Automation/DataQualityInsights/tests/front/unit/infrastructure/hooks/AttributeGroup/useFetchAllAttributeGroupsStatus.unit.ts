import {fetchAllAttributeGroupsDqiStatus} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/attributeGroupDqiStatusFetcher';
import {renderHook, act} from '@testing-library/react-hooks';
import {useFetchAllAttributeGroupsStatus} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks';

jest.mock(
  '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/attributeGroupDqiStatusFetcher'
);

describe('useFetchAllAttributeGroupsStatus', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it loads all attribute groups status for DQI', async () => {
    fetchAllAttributeGroupsDqiStatus.mockResolvedValueOnce({erp: true, technical: true, marketing: true});

    const {result} = renderHook(() => useFetchAllAttributeGroupsStatus());

    expect(result.current.status).toEqual({});

    await act(async() => result.current.load());

    expect(result.current.status).toEqual({erp: true, technical: true, marketing: true});
  });
});
