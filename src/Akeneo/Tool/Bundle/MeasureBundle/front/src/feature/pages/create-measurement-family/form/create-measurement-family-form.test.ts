import {initializeCreateMeasurementFamilyForm, createMeasurementFamilyFromForm} from './create-measurement-family-form';

describe('create-measurement-family-form', () => {
  test('It can create an empty form state', () => {
    const state = initializeCreateMeasurementFamilyForm();

    expect(state).toEqual({
      family_code: '',
      family_label: '',
      standard_unit_code: '',
      standard_unit_label: '',
      standard_unit_symbol: '',
    });
  });

  test('It can create a measurement family from an empty form state', () => {
    const state = initializeCreateMeasurementFamilyForm();
    const locale = 'en_US';
    const measurementFamily = createMeasurementFamilyFromForm(state, locale);

    expect(measurementFamily).toEqual({
      code: '',
      labels: {
        en_US: '',
      },
      standard_unit_code: '',
      units: [
        {
          code: '',
          labels: {
            en_US: '',
          },
          symbol: '',
          convert_from_standard: [
            {
              operator: 'mul',
              value: '1',
            },
          ],
        },
      ],
      is_locked: false,
    });
  });

  test('It can create a measurement family from a form state with values', () => {
    const state = {
      family_code: 'custom_metric',
      family_label: 'My custom metric',
      standard_unit_code: 'METER',
      standard_unit_label: 'Meters',
      standard_unit_symbol: 'm',
    };
    const locale = 'en_US';
    const measurementFamily = createMeasurementFamilyFromForm(state, locale);

    expect(measurementFamily).toEqual({
      code: 'custom_metric',
      labels: {
        en_US: 'My custom metric',
      },
      standard_unit_code: 'METER',
      units: [
        {
          code: 'METER',
          labels: {
            en_US: 'Meters',
          },
          symbol: 'm',
          convert_from_standard: [
            {
              operator: 'mul',
              value: '1',
            },
          ],
        },
      ],
      is_locked: false,
    });
  });
});
