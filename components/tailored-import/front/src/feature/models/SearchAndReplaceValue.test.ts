import {
  filterEmptyReplacements,
  filterOnSearchValue,
  getDefaultSearchAndReplaceValue,
  updateByIndex,
  updateByUuid,
} from './SearchAndReplaceValue';

const replacements = [
  {
    uuid: 'fake-uuid-1',
    what: 'replace me',
    with: 'with that',
    case_sensitive: true,
  },
  {
    uuid: 'fake-uuid-2',
    what: 'another one',
    with: 'with that',
    case_sensitive: true,
  },
];

test('it can get the default search and replace value', () => {
  expect(getDefaultSearchAndReplaceValue()).toEqual({
    uuid: expect.any(String),
    what: '',
    with: '',
    case_sensitive: true,
  });
});

test('it can update a replacement in an array by index', () => {
  expect(
    updateByIndex(
      replacements,
      {
        uuid: 'fake-uuid-3',
        what: '3',
        with: 'three',
        case_sensitive: false,
      },
      1
    )
  ).toEqual([
    {
      uuid: 'fake-uuid-1',
      what: 'replace me',
      with: 'with that',
      case_sensitive: true,
    },
    {
      uuid: 'fake-uuid-3',
      what: '3',
      with: 'three',
      case_sensitive: false,
    },
  ]);
});

test('it can update a replacement in an array by uuid', () => {
  expect(
    updateByUuid(replacements, {
      uuid: 'fake-uuid-1',
      what: '3',
      with: 'three',
      case_sensitive: false,
    })
  ).toEqual([
    {
      uuid: 'fake-uuid-1',
      what: '3',
      with: 'three',
      case_sensitive: false,
    },
    {
      uuid: 'fake-uuid-2',
      what: 'another one',
      with: 'with that',
      case_sensitive: true,
    },
  ]);
});

test('it can filter replacements on search value', () => {
  expect(filterOnSearchValue(replacements, '')).toEqual(replacements);
  expect(filterOnSearchValue(replacements, 'e')).toEqual(replacements);
  expect(filterOnSearchValue(replacements, 'with')).toEqual(replacements);
  expect(filterOnSearchValue(replacements, 'aNoThEr')).toEqual([
    {
      uuid: 'fake-uuid-2',
      what: 'another one',
      with: 'with that',
      case_sensitive: true,
    },
  ]);
});

test('it can filter out empty replacements', () => {
  const replacements = [
    {
      uuid: 'fake-uuid-1',
      what: '',
      with: '',
      case_sensitive: true,
    },
    {
      uuid: 'fake-uuid-2',
      what: 'another one',
      with: 'with that',
      case_sensitive: true,
    },
    {
      uuid: 'fake-uuid-1',
      what: '',
      with: '',
      case_sensitive: true,
    },
  ];

  expect(filterEmptyReplacements(replacements)).toEqual([
    {
      uuid: 'fake-uuid-2',
      what: 'another one',
      with: 'with that',
      case_sensitive: true,
    },
  ]);
});
