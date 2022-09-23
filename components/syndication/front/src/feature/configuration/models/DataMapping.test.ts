import {Channel} from '@akeneo-pim-community/shared';
import {Attribute} from './pim/Attribute';
import {
  createDataMapping,
  addDataMapping,
  removeDataMapping,
  updateDataMapping,
  addAttributeSource,
  addPropertySource,
  updateSource,
  removeSource,
  addAssociationTypeSource,
  filterEmptyOperations,
  filterDataMappings,
} from './DataMapping';
import {Source} from './Source';
import {AssociationType} from './pim/AssociationType';
import {AttributeSource} from 'feature';

const channels: Channel[] = [
  {
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
    code: 'ecommerce',
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        region: 'FR',
        language: 'fr',
      },
    ],
    labels: {
      fr_FR: 'Ecommerce',
    },
  },
  {
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
    code: 'mobile',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
        region: 'DE',
        language: 'de',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'US',
        language: 'en',
      },
    ],
    labels: {
      fr_FR: 'Mobile',
    },
  },
  {
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
    code: 'print',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
        region: 'DE',
        language: 'de',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        region: 'FR',
        language: 'fr',
      },
    ],
    labels: {
      fr_FR: 'Impression',
    },
  },
];

const attribute: Attribute = {
  type: 'pim_catalog_text',
  code: 'name',
  labels: {
    fr_FR: 'Nom',
  },
  scopable: true,
  localizable: true,
  is_locale_specific: false,
  available_locales: [],
};

test('it creates a dataMapping', () => {
  expect(
    createDataMapping({code: 'Identifier', type: 'string', required: false}, 'fbf9cff9-e95c-4e7d-983b-2947c7df90df')
  ).toEqual({
    format: {
      elements: [],
      type: 'concat',
      space_between: true,
    },
    sources: [],
    target: {
      name: 'Identifier',
      type: 'string',
      required: false,
    },
    uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
  });

  expect(() => {
    createDataMapping({code: 'Identifier', type: 'string', required: false}, 'invalid_uuid');
  }).toThrowError('DataMapping configuration creation requires a valid uuid: "invalid_uuid"');
});

