import {createAttributeTarget, createPropertyTarget, isAttributeTarget, isPropertyTarget} from './Target';

test('it can create an attribute target', () => {
  expect(createAttributeTarget('name', null, null)).toEqual({
    action: 'set',
    code: 'name',
    ifEmpty: 'skip',
    onError: 'skipLine',
    type: 'attribute',
    channel: null,
    locale: null,
  });

  expect(createAttributeTarget('name', 'ecommerce', null)).toEqual({
    action: 'set',
    code: 'name',
    ifEmpty: 'skip',
    onError: 'skipLine',
    type: 'attribute',
    channel: 'ecommerce',
    locale: null,
  });

  expect(createAttributeTarget('name', null, 'fr_FR')).toEqual({
    action: 'set',
    code: 'name',
    ifEmpty: 'skip',
    onError: 'skipLine',
    type: 'attribute',
    channel: null,
    locale: 'fr_FR',
  });

  expect(createAttributeTarget('name', 'mobile', 'fr_FR')).toEqual({
    action: 'set',
    code: 'name',
    ifEmpty: 'skip',
    onError: 'skipLine',
    type: 'attribute',
    channel: 'mobile',
    locale: 'fr_FR',
  });
});

test('it can create a property target', () => {
  expect(createPropertyTarget('family')).toEqual({
    action: 'set',
    code: 'family',
    ifEmpty: 'skip',
    onError: 'skipLine',
    type: 'property',
  });
});

test('it can tell if a Target is an attribute target', () => {
  expect(
    isAttributeTarget({
      action: 'set',
      code: 'name',
      ifEmpty: 'skip',
      onError: 'skipLine',
      type: 'attribute',
      channel: null,
      locale: null,
    })
  ).toEqual(true);

  expect(
    isAttributeTarget({
      action: 'set',
      code: 'name',
      ifEmpty: 'skip',
      onError: 'skipLine',
      type: 'property',
    })
  ).toEqual(false);

  expect(
    isAttributeTarget({
      action: 'set',
      code: 'name',
      ifEmpty: 'skip',
      onError: 'skipLine',
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
      action: 'set',
      code: 'name',
      ifEmpty: 'skip',
      onError: 'skipLine',
      type: 'property',
    })
  ).toEqual(true);

  expect(
    isPropertyTarget({
      action: 'set',
      code: 'name',
      ifEmpty: 'skip',
      onError: 'skipLine',
      type: 'attribute',
      channel: null,
      locale: null,
    })
  ).toEqual(false);
});
