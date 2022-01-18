import {flattenSections} from './flattenSections';

test('it flattens sections in array', () => {
  expect(
    flattenSections([
      {
        code: 'system',
        label: 'System',
        children: [
          {
            code: 'category',
            label: 'Categories',
            type: 'property',
          },
          {
            code: 'enabled',
            label: 'Activé',
            type: 'property',
          },
        ],
      },
      {
        code: 'marketing',
        label: 'Marketing',
        children: [
          {
            code: 'name',
            label: 'Nom',
            type: 'attribute',
          },
          {
            code: 'description',
            label: 'Description',
            type: 'attribute',
          },
        ],
      },
      {
        code: 'marketing',
        label: 'Marketing',
        children: [
          {
            code: 'price',
            label: 'Price',
            type: 'attribute',
          },
          {
            code: 'release_date',
            label: 'Release date',
            type: 'attribute',
          },
        ],
      },
      {
        code: 'technical',
        label: 'Technical',
        children: [],
      },
    ])
  ).toEqual([
    {
      code: 'system',
      label: 'System',
      type: 'section',
    },
    {
      code: 'category',
      label: 'Categories',
      targetType: 'property',
      type: 'target',
    },
    {
      code: 'enabled',
      label: 'Activé',
      targetType: 'property',
      type: 'target',
    },
    {
      code: 'marketing',
      label: 'Marketing',
      type: 'section',
    },
    {
      code: 'name',
      label: 'Nom',
      targetType: 'attribute',
      type: 'target',
    },
    {
      code: 'description',
      label: 'Description',
      targetType: 'attribute',
      type: 'target',
    },
    {
      code: 'price',
      label: 'Price',
      targetType: 'attribute',
      type: 'target',
    },
    {
      code: 'release_date',
      label: 'Release date',
      targetType: 'attribute',
      type: 'target',
    },
  ]);
});
