import {
  createColumn,
  addColumn,
  removeColumn,
  updateColumn,
  addAttributeSource,
  addPropertySource,
  updateSource,
  removeSource,
  Source,
} from './ColumnConfiguration';

const channels = [
  {
    code: 'ecommerce',
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
      },
    ],
    labels: {
      fr_FR: 'Ecommerce',
    },
  },
  {
    code: 'mobile',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
      },
    ],
    labels: {
      fr_FR: 'Mobile',
    },
  },
  {
    code: 'print',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
      },
    ],
    labels: {
      fr_FR: 'Impression',
    },
  },
];

const attribute = {
  code: 'name',
  labels: {
    fr_FR: 'Nom',
  },
  scopable: true,
  localizable: true,
};

test('it creates a column', () => {
  expect(createColumn('Identifier', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df')).toEqual({
    format: {elements: [], type: 'concat'},
    sources: [],
    target: 'Identifier',
    uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
  });

  expect(() => {
    createColumn('Identifier', 'invalid_uuid');
  }).toThrowError('Column configuration creation requires a valid uuid: "invalid_uuid"');
});

test('it appends a column', () => {
  const existingColumn = createColumn('The first column', 'abf9cff9-e95c-4e7d-983b-2947c7df90df');
  const columnToAdd = createColumn('Identifier', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  expect(addColumn([], columnToAdd)).toEqual([columnToAdd]);
  expect(addColumn([existingColumn], columnToAdd)).toEqual([existingColumn, columnToAdd]);
});

test('it removes a column', () => {
  const existingColumn = createColumn('The first column', 'abf9cff9-e95c-4e7d-983b-2947c7df90df');
  const columnToRemove = createColumn('Identifier', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  expect(removeColumn([], 'abf9cff9-e95c-4e7d-983b-2947c7df90df')).toEqual([]);
  expect(removeColumn([existingColumn, columnToRemove], 'fbf9cff9-e95c-4e7d-983b-2947c7df90df')).toEqual([
    existingColumn,
  ]);
});

test('it updates a column', () => {
  const existingColumn = createColumn('The first column', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  const anotherColumn = createColumn('Another', 'abf9cff9-e95c-4e7d-983b-2947c7df90df');
  const columnToUpdate = createColumn('Identifier', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  expect(updateColumn([], columnToUpdate)).toEqual([]);
  expect(updateColumn([existingColumn], columnToUpdate)).toEqual([columnToUpdate]);
  expect(updateColumn([anotherColumn, existingColumn], columnToUpdate)).toEqual([anotherColumn, columnToUpdate]);
});

test('it add attribute source', () => {
  const columnConfiguration = createColumn('The first column', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  const newColumnConfiguration = addAttributeSource(columnConfiguration, 'name', attribute, channels);
  expect(newColumnConfiguration).toEqual({
    uuid: columnConfiguration.uuid,
    target: 'The first column',
    sources: [
      {
        uuid: newColumnConfiguration.sources[0].uuid,
        type: 'attribute',
        code: 'name',
        channel: 'ecommerce',
        locale: 'en_US',
        operations: [],
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [],
    },
  });
});

test('it adds a locale specific attribute source', () => {
  const columnConfiguration = createColumn('The first column', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  const localeSpecificAttribute = {
    code: 'name',
    labels: {
      fr_FR: 'Nom',
    },
    scopable: true,
    localizable: true,
    is_locale_specific: true,
    available_locales: ['fr_FR'],
  };

  const newColumnConfiguration = addAttributeSource(columnConfiguration, 'name', localeSpecificAttribute, channels);
  expect(newColumnConfiguration).toEqual({
    uuid: columnConfiguration.uuid,
    target: 'The first column',
    sources: [
      {
        uuid: newColumnConfiguration.sources[0].uuid,
        type: 'attribute',
        code: 'name',
        channel: 'ecommerce',
        locale: 'fr_FR',
        operations: [],
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [],
    },
  });
});

test('it add property source', () => {
  const columnConfiguration = createColumn('The first column', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  const newColumnConfiguration = addPropertySource(columnConfiguration, 'category');
  expect(newColumnConfiguration).toEqual({
    uuid: columnConfiguration.uuid,
    target: 'The first column',
    sources: [
      {
        uuid: newColumnConfiguration.sources[0].uuid,
        type: 'property',
        code: 'category',
        channel: null,
        locale: null,
        operations: [],
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [],
    },
  });
});

test('it does nothing when update an nonexistent source', () => {
  const columnConfiguration = createColumn('The first column', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  const updatedSource: Source = {
    uuid: 'abf9cff9-e95c-4e7d-983b-2947c7df90df',
    type: 'property',
    code: 'category',
    channel: null,
    locale: null,
    operations: [],
    selection: {
      type: 'code',
    },
  };

  const updatedConfiguration = updateSource(columnConfiguration, updatedSource);

  expect(updatedConfiguration).toEqual(columnConfiguration);
});

test('it update a source', () => {
  const columnConfiguration = createColumn('The first column', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  const columnConfigurationWithSource = addAttributeSource(columnConfiguration, 'category', attribute, channels);
  const updatedSource: Source = {
    uuid: columnConfigurationWithSource.sources[0].uuid,
    type: 'attribute',
    code: 'name',
    channel: 'mobile',
    locale: 'fr_FR',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  const updatedConfiguration = updateSource(columnConfigurationWithSource, updatedSource);

  expect(updatedConfiguration).toEqual({
    uuid: columnConfiguration.uuid,
    target: 'The first column',
    sources: [
      {
        uuid: columnConfigurationWithSource.sources[0].uuid,
        type: 'attribute',
        code: 'name',
        channel: 'mobile',
        locale: 'fr_FR',
        operations: [],
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [],
    },
  });
});

test('it removes a source', () => {
  const columnConfiguration = createColumn('The first column', 'fbf9cff9-e95c-4e7d-983b-2947c7df90df');
  const columnConfigurationWithSource = addAttributeSource(columnConfiguration, 'category', attribute, channels);

  const updatedConfiguration = removeSource(columnConfigurationWithSource, columnConfigurationWithSource.sources[0]);

  expect(updatedConfiguration).toEqual({
    uuid: columnConfiguration.uuid,
    target: 'The first column',
    sources: [],
    format: {
      type: 'concat',
      elements: [],
    },
  });
});
