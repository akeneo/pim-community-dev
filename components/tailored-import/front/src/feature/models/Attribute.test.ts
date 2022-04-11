import {Attribute, isMultiSourceAttribute} from './Attribute';

const createAttribute = (type: string): Attribute => ({
  code: type,
  localizable: false,
  scopable: false,
  is_locale_specific: false,
  available_locales: [],
  type,
  labels: {},
});

test('it can tell if an attribute is a multi source attribute', () => {
  expect(isMultiSourceAttribute(createAttribute('pim_catalog_multiselect'))).toBe(true);
  expect(isMultiSourceAttribute(createAttribute('akeneo_reference_entity_collection'))).toBe(true);
  expect(isMultiSourceAttribute(createAttribute('pim_catalog_asset_collection'))).toBe(true);

  expect(isMultiSourceAttribute(createAttribute('pim_catalog_identifier'))).toBe(false);
  expect(isMultiSourceAttribute(createAttribute('pim_catalog_text'))).toBe(false);
  expect(isMultiSourceAttribute(createAttribute('another one'))).toBe(false);
});
