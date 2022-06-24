import {EnabledTarget, isEnabledTarget} from './model';
import {NumberTarget} from '../../Attribute';

test('it returns true if it is an enabled target', () => {
  const enabledTarget: EnabledTarget = {
    code: 'enabled',
    type: 'property',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isEnabledTarget(enabledTarget)).toEqual(true);
});

test('it returns false if it is not an enabled target', () => {
  const numberTarget: NumberTarget = {
    code: 'name',
    type: 'attribute',
    attribute_type: 'pim_catalog_number',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isEnabledTarget(numberTarget)).toEqual(false);
});
