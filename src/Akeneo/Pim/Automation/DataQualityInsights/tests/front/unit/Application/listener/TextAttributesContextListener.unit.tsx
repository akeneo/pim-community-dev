import {Attribute, Family, Product} from "@akeneo-pim-ee/data-quality-insights/src/domain";
import {
  getTextAttributes,
  isTitleFormatterActivated
} from '@akeneo-pim-ee/data-quality-insights/src/application/listener/TextAttributesContextListener';

const localizableTextarea = buildAttribute("textarea_1", "pim_catalog_textarea", true, false, false);
const localizableTextareaWysiwyg = buildAttribute("textarea_2", "pim_catalog_textarea", true, true, false);
const localizableTextareaWysiwygReadonly = buildAttribute("textarea_3", "pim_catalog_textarea", true, true, true);
const localizableTextareaReadonly = buildAttribute("textarea_4", "pim_catalog_textarea", true, false, true);
const notLocalizableTextarea = buildAttribute("textarea_5", "pim_catalog_textarea", false, false, false);

const localizableText = buildAttribute("text_1", "pim_catalog_text", true, false, false);
const localizableTextReadonly = buildAttribute("text_2", "pim_catalog_text", true, false, true);
const notLocalizableText = buildAttribute("text_3", "pim_catalog_text", false, false, false);

const attributes = [
  localizableTextarea,
  localizableTextareaWysiwyg,
  localizableTextareaWysiwygReadonly,
  localizableTextareaReadonly,
  notLocalizableTextarea,
  localizableText,
  localizableTextReadonly,
  notLocalizableText,
];

describe('Get eligible text attributes to initialize the PEF widgets', () => {
  test('Empty family', () => {
    const family = buildFamilyWithAttributes([]);
    const product = buildSimpleProduct();
    expect(getTextAttributes(family, product, 3)).toMatchObject({});
  });

  test('No eligible attribute types', () => {
    const attributes = [
      buildAttribute("description", "pim_catalog_simpleselect", true, true, false),
      buildAttribute("weight", "pim_catalog_number", true, true, false),
    ];
    const family = buildFamilyWithAttributes(attributes);
    const product = buildSimpleProduct();
    expect(getTextAttributes(family, product, 3)).toMatchObject({});
  });

  test('Multiple eligible attributes for a simple product', () => {
    const family = buildFamilyWithAttributes(attributes);
    const product = buildSimpleProduct();
    expect(getTextAttributes(family, product, 3)).toMatchObject([
      localizableTextarea,
      localizableTextareaWysiwyg,
      localizableText
    ]);
  });

  test('No eligible attribute for a variant product', () => {
    const family = buildFamilyWithAttributes(attributes);
    const product = buildVariantProduct([]);
    expect(getTextAttributes(family, product, 3)).toMatchObject([]);
  });

  test('Multiple eligible attributes for a variant product', () => {
    const family = buildFamilyWithAttributes(attributes);
    const product = buildVariantProduct(['textarea_1', 'text_1']);
    expect(getTextAttributes(family, product, 3)).toMatchObject([
      localizableTextarea,
      localizableText
    ]);
  });

  test('Multiple eligible attributes with only 1 active locale', () => {
    const family = buildFamilyWithAttributes([
      localizableTextarea,
      localizableText,
      notLocalizableText,
      notLocalizableTextarea
    ]);
    const product = buildSimpleProduct();
    expect(getTextAttributes(family, product, 1)).toMatchObject([
      localizableTextarea,
      localizableText,
      notLocalizableText,
      notLocalizableTextarea,
    ]);
  });
});

function buildAttribute(code: string, type: string, localizable: boolean, wysiwyg: boolean, readOnly: boolean): Attribute {
  return {
    code: code,
    type: type,
    group: "",
    validation_rule: null,
    validation_regexp: null,
    wysiwyg_enabled: wysiwyg,
    localizable: localizable,
    scopable: true,
    labels: {},
    is_read_only: readOnly,
    meta: {id: 1}
  };
}

function buildFamilyWithAttributes(attributes: Attribute[]): Family {
  return {
    attributes: attributes,
    code: 'laptops',
    attribute_as_label: 'title',
    labels: {},
  };
}

function buildSimpleProduct(): Product {
  return {
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
      parent_attributes: [],
      family_variant: {
        variant_attribute_sets: []
      },
      variant_navigation: [],
    },
    created: null,
    updated: null,
  };
}

function buildVariantProduct(levelAttributes: string[]): Product {
  return {
    categories: [],
    enabled: true,
    family: "led_tvs",
    identifier: null,
    meta: {
      id: 1,
      label: {},
      attributes_for_this_level: levelAttributes,
      level: 1,
      model_type: "product",
      parent_attributes: [],
      family_variant: {
        variant_attribute_sets: []
      },
      variant_navigation: [],
    },
    created: null,
    updated: null,
  };
}
