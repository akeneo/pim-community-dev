import {getDefaultMeasurementTarget, isMeasurementTarget, MeasurementTarget} from './model';
import {TextTarget} from '../Text/model';

test('it returns true if it is a measurement target', () => {
  const measurementTarget: MeasurementTarget = {
    code: 'power',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {
      decimal_separator: ',',
      unit: 'WATT',
    },
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isMeasurementTarget(measurementTarget)).toBe(true);
});

test('it returns false if it is not a measurement target', () => {
  const textTarget: TextTarget = {
    code: 'text',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isMeasurementTarget(textTarget)).toBe(false);
});

test('it returns a default measurement target', () => {
  const attribute = {
    code: 'weight',
    type: 'pim_catalog_metric',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
    decimals_allowed: true,
    metric_family: 'Weight',
    default_metric_unit: 'KILOGRAM',
  };

  const measurementTarget: MeasurementTarget = {
    code: 'weight',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {
      decimal_separator: '.',
      unit: 'KILOGRAM',
    },
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(getDefaultMeasurementTarget(attribute, null, null)).toEqual(measurementTarget);
});
