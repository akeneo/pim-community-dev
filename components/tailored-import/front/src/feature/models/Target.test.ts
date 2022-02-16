import {createAttributeTarget, createPropertyTarget, isAttributeTarget, isPropertyTarget} from './Target';

test('it can create an attribute target', () => {
  expect(createAttributeTarget('name', null, null)).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: null,
    locale: null,
  });

  expect(createAttributeTarget('name', 'ecommerce', null)).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: 'ecommerce',
    locale: null,
  });

  expect(createAttributeTarget('name', null, 'fr_FR')).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: null,
    locale: 'fr_FR',
  });

  expect(createAttributeTarget('name', 'mobile', 'fr_FR')).toEqual({
    action_if_not_empty: 'set',
    code: 'name',
    action_if_empty: 'skip',
    type: 'attribute',
    channel: 'mobile',
    locale: 'fr_FR',
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
      action_if_not_empty: 'set',
      code: 'name',
      action_if_empty: 'skip',
      type: 'attribute',
      channel: null,
      locale: null,
    })
  ).toEqual(true);

  expect(
    isAttributeTarget({
      action_if_not_empty: 'set',
      code: 'name',
      action_if_empty: 'skip',
      type: 'property',
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
      action_if_not_empty: 'set',
      code: 'name',
      action_if_empty: 'skip',
      type: 'property',
    })
  ).toEqual(true);

  expect(
    isPropertyTarget({
      action_if_not_empty: 'set',
      code: 'name',
      action_if_empty: 'skip',
      type: 'attribute',
      channel: null,
      locale: null,
    })
  ).toEqual(false);
});
