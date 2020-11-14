import {fireEvent} from '@testing-library/react';
import {renderTooManyAttributesLink} from '../../../../../../../utils/render';
import {aProduct} from '../../../../../../../utils/provider';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
} from '@akeneo-pim-community/data-quality-insights/src';

describe('TooManyAttributesLink', () => {
  test('it displays a message with the number of attributes to improve', () => {
    const product = aProduct();

    const {getByText} = renderTooManyAttributesLink('an_axis', [], 0, {
      product,
    });
    expect(
      getByText('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes')
    ).toBeInTheDocument();
  });
});

describe('TooManyAttributesLink user actions', () => {
  beforeAll(() => {
    jest.spyOn(window, 'dispatchEvent').mockImplementation(() => true);
  });
  beforeEach(() => {
    sessionStorage.clear();
  });
  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it redirects to product edit form and filters on missing attributes when axis is enrichment', () => {
    const attributes = ['an_attribute', 'a_second_attribute'];
    const expectedEvent = new CustomEvent(DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES, {
      detail: {
        attributes,
      },
    });

    const product = aProduct();
    const {getByText} = renderTooManyAttributesLink('enrichment', attributes, 2, {
      product,
    });

    fireEvent.click(getByText('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes'));
    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });

  test('it redirects to product edit form and filters on missing attributes when axis is consistency', () => {
    const attributes = ['an_attribute', 'a_second_attribute'];
    const expectedEvent = new CustomEvent(DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES, {
      detail: {
        attributes,
      },
    });

    const product = aProduct();
    const {getByText} = renderTooManyAttributesLink('consistency', attributes, 2, {
      product,
    });

    fireEvent.click(getByText('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes'));
    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });
});
