import {
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
  filterMeasurementFamily,
  sortMeasurementFamily,
} from 'akeneomeasure/model/measurement-family';

describe('measurement family', () => {
  it('should provide a label', () => {
    const label = getMeasurementFamilyLabel(
      {
        labels: {
          en_US: 'Area',
        },
      },
      'en_US'
    );

    expect(label).toEqual('Area');
  });

  it('should provide a fallback label', () => {
    const label = getMeasurementFamilyLabel(
      {
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
      },
      'fr_FR'
    );

    expect(label).toEqual('[AREA]');
  });

  it('should provide a unit label', () => {
    const label = getStandardUnitLabel(
      {
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
          },
        ],
      },
      'en_US'
    );

    expect(label).toEqual('Square Meter');
  });

  it('should provide a unit fallback label', () => {
    const label = getStandardUnitLabel(
      {
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
          },
        ],
      },
      'fr_FR'
    );

    expect(label).toEqual('[SQUARE_METER]');
  });

  it('should provide a unit fallback label if label does not exist', () => {
    const label = getStandardUnitLabel(
      {
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
          },
        ],
      },
      'fr_FR'
    );

    expect(label).toEqual('[SQUARE_METER]');
  });

  it('should provide a unit fallback label if unit does not exist', () => {
    const label = getStandardUnitLabel(
      {
        code: 'AREA',
        labels: {
          en_US: 'Area',
        },
        standard_unit_code: 'SQUARE_FEET',
        units: [
          {
            code: 'SQUARE_METER',
            labels: {
              en_US: 'Square Meter',
            },
          },
        ],
      },
      'fr_FR'
    );

    expect(label).toEqual('[SQUARE_FEET]');
  });

  it('should filter a measurement family on label and code', () => {
    expect(
      filterMeasurementFamily(
        {
          code: 'AREA',
          labels: {
            en_US: 'Area',
          },
        },
        're',
        'en_US'
      )
    ).toEqual(true);

    expect(
      filterMeasurementFamily(
        {
          code: 'AREA',
          labels: {
            en_US: 'Area',
          },
        },
        're',
        'fr_FR'
      )
    ).toEqual(true);

    expect(
      filterMeasurementFamily(
        {
          code: 'AREA',
          labels: {
            en_US: 'Area',
          },
        },
        'nice',
        'fr_FR'
      )
    ).toEqual(false);

    expect(
      filterMeasurementFamily(
        {
          code: 'AREA',
          labels: {
            fr_FR: 'Aire',
          },
        },
        'aire',
        'fr_FR'
      )
    ).toEqual(true);
  });

  it('should sort two measurement families', () => {
    expect(
      sortMeasurementFamily(
        'Ascending',
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
        'Descending',
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
        'Ascending',
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
        'Ascending',
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
        }
      )
    ).toEqual(1);

    expect(
      sortMeasurementFamily(
        'Ascending',
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
        },
        {
          code: 'AREB',
          standard_unit_code: 'SQUARE_METEB',
          labels: {
            fr_FR: 'Aire',
          },
          units: [],
        }
      )
    ).toEqual(1);

    expect(sortMeasurementFamily('Ascending', 'en_US', 'yolo')({}, {})).toEqual(0);
  });
});
