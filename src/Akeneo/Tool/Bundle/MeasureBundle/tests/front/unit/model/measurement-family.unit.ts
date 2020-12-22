import {
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
  getStandardUnit,
  setMeasurementFamilyLabel,
  setUnitLabel,
  setUnitOperations,
  setUnitSymbol,
  sortMeasurementFamily,
  filterOnLabelOrCode,
  getUnitIndex,
  removeUnit,
  addUnit,
} from 'akeneomeasure/model/measurement-family';

const measurementFamily = {
  code: 'AREA',
  labels: {
    en_US: 'Area',
  },
  standard_unit_code: 'SQUARE_METER',
  units: [
    {
      code: 'SQUARE_METER',
      labels: {
        en_US: 'Square Meter',
      },
      symbol: '',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    },
    {
      code: 'SQUARE_KILOMETER',
      labels: {
        en_US: 'Square Kilometer',
      },
      symbol: '',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1000',
        },
      ],
    },
  ],
  is_locked: false,
};

describe('measurement family', () => {
  it('should provide a label', () => {
    const label = getMeasurementFamilyLabel(measurementFamily, 'en_US');

    expect(label).toEqual('Area');
  });

  it('should provide a fallback label', () => {
    const label = getMeasurementFamilyLabel(measurementFamily, 'fr_FR');

    expect(label).toEqual('[AREA]');
  });

  it('should provide a unit label', () => {
    const label = getStandardUnitLabel(measurementFamily, 'en_US');

    expect(label).toEqual('Square Meter');
  });

  it('should provide a unit fallback label', () => {
    const label = getStandardUnitLabel(measurementFamily, 'fr_FR');

    expect(label).toEqual('[SQUARE_METER]');
  });

  it('should provide a unit fallback label if label does not exist', () => {
    const label = getStandardUnitLabel(measurementFamily, 'fr_FR');

    expect(label).toEqual('[SQUARE_METER]');
  });

  it('should set the provided label on the measurement family', () => {
    const newMeasurementFamily = setMeasurementFamilyLabel(measurementFamily, 'fr_FR', 'Aire');

    expect(newMeasurementFamily.labels.fr_FR).toEqual('Aire');
  });

  it('should set the provided label on the unit in the measurement family', () => {
    const newMeasurementFamily = setUnitLabel(measurementFamily, 'SQUARE_METER', 'fr_FR', 'Mètre carré');

    expect(newMeasurementFamily.units[0].labels.fr_FR).toEqual('Mètre carré');
  });

  it('should set the provided operations on the unit in the measurement family', () => {
    const newMeasurementFamily = setUnitOperations(measurementFamily, 'SQUARE_METER', [{operator: 'div', value: '3'}]);

    expect(newMeasurementFamily.units[0].convert_from_standard).toEqual([{operator: 'div', value: '3'}]);
  });

  it('should remove the provided unit (using the unit code) from the measurement family', () => {
    expect(measurementFamily.units.length).toEqual(2);

    const newMeasurementFamily = removeUnit(measurementFamily, 'SQUARE_KILOMETER');

    expect(newMeasurementFamily.units.length).toEqual(1);
  });

  it('should add the provided unit in the measurement family', () => {
    expect(measurementFamily.units.length).toEqual(2);

    const newMeasurementFamily = addUnit(measurementFamily, {
      code: 'CUSTOM',
      labels: {
        en_US: 'Custom',
      },
      symbol: 'c',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    });

    expect(newMeasurementFamily.units.length).toEqual(3);
  });

  it('should set the provided symbol on the unit in the measurement family', () => {
    const newMeasurementFamily = setUnitSymbol(measurementFamily, 'SQUARE_METER', 'new symbol');

    expect(newMeasurementFamily.units[0].symbol).toEqual('new symbol');
  });

  it('should return the unit index in the measurement family unit list', () => {
    expect(getUnitIndex(measurementFamily, 'SQUARE_METER')).toEqual(0);
    expect(getUnitIndex(measurementFamily, 'UNKNOWN')).toEqual(-1);
  });

  it('should return the standard unit from the measurement family', () => {
    expect(getStandardUnit(measurementFamily)).toEqual({
      code: 'SQUARE_METER',
      labels: {
        en_US: 'Square Meter',
      },
      symbol: '',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    });
  });

  it('should throw if the standard unit from the measurement family is not found', () => {
    expect(() => getStandardUnit({...measurementFamily, standard_unit_code: 'UNKNOWN'})).toThrowError();
  });

  it('should filter a measurement family on label and code', () => {
    expect(
      filterOnLabelOrCode(
        're',
        'en_US'
      )({
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
      })
    ).toEqual(true);

    expect(
      filterOnLabelOrCode(
        're',
        'fr_FR'
      )({
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
      })
    ).toEqual(true);

    expect(
      filterOnLabelOrCode(
        'nice',
        'fr_FR'
      )({
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
      })
    ).toEqual(false);

    expect(
      filterOnLabelOrCode(
        'aire',
        'fr_FR'
      )({
        code: 'AREA',
        labels: {
          fr_FR: 'Aire',
        },
      })
    ).toEqual(true);
  });

  it('should sort two measurement families', () => {
    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'label'
      )(
        {
          code: 'AREA',
          labels: {
            fr_FR: 'Aire',
          },
        },
        {
          code: 'AREB',
          labels: {
            fr_FR: 'Aire',
          },
        }
      )
    ).toEqual(-1);

    expect(
      sortMeasurementFamily(
        'descending',
        'en_US',
        'label'
      )(
        {
          code: 'ARE',
          labels: {
            en_US: 'Aireb',
          },
        },
        {
          code: 'AREB',
          labels: {
            en_US: 'Airea',
          },
        }
      )
    ).toEqual(-1);

    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'code'
      )(
        {
          code: 'AREA',
          labels: {
            fr_FR: 'Aire',
          },
        },
        {
          code: 'AREB',
          labels: {
            fr_FR: 'Aire',
          },
        }
      )
    ).toEqual(-1);

    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'standard_unit'
      )(
        {
          code: 'AREA',
          standard_unit_code: 'SQUARE_METER',
          labels: {
            fr_FR: 'Aire',
          },
          units: [
            {
              code: 'SQUARE_METER',
              labels: {
                en_US: 'Square Meter',
              },
            },
          ],
          is_locked: false,
        },
        {
          code: 'AREB',
          standard_unit_code: 'SQUARE_METEB',
          labels: {
            fr_FR: 'Aire',
          },
          units: [
            {
              code: 'SQUARE_METEB',
              labels: {
                en_US: 'Square Meteb',
              },
            },
          ],
          is_locked: false,
        }
      )
    ).toEqual(1);

    expect(
      sortMeasurementFamily(
        'ascending',
        'en_US',
        'unit_count'
      )(
        {
          code: 'AREA',
          standard_unit_code: 'SQUARE_METER',
          labels: {
            fr_FR: 'Aire',
          },
          units: [
            {
              code: 'SQUARE_METER',
              labels: {
                en_US: 'Square Meter',
              },
            },
          ],
          is_locked: false,
        },
        {
          code: 'AREB',
          standard_unit_code: 'SQUARE_METEB',
          labels: {
            fr_FR: 'Aire',
          },
          units: [],
          is_locked: false,
        }
      )
    ).toEqual(1);

    expect(sortMeasurementFamily('ascending', 'en_US', 'yolo')({}, {})).toEqual(0);
  });
});
