import {transformVolumesToAxis} from './catalogVolumeWrapper';

test('it renders catalog volumes by axis', () => {
  const volumes = {
    count_products: {
      value: 1389,
      has_warning: false,
      type: 'count',
    },
    average_max_attributes_per_family: {
      value: {
        average: 4,
        max: 43,
      },
      has_warning: false,
      type: 'average_max',
    },
  };

  const catalogVolumesByAxis = transformVolumesToAxis(volumes);

  expect(catalogVolumesByAxis[0]).toEqual({
    name: 'product',
    catalogVolumes: [
      {
        name: 'count_products',
        type: 'count',
        value: 1389,
      },
    ],
  });

  expect(catalogVolumesByAxis[1]).toEqual({
    name: 'product_structure',
    catalogVolumes: [
      {
        name: 'average_max_attributes_per_family',
        type: 'average_max',
        value: {
          average: 4,
          max: 43,
        },
      },
    ],
  });
});

test('it renders catalog volumes by axis when a key is missing', () => {
  const volumes = {
    count_products: {
      has_warning: false,
      type: 'count',
    },
  };

  const catalogVolumesByAxis = transformVolumesToAxis(volumes);

  expect(catalogVolumesByAxis.length).toBeGreaterThan(0);
  expect(catalogVolumesByAxis[0]).toEqual({
    name: 'product',
    catalogVolumes: [
      {
        name: 'count_products',
        type: 'count',
        value: undefined,
      },
    ],
  });
});
