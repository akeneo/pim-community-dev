import {flattenSections} from '../../../components/ColumnDetails/AddSourceDropdown/flattenSections';

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
      sourceType: '',
      type: 'section',
    },
    {
      code: 'category',
      label: 'Categories',
      sourceType: 'property',
      type: 'source',
    },
    {
      code: 'enabled',
      label: 'Activé',
      sourceType: 'property',
      type: 'source',
    },
    {
      code: 'marketing',
      label: 'Marketing',
      sourceType: '',
      type: 'section',
    },
    {
      code: 'name',
      label: 'Nom',
      sourceType: 'attribute',
      type: 'source',
    },
    {
      code: 'description',
      label: 'Description',
      sourceType: 'attribute',
      type: 'source',
    },
    {
      code: 'price',
      label: 'Price',
      sourceType: 'attribute',
      type: 'source',
    },
    {
      code: 'release_date',
      label: 'Release date',
      sourceType: 'attribute',
      type: 'source',
    },
  ]);
});
