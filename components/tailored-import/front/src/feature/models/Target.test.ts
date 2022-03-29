import {createAttributeTarget, createPropertyTarget, isAttributeTarget, isPropertyTarget} from './Target';
import {Attribute} from './Attribute';
import {getDefaultTextTarget} from '../components/TargetDetails/Text/model';
import {getDefaultNumberTarget} from '../components/TargetDetails/Number/model';

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

test('it throw an exception if an attribute type is not supported', () => {
  expect(() => {
    createAttributeTarget(getAttribute('name', 'toto'), null, null);
  }).toThrowError('Invalid attribute target "toto"');
});

test('it can get the default attribute target by attribute type', () => {
  expect(createAttributeTarget(getAttribute('pim_catalog_number', 'pim_catalog_number'), 'ecommerce', 'br_FR')).toEqual(
    getDefaultNumberTarget(getAttribute('pim_catalog_number', 'pim_catalog_number'), 'ecommerce', 'br_FR')
  );
  expect(createAttributeTarget(getAttribute('pim_catalog_textarea'), 'ecommerce', 'br_FR')).toEqual(
    getDefaultTextTarget(getAttribute('pim_catalog_textarea'), 'ecommerce', 'br_FR')
  );
  expect(createAttributeTarget(getAttribute('pim_catalog_text'), 'ecommerce', 'br_FR')).toEqual(
    getDefaultTextTarget(getAttribute('pim_catalog_text'), 'ecommerce', 'br_FR')
  );
});
