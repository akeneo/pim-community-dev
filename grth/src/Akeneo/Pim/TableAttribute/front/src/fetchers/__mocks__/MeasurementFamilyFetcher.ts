import {MeasurementFamily} from '../../models/MeasurementFamily';
import {LabelCollection} from '@akeneo-pim-community/shared';

const fetchAll: () => Promise<MeasurementFamily[]> = () => {
  return Promise.resolve([
    {
      code: 'ElectricCharge',
      is_locked: false,
      labels: {
        de_DE: 'Elektrische Ladung',
        en_US: 'Electric charge',
        fr_FR: 'Charge électrique',
      },
      standard_unit_code: 'AMPEREHOUR',
      units: [
        {
          code: 'MILLIAMPEREHOUR',
          labels: {
            de_DE: 'Milliampere-Stunden',
            en_US: 'Milliampere hour',
            fr_FR: 'Milliampères heure',
          },
          convert_from_standard: [
            {
              operator: 'mul',
              value: '0.001',
            },
          ],
          symbol: 'mAh',
        },
        {
          code: 'AMPEREHOUR',
          labels: {
            de_DE: 'Ampere-Stunden',
            en_US: 'Ampere hour',
            fr_FR: 'Ampère heure',
          },
          convert_from_standard: [
            {
              operator: 'mul',
              value: '1',
            },
          ],
          symbol: 'Ah',
        },
        {
          code: 'MILLICOULOMB',
          labels: {
            de_DE: 'Millicoulomb',
            en_US: 'Millicoulomb',
            fr_FR: 'Millicoulomb',
          },
          convert_from_standard: [
            {
              operator: 'div',
              value: '3600000',
            },
          ],
          symbol: 'mC',
        },
      ],
    },
    {
      code: 'Energy',
      is_locked: false,
      labels: {
        de_DE: 'Energie',
        en_US: 'Energy',
        fr_FR: 'Energie',
      },
      standard_unit_code: 'JOULE',
      units: [
        {
          code: 'JOULE',
          labels: {
            de_DE: 'Joule',
            en_US: 'joule',
            fr_FR: 'joule',
          },
          convert_from_standard: [
            {
              operator: 'mul',
              value: '1',
            },
          ],
          symbol: 'J',
        },
        {
          code: 'CALORIE',
          labels: {
            de_DE: 'Kalorien',
            en_US: 'calorie',
            fr_FR: 'calorie',
          },
          convert_from_standard: [
            {
              operator: 'mul',
              value: '4.184',
            },
          ],
          symbol: 'cal',
        },
        {
          code: 'KILOCALORIE',
          labels: {
            de_DE: 'Kilokalorien',
            en_US: 'kilocalorie',
          } as LabelCollection,
          convert_from_standard: [
            {
              operator: 'mul',
              value: '4184',
            },
          ],
          symbol: 'kcal',
        },
      ],
    },
    {
      code: 'Force',
      is_locked: false,
      labels: {
        de_DE: 'Kraft',
        en_US: 'Force',
        fr_FR: 'Force',
      },
      standard_unit_code: 'NEWTON',
      units: [
        {
          code: 'MILLINEWTON',
          labels: {
            en_US: 'Millinewton',
            fr_FR: 'Millinewton',
          },
          convert_from_standard: [
            {
              operator: 'mul',
              value: '0.001',
            },
          ],
          symbol: 'mN',
        },
        {
          code: 'NEWTON',
          labels: {
            en_US: 'Newton',
            fr_FR: 'Newton',
          },
          convert_from_standard: [
            {
              operator: 'mul',
              value: '1',
            },
          ],
          symbol: 'N',
        },
        {
          code: 'KILONEWTON',
          labels: {
            en_US: 'Kilonewton',
            fr_FR: 'Kilonewton',
          },
          convert_from_standard: [
            {
              operator: 'mul',
              value: '1000',
            },
          ],
          symbol: 'kN',
        },
      ],
    },
  ]);
};

const MeasurementFamilyFetcher = {
  fetchAll,
};

export {MeasurementFamilyFetcher};
