import React from 'react';
import {fireEvent} from '@testing-library/react';
import {aFamily, aProduct, aProductModel, aVariantProduct, renderAttribute} from '../../../../../../../utils';
import {
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
} from '@akeneo-pim-community/data-quality-insights/src';
import {ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY} from '@akeneo-pim-community/data-quality-insights/src/application/constant';
import Router from 'pim/router';

jest.mock('pim/router');

describe('Attribute', () => {
  test('it displays an attribute label', () => {
    const product = aProduct();
    const {getByText} = renderAttribute('an_attribute', 'an_attribute_label', null, 'an_axis', {
      product,
    });

    expect(getByText('an_attribute_label')).toBeInTheDocument();
  });

  test('it displays an attribute label with a separator', () => {
    const product = aProduct();
    const separator = <>__SEPARATOR__</>;
    const {getByText} = renderAttribute('an_attribute', 'an_attribute_label', separator, 'an_axis', {
      product,
    });

    expect(getByText('an_attribute_label')).toBeInTheDocument();
    expect(getByText('__SEPARATOR__')).toBeInTheDocument();
  });
});

describe('Attribute actions', () => {
  beforeAll(() => {
    jest.spyOn(window, 'dispatchEvent').mockImplementation(() => true);
    jest.spyOn(Router, 'generate').mockImplementation(() => true);
  });
  beforeEach(() => {
    jest.resetAllMocks();
    sessionStorage.clear();
  });
  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it redirects to the attribute on product Edit Form when it is a simple product', () => {
    const expectedEvent = new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {
      detail: {
        code: 'an_attribute',
      },
    });
    const product = aProduct();
    const {getByText} = renderAttribute('an_attribute', 'an_attribute_label', null, 'an_axis', {
      product,
    });

    fireEvent.click(getByText('an_attribute_label'));

    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });

  test('it redirects to the attribute on product Edit Form when it is a the same product variant level', () => {
    const expectedEvent = new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {
      detail: {
        code: 'an_attribute',
      },
    });
    const family = aFamily('a_family');
    const product = aVariantProduct(1234, {}, 1, 'idx_1234', 'a_family', ['an_axis_attribute', 'an_attribute']);
    const {getByText} = renderAttribute('an_attribute', 'an_attribute_label', null, 'an_axis', {
      product,
      families: {a_family: family},
    });

    fireEvent.click(getByText('an_attribute_label'));

    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });

  test('it redirects to the attribute on product Edit Form when it is a the same product model level', () => {
    const expectedEvent = new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {
      detail: {
        code: 'a_model_attribute',
      },
    });
    const product = aProductModel();
    const {getByText} = renderAttribute('a_model_attribute', 'a_model_attribute_label', null, 'an_axis', {
      product,
    });

    fireEvent.click(getByText('a_model_attribute_label'));

    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });

  test('it redirects to the attribute on parent product model Form when it is a variant product with 1 level of variation', () => {
    const family = aFamily('a_family');
    const product = aVariantProduct(
      1234,
      {en_US: 'A variant product'},
      1,
      'idx_1234',
      'a_family',
      ['a_variant_attribute', 'a_second_variant_attribute'],
      [
        {axes: {en_US: 'Model'}, selected: {id: 123}},
        {axes: {en_US: 'A variant product'}, selected: {id: 1234}},
      ],
      [{attributes: ['a_variant_level_1_attribute, another_parent_level_1_attribute']}],
      ['a_variant_level_1_attribute', 'another_parent_level_1_attribute']
    );
    const {getByText} = renderAttribute(
      'another_parent_level_1_attribute',
      'another_parent_level_1_attribute_label',
      null,
      'an_axis',
      {
        product,
        families: {a_family: family},
      }
    );

    fireEvent.click(getByText('another_parent_level_1_attribute_label'));

    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('another_parent_level_1_attribute');
    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    expect(Router.generate).toHaveBeenCalledWith('pim_enrich_product_model_edit', {id: 123});
  });

  test('it redirects to the attribute on product model level 1 Form when it is a variant product with 2 levels of variations', () => {
    const family = aFamily('a_family');
    const product = aVariantProduct(
      1234,
      {en_US: 'A variant product'},
      2,
      'idx_1234',
      'a_family',
      ['a_variant_attribute', 'a_second_variant_attribute'],
      [
        {axes: {en_US: 'Model'}, selected: {id: 12}},
        {axes: {en_US: 'Model With Primary Axis'}, selected: {id: 123}},
        {axes: {en_US: 'A variant product'}, selected: {id: 1234}},
      ],
      [
        {attributes: ['a_variant_level_1_attribute, another_parent_level_1_attribute']},
        {attributes: ['a_variant_level_2_attribute', 'another_parent_level_2_attribute']},
      ],
      ['a_variant_level_2_attribute', 'another_parent_level_2_attribute']
    );
    const {getByText} = renderAttribute(
      'another_parent_level_2_attribute',
      'another_parent_level_2_attribute_label',
      null,
      'an_axis',
      {
        product,
        families: {a_family: family},
      }
    );

    fireEvent.click(getByText('another_parent_level_2_attribute_label'));

    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('another_parent_level_2_attribute');
    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    expect(Router.generate).toHaveBeenCalledWith('pim_enrich_product_model_edit', {id: 123});
  });

  test('it redirects to the attribute on product model root Form when it is a variant product with 2 levels of variations', () => {
    const family = aFamily('a_family');
    const product = aVariantProduct(
      1234,
      {en_US: 'A variant product'},
      2,
      'idx_1234',
      'a_family',
      ['a_variant_attribute', 'a_second_variant_attribute'],
      [
        {axes: {en_US: 'Model'}, selected: {id: 12}},
        {axes: {en_US: 'Model With Primary Axis'}, selected: {id: 123}},
        {axes: {en_US: 'A variant product'}, selected: {id: 1234}},
      ],
      [
        {attributes: ['a_variant_level_1_attribute, another_parent_level_1_attribute']},
        {attributes: ['a_variant_level_2_attribute', 'another_parent_level_2_attribute']},
      ],
      ['a_variant_level_2_attribute', 'another_parent_level_2_attribute']
    );
    const {getByText} = renderAttribute(
      'another_parent_level_1_attribute',
      'another_parent_level_1_attribute_label',
      null,
      'an_axis',
      {
        product,
        families: {a_family: family},
      }
    );

    fireEvent.click(getByText('another_parent_level_1_attribute_label'));

    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('another_parent_level_1_attribute');
    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    expect(Router.generate).toHaveBeenCalledWith('pim_enrich_product_model_edit', {id: 12});
  });
});
