import {isSimpleReferenceEntityTarget, SimpleReferenceEntityTarget} from './model';
import {NumberTarget} from '../Number/model';

test('it returns true if it is a simple reference entity target', () => {
  const simpleReferenceEntityTarget: SimpleReferenceEntityTarget = {
    code: 'color',
    type: 'attribute',
    attribute_type: 'akeneo_reference_entity',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isSimpleReferenceEntityTarget(simpleReferenceEntityTarget)).toEqual(true);
});

test('it returns false if it is not a simple reference entity target', () => {
  const numberTarget: NumberTarget = {
    code: 'age',
    type: 'attribute',
    attribute_type: 'pim_catalog_number',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isSimpleReferenceEntityTarget(numberTarget)).toEqual(false);
});