test('it appends a dataMapping', () => {
  const existingDataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'abf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const dataMappingToAdd = createDataMapping(
    {code: 'Identifier', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  expect(addDataMapping([], dataMappingToAdd)).toEqual([dataMappingToAdd]);
  expect(addDataMapping([existingDataMapping], dataMappingToAdd)).toEqual([existingDataMapping, dataMappingToAdd]);
});

test('it removes a dataMapping', () => {
  const existingDataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'abf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const dataMappingToRemove = createDataMapping(
    {code: 'Identifier', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  expect(removeDataMapping([], 'abf9cff9-e95c-4e7d-983b-2947c7df90df')).toEqual([]);
  expect(removeDataMapping([existingDataMapping, dataMappingToRemove], 'fbf9cff9-e95c-4e7d-983b-2947c7df90df')).toEqual(
    [existingDataMapping]
  );
});

test('it updates a dataMapping', () => {
  const existingDataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  existingDataMapping.sources = [
    {
      uuid: 'uuid',
      locale: null,
      channel: null,
      code: 'name',
      type: 'attribute',
      operations: [],
      selection: {type: 'code'},
    } as AttributeSource,
  ];
  const anotherDataMapping = createDataMapping(
    {code: 'Another', type: 'string', required: false},
    'abf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  anotherDataMapping.sources = [
    {
      uuid: 'uuid',
      locale: null,
      channel: null,
      code: 'name',
      type: 'attribute',
      operations: [],
      selection: {type: 'code'},
    } as AttributeSource,
  ];
  const dataMappingToUpdate = createDataMapping(
    {code: 'Identifier', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  dataMappingToUpdate.sources = [
    {
      uuid: 'uuid',
      locale: null,
      channel: null,
      code: 'name',
      type: 'attribute',
      operations: [],
      selection: {type: 'code'},
    } as AttributeSource,
  ];
  expect(updateDataMapping([], dataMappingToUpdate)).toEqual([]);
  expect(updateDataMapping([existingDataMapping], dataMappingToUpdate)).toEqual([dataMappingToUpdate]);
  expect(updateDataMapping([anotherDataMapping, existingDataMapping], dataMappingToUpdate)).toEqual([
    anotherDataMapping,
    dataMappingToUpdate,
  ]);
});

test('it adds attribute source', () => {
  const dataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const newDataMapping = addAttributeSource(dataMapping, attribute, channels);
  const firstSourceUuid = newDataMapping.sources[0].uuid;

  expect(newDataMapping).toEqual({
    uuid: dataMapping.uuid,
    target: {
      name: 'The first dataMapping',
      type: 'string',
      required: false,
    },
    sources: [
      {
        uuid: firstSourceUuid,
        type: 'attribute',
        code: 'name',
        channel: 'ecommerce',
        locale: 'en_US',
        operations: {},
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          uuid: firstSourceUuid,
          value: firstSourceUuid,
        },
      ],
      space_between: true,
    },
  });
});

test('it adds a locale specific attribute source', () => {
  const dataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const localeSpecificAttribute: Attribute = {
    type: 'pim_catalog_text',
    code: 'name',
    labels: {
      fr_FR: 'Nom',
    },
    scopable: true,
    localizable: true,
    is_locale_specific: true,
    available_locales: ['fr_FR'],
  };

  const newDataMapping = addAttributeSource(dataMapping, localeSpecificAttribute, channels);
  const firstSourceUuid = newDataMapping.sources[0].uuid;

  expect(newDataMapping).toEqual({
    uuid: dataMapping.uuid,
    target: {
      name: 'The first dataMapping',
      type: 'string',
      required: false,
    },
    sources: [
      {
        uuid: firstSourceUuid,
        type: 'attribute',
        code: 'name',
        channel: 'ecommerce',
        locale: 'fr_FR',
        operations: {},
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          uuid: firstSourceUuid,
          value: firstSourceUuid,
        },
      ],
      space_between: true,
    },
  });
});

test('it adds property source', () => {
  const dataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const newDataMapping = addPropertySource(dataMapping, 'categories', []);
  const firstSourceUuid = newDataMapping.sources[0].uuid;

  expect(newDataMapping).toEqual({
    uuid: dataMapping.uuid,
    target: {
      name: 'The first dataMapping',
      type: 'string',
      required: false,
    },
    sources: [
      {
        uuid: firstSourceUuid,
        type: 'property',
        code: 'categories',
        channel: null,
        locale: null,
        operations: {},
        selection: {
          type: 'code',
          separator: ',',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          uuid: firstSourceUuid,
          value: firstSourceUuid,
        },
      ],
      space_between: true,
    },
  });
});

test('it adds association type source', () => {
  const dataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const associationType: AssociationType = {
    code: 'UPSELL',
    labels: {},
    is_quantified: false,
  };

  const newDataMapping = addAssociationTypeSource(dataMapping, associationType);
  const firstSourceUuid = newDataMapping.sources[0].uuid;
  expect(newDataMapping).toEqual({
    uuid: dataMapping.uuid,
    target: {
      name: 'The first dataMapping',
      type: 'string',
      required: false,
    },
    sources: [
      {
        uuid: firstSourceUuid,
        type: 'association_type',
        code: 'UPSELL',
        channel: null,
        locale: null,
        operations: {},
        selection: {
          type: 'code',
          entity_type: 'products',
          separator: ',',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          uuid: firstSourceUuid,
          value: firstSourceUuid,
        },
      ],
      space_between: true,
    },
  });
});

test('it does nothing when update an nonexistent source', () => {
  const dataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const updatedSource: Source = {
    uuid: 'abf9cff9-e95c-4e7d-983b-2947c7df90df',
    type: 'attribute',
    code: 'description',
    channel: null,
    locale: null,
    operations: {},
    selection: {
      type: 'code',
      separator: ',',
    },
  };

  const updatedConfiguration = updateSource(dataMapping, updatedSource);

  expect(updatedConfiguration).toEqual(dataMapping);
});

test('it updates a source', () => {
  const dataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const dataMappingWithSource = addAttributeSource(dataMapping, attribute, channels);
  const firstSourceUuid = dataMappingWithSource.sources[0].uuid;
  const updatedSource: Source = {
    uuid: firstSourceUuid,
    type: 'attribute',
    code: 'name',
    channel: 'mobile',
    locale: 'fr_FR',
    operations: {},
    selection: {
      type: 'code',
    },
  };

  const updatedConfiguration = updateSource(dataMappingWithSource, updatedSource);

  expect(updatedConfiguration).toEqual({
    uuid: dataMapping.uuid,
    target: {
      name: 'The first dataMapping',
      type: 'string',
      required: false,
    },
    sources: [
      {
        uuid: firstSourceUuid,
        type: 'attribute',
        code: 'name',
        channel: 'mobile',
        locale: 'fr_FR',
        operations: {},
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          uuid: firstSourceUuid,
          value: firstSourceUuid,
        },
      ],
      space_between: true,
    },
  });
});

test('it removes a source', () => {
  const dataMapping = createDataMapping(
    {code: 'The first dataMapping', type: 'string', required: false},
    'fbf9cff9-e95c-4e7d-983b-2947c7df90df'
  );
  const dataMappingWithSource = addAttributeSource(dataMapping, attribute, channels);

  const updatedConfiguration = removeSource(dataMappingWithSource, dataMappingWithSource.sources[0]);

  expect(updatedConfiguration).toEqual({
    uuid: dataMapping.uuid,
    target: {
      name: 'The first dataMapping',
      type: 'string',
      required: false,
    },
    sources: [],
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  });
});

test('it filters empty operations', () => {
  const operations = {
    replacement: {
      type: 'replacement',
      mapping: {
        true: 'vrai',
        false: 'faux',
      },
    },
    empty: undefined,
    another: {not: 'empty'},
  };

  expect(filterEmptyOperations(operations)).toEqual({
    replacement: {
      type: 'replacement',
      mapping: {
        true: 'vrai',
        false: 'faux',
      },
    },
    another: {not: 'empty'},
  });
});

test('it filters dataMappings based on a search value', () => {
  const dataMappings = [
    createDataMapping({code: 'FIRST', required: false, type: 'string'}, 'fbf9cff9-e95c-4e7d-983b-2947c7df90df'),
    createDataMapping({code: 'first', required: false, type: 'string'}, 'fbf9cff9-e95c-4e7d-983b-2947c7df90de'),
    createDataMapping({code: 'fir', required: false, type: 'string'}, 'fbf9cff9-e95c-4e7d-983b-2947c7df90dd'),
  ];

  expect(filterDataMappings(dataMappings, '')).toHaveLength(3);
  expect(filterDataMappings(dataMappings, 'fir')).toHaveLength(3);
  expect(filterDataMappings(dataMappings, 'FIR')).toHaveLength(3);
  expect(filterDataMappings(dataMappings, 'ir')).toHaveLength(3);
  expect(filterDataMappings(dataMappings, 'st')).toHaveLength(2);
  expect(filterDataMappings(dataMappings, 'first')).toHaveLength(2);
  expect(filterDataMappings(dataMappings, 'FIRST')).toHaveLength(2);
  expect(filterDataMappings(dataMappings, 'firsttt')).toHaveLength(0);
});
