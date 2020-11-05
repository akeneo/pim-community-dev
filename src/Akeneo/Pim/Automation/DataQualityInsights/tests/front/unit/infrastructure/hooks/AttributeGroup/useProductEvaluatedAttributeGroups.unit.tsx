import React, {PropsWithChildren} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {Provider} from 'react-redux';
import {createStoreWithInitialState} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/store/productEditFormStore';
import {
  fetchAllAttributeGroupsDqiStatus,
  fetchAttributeGroupsByCode,
} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher';
import {useProductEvaluatedAttributeGroups} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks';

jest.mock(
  '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/attributeGroupDqiStatusFetcher'
);
jest.mock(
  '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/attributeGroupsFetcher'
);

describe('AttributeGroupsHelper', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('Product with 3 attribute groups including 1 (marketing) deactivated', async () => {
    fetchAllAttributeGroupsDqiStatus.mockResolvedValueOnce({erp: true, technical: true, marketing: false});
    fetchAttributeGroupsByCode.mockResolvedValueOnce({
      erp: {code: 'erp', labels: {en_US: 'ERP'}},
      technical: {code: 'technical', labels: {en_US: 'Technical'}},
    });

    const {result, waitForNextUpdate} = await renderUseProductEvaluatedAttributeGroups(getInitialState());
    await waitForNextUpdate();

    expect(fetchAllAttributeGroupsDqiStatus).toHaveBeenCalledTimes(1);
    expect(fetchAttributeGroupsByCode).toHaveBeenNthCalledWith(1, ['erp', 'technical']);
    expect(result.current.allGroupsEvaluated).toEqual(false);
    expect(result.current.evaluatedGroups).toEqual({
      erp: {code: 'erp', labels: {en_US: 'ERP'}},
      technical: {code: 'technical', labels: {en_US: 'Technical'}},
    });
  });

  test('Product with 3 attribute groups, all activated', async () => {
    fetchAllAttributeGroupsDqiStatus.mockResolvedValueOnce({erp: true, technical: true, marketing: true});

    const {result, waitForNextUpdate} = await renderUseProductEvaluatedAttributeGroups(getInitialState());
    await waitForNextUpdate();

    expect(fetchAllAttributeGroupsDqiStatus).toHaveBeenCalledTimes(1);
    expect(fetchAttributeGroupsByCode).not.toHaveBeenCalled();
    expect(result.current.allGroupsEvaluated).toEqual(true);
    expect(result.current.evaluatedGroups).toEqual(null);
  });

  test('Product with 3 attribute groups, all disabled', async () => {
    fetchAllAttributeGroupsDqiStatus.mockResolvedValueOnce({erp: false, technical: false, marketing: false});

    const {result, waitForNextUpdate} = await renderUseProductEvaluatedAttributeGroups(getInitialState());
    await waitForNextUpdate();

    expect(fetchAllAttributeGroupsDqiStatus).toHaveBeenCalledTimes(1);
    expect(fetchAttributeGroupsByCode).not.toHaveBeenCalled();
    expect(result.current.allGroupsEvaluated).toEqual(false);
    expect(result.current.evaluatedGroups).toEqual({});
  });
});

const renderUseProductEvaluatedAttributeGroups = (initialState: any) => {
  const wrapper = ({children}: PropsWithChildren<any>) => (
    <Provider store={createStoreWithInitialState(initialState)}>{children}</Provider>
  );

  return renderHook(() => useProductEvaluatedAttributeGroups(), {wrapper});
};

function getInitialState(channel = 'ecommerce', locale = 'en_US', initProductEvaluation: boolean = true) {
  let state = {
    catalogContext: {channel: channel, locale: locale},
    product: {
      categories: [],
      enabled: true,
      family: 'led_tvs',
      identifier: null,
      meta: {
        id: 1,
        label: {},
        attributes_for_this_level: [],
        level: null,
        model_type: 'product',
      },
      created: null,
      updated: null,
    },
    families: {
      led_tvs: {
        code: 'led_tvs',
        attributes: [
          {
            code: 'description',
            group: 'marketing',
          },
          {
            code: 'size',
            group: 'technical',
          },
          {
            code: 'EAN',
            group: 'erp',
          },
        ],
        attribute_as_label: 'description',
        labels: {
          en_US: 'LED TVs',
        },
      },
    },
    productEvaluation: {},
  };

  if (initProductEvaluation) {
    state.productEvaluation = {
      1: {
        enrichment: {
          ecommerce: {
            de_DE: {
              rate: {
                value: null,
                rank: null,
              },
              criteria: [],
            },
            en_US: {
              rate: {
                value: 47,
                rank: 'E',
              },
              criteria: [],
            },
          },
          mobile: {
            de_DE: {
              rate: {
                value: null,
                rank: null,
              },
              criteria: [],
            },
            en_US: {
              rate: {
                value: 45,
                rank: 'E',
              },
              criteria: [],
            },
          },
        },
        consistency: {
          ecommerce: {
            de_DE: {
              rate: {
                value: null,
                rank: null,
              },
              criteria: [],
            },
            en_US: {
              rate: {
                value: 94,
                rank: 'A',
              },
              criteria: [],
            },
          },
          mobile: {
            de_DE: {
              rate: {
                value: null,
                rank: null,
              },
              criteria: [],
            },
            en_US: {
              rate: {
                value: null,
                rank: null,
              },
              criteria: [],
            },
          },
        },
      },
    };
  }

  return state;
}
