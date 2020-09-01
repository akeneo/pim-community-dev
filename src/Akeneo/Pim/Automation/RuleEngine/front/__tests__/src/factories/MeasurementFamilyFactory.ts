import { MeasurementFamily } from '../../../src/models';

export const measurementFamiliesResponse: MeasurementFamily[] = [
  {
    code: 'weight_metric_family',
    labels: {},
    standard_unit_code: 'KILOGRAM',
    units: [
      {
        code: 'MICROGRAM',
        labels: {
          en_US: 'Microgram',
          fr_FR: 'Microgramme',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.000001',
          },
        ],
        symbol: 'μg',
      },
      {
        code: 'MILLIGRAM',
        labels: {
          en_US: 'Milligram',
          fr_FR: 'Milligramme',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.000001',
          },
        ],
        symbol: 'mg',
      },
      {
        code: 'GRAM',
        labels: {
          en_US: 'Gram',
          fr_FR: 'Gramme',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.001',
          },
        ],
        symbol: 'g',
      },
      {
        code: 'KILOGRAM',
        labels: {
          en_US: 'Kilogram',
          fr_FR: 'Kilogramme',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '1',
          },
        ],
        symbol: 'kg',
      },
      {
        code: 'TON',
        labels: {
          en_US: 'Ton',
          fr_FR: 'Tonne',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '1000',
          },
        ],
        symbol: 't',
      },
      {
        code: 'GRAIN',
        labels: {
          en_US: 'Grain',
          fr_FR: 'Grain',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.00006479891',
          },
        ],
        symbol: 'gr',
      },
      {
        code: 'DENIER',
        labels: {
          en_US: 'Denier',
          fr_FR: 'Denier',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.001275',
          },
        ],
        symbol: 'denier',
      },
      {
        code: 'ONCE',
        labels: {
          en_US: 'Once',
          fr_FR: 'Once française',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.03059',
          },
        ],
        symbol: 'once',
      },
      {
        code: 'MARC',
        labels: {
          en_US: 'Marc',
          fr_FR: 'Marc',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.24475',
          },
        ],
        symbol: 'marc',
      },
      {
        code: 'LIVRE',
        labels: {
          en_US: 'Livre',
          fr_FR: 'Livre française',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.4895',
          },
        ],
        symbol: 'livre',
      },
      {
        code: 'OUNCE',
        labels: {
          en_US: 'Ounce',
          fr_FR: 'Once',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.45359237',
          },
          {
            operator: 'div',
            value: '16',
          },
        ],
        symbol: 'oz',
      },
      {
        code: 'POUND',
        labels: {
          en_US: 'Pound',
          fr_FR: 'Livre',
        },
        convert_from_standard: [
          {
            operator: 'mul',
            value: '0.45359237',
          },
        ],
        symbol: 'lb',
      },
    ],
    is_locked: true,
  },
];
