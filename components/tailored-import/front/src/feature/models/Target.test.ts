import {createAttributeTarget, createPropertyTarget, isAttributeTarget, isPropertyTarget} from './Target';
import {Attribute} from './Attribute';

const getAttribute = (code: string, type: string = 'pim_catalog_text'): Attribute => ({
  code,
  type,
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
});

test('it can create an attribute target', () => {
  expect(createAttributeTarget(getAttribute('name'), null, null)).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: null,
    locale: null,
    source_parameter: null,
  });

  expect(createAttributeTarget(getAttribute('name'), 'ecommerce', null)).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: 'ecommerce',
    locale: null,
    source_parameter: null,
  });

  expect(createAttributeTarget(getAttribute('name'), null, 'fr_FR')).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: null,
    locale: 'fr_FR',
    source_parameter: null,
  });

  expect(createAttributeTarget(getAttribute('name'), 'mobile', 'fr_FR')).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: 'mobile',
    locale: 'fr_FR',
    source_parameter: null,
  });
});

test('it can create a property target', () => {
  expect(createPropertyTarget('family')).toEqual({
    action_if_not_empty: 'set',
    code: 'family',
    action_if_empty: 'skip',
    type: 'property',
  });
});

test('it can tell if a Target is an attribute target', () => {
  expect(
    isAttributeTarget({
      code: 'name',
      type: 'attribute',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
      channel: null,
      locale: null,
      source_parameter: null,
    })
  ).toEqual(true);

  expect(
    isAttributeTarget({
      code: 'name',
      type: 'property',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
    })
  ).toEqual(false);

  expect(
    isAttributeTarget({
      action_if_not_empty: 'set',
      code: 'name',
      action_if_empty: 'skip',
      // @ts-expect-error invalid type
      type: 'another one',
      channel: null,
      locale: null,
    })
  ).toEqual(false);
});

test('it can tell if a Target is a property target', () => {
  expect(
    isPropertyTarget({
      code: 'name',
      type: 'property',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
    })
  ).toEqual(true);

  expect(
    isPropertyTarget({
      code: 'name',
      type: 'attribute',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
      channel: null,
      locale: null,
      source_parameter: null,
    })
  ).toEqual(false);
});

test('it throws an exception if an attribute type is not supported', () => {
  expect(() => {
    createAttributeTarget(getAttribute('name', 'toto'), null, null);
  }).toThrowError('Invalid attribute target "toto"');
});
