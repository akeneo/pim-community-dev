import React, {PropsWithChildren} from "react";
import {renderHook} from '@testing-library/react-hooks';
import {Provider} from "react-redux";
import {createStoreWithInitialState} from "@akeneo-pim-community/data-quality-insights/src/infrastructure/store/productEditFormStore";
import {fetchAllAttributeGroupsDqiStatus, fetchAttributeGroupsByCode} from "@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher";
import {useProductEvaluatedAttributeGroups} from "@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks";

jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/attributeGroupDqiStatusFetcher');
jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/attributeGroupsFetcher');

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
      "erp": {"code":"erp", "labels":{"en_US":"ERP"}},
      "technical": {"code":"technical", "labels":{"en_US":"Technical"}},
    });

    const {result, waitForNextUpdate} = await renderUseProductEvaluatedAttributeGroups(getInitialState());
    await waitForNextUpdate();

    expect(fetchAllAttributeGroupsDqiStatus).toHaveBeenCalledTimes(1);
    expect(fetchAttributeGroupsByCode).toHaveBeenNthCalledWith(1, ['erp', 'technical']);
    expect(result.current.allGroupsEvaluated).toEqual(false);
    expect(result.current.evaluatedGroups).toEqual({
      "erp": {"code":"erp", "labels":{"en_US":"ERP"}},
      "technical": {"code":"technical", "labels":{"en_US":"Technical"}},
    });
  });

  test('Product with 3 attribute groups, all activated', async () => {
    fetchAllAttributeGroupsDqiStatus.mockResolvedValueOnce({erp: true, technical: true, marketing: true});

    const {result, waitForNextUpdate} = await renderUseProductEvaluatedAttributeGroups(getInitialState());
    await waitForNextUpdate();

    expect(fetchAllAttributeGroupsDqiStatus).toHaveBeenCalledTimes(1);
    expect(fetchAttributeGroupsByCode).not.toHaveBeenCalled();
    expect(result.current.allGroupsEvaluated).toEqual(true);
    expect(result.current.evaluatedGroups).toEqual({});
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

  test('Product without an evaluation', async () => {
    const {result} = await renderUseProductEvaluatedAttributeGroups(getInitialState(false));

    expect(fetchAllAttributeGroupsDqiStatus).not.toHaveBeenCalled();
    expect(fetchAttributeGroupsByCode).not.toHaveBeenCalled();
    expect(result.current.allGroupsEvaluated).toEqual(false);
    expect(result.current.evaluatedGroups).toEqual(null);
  });
});

const renderUseProductEvaluatedAttributeGroups = (initialState: any) => {
  const wrapper = ({children}: PropsWithChildren<any>) => (
    <Provider store={createStoreWithInitialState(initialState)}>{children}</Provider>
  );

  return renderHook(() => useProductEvaluatedAttributeGroups(), {wrapper});
};

function getInitialState(initAxisRates: boolean = true) {
  let state = {
    catalogContext: {channel: 'ecommerce', locale: 'en_US'},
    product: {
      categories: [],
      enabled: true,
      family: "led_tvs",
      identifier: null,
      meta: {
        id: 1,
        label: {},
        attributes_for_this_level: [],
        level: null,
        model_type: "product",
      },
      created: null,
      updated: null,
    },
    families: {
      led_tvs: {
        code: "led_tvs",
        attributes: [
          {
            code: "description",
            group: "marketing",
          },
          {
            code: "size",
            group: "technical",
          },
          {
            code: "EAN",
            group: "erp",
          },
        ],
        attribute_as_label: "description",
        labels: {
          en_US: 'LED TVs',
        }
      }
    },
    productAxesRates: {}
  };

  if (initAxisRates) {
    state.productAxesRates = {
      1: {
        consistency: {
          code: 'consistency',
          rates: {
            ecommerce: {
              en_US: 'A',
            }
          },
        }
      }
    }
  }

  return state;
}
