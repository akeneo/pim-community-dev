import {Attribute, Family, Product} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {
  VariantAttributeSet,
  VariantNavigation,
} from '@akeneo-pim-community/data-quality-insights/src/domain/Product.interface';

type Labels = {
  [locale: string]: string;
};
const aProduct = (
  id: number = 1234,
  labels: Labels = {},
  identifier: string = 'idx_1234',
  family: string = 'a_family'
): Product => {
  return {
    categories: [],
    enabled: true,
    family,
    identifier,
    created: null,
    updated: null,
    meta: {
      id,
      label: labels,
      level: null,
      attributes_for_this_level: [],
      model_type: 'product',
      variant_navigation: [],
      family_variant: {
        variant_attribute_sets: [],
      },
      parent_attributes: [],
    },
  };
};

const aVariantProduct = (
  id: number = 1234,
  labels: Labels = {},
  level: number = 1,
  identifier: string = 'idx_1234',
  family: string = 'a_family',
  attributes_for_this_level: string[] = ['an_axis_attribute'],
  variant_navigation: VariantNavigation[] = [
    {axes: {en_US: 'Model'}, selected: {id: 12}},
    {axes: {en_US: 'Axis Model'}, selected: {id: 123}},
  ],
  variant_attribute_sets: VariantAttributeSet[] = [
    {attributes: ['a_parent_attribute']},
    {attributes: ['an_axis_attribute']},
  ],
  parent_attributes: string[] = ['a_parent_attribute']
): Product => {
  return {
    categories: [],
    enabled: true,
    family,
    identifier,
    created: null,
    updated: null,
    meta: {
      id,
      label: labels,
      level,
      attributes_for_this_level,
      model_type: 'product',
      variant_navigation,
      family_variant: {
        variant_attribute_sets,
      },
      parent_attributes,
    },
  };
};

const aProductModel = (
  id: number = 12,
  level: number = 0,
  labels: Labels = {},
  family: string = 'a_family',
  attributes_for_this_level: string[] = ['a_model_attribute'],
  variant_navigation: VariantNavigation[] = [
    {axes: {en_US: 'Model'}, selected: {id: 12}},
    {axes: {en_US: 'Axis Model'}, selected: {id: 123}},
  ],
  variant_attribute_sets: VariantAttributeSet[] = [{attributes: ['a_variant_attribute_from_child_level']}],
  parent_attributes: string[] = []
): Product => {
  return {
    identifier: null,
    categories: [],
    enabled: true,
    family,
    created: null,
    updated: null,
    meta: {
      id,
      label: labels,
      level,
      attributes_for_this_level,
      model_type: 'product_model',
      variant_navigation,
      family_variant: {
        variant_attribute_sets,
      },
      parent_attributes,
    },
  };
};

const aFamily = (
  code: string,
  id: number = 1234,
  labels: Labels = {},
  attributes: Attribute[] = [],
  attribute_as_label: string = ''
): Family => {
  return {
    attributes,
    code,
    attribute_as_label,
    labels,
    meta: {
      id,
    },
  };
};

const anAttribute = (
  code: string = 'an_attribute',
  id: number = 1234,
  type: string = 'a_type',
  group: string = 'an_attribute_group',
  labels: Labels = {}
): Attribute => {
  return {
    code,
    labels,
    type,
    group,
    meta: {
      id,
    },
  };
};

export {aProduct, aVariantProduct, aProductModel, aFamily, anAttribute};
