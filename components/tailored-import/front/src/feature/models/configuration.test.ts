import {generateColumns, generateColumnName} from './configuration';

const mockUuid = 'uuid';
jest.mock('akeneo-design-system', () => ({
  uuid: () => mockUuid,
}));

test('it generates columns', () => {
  expect(generateColumns('Sku\tName')).toEqual([
    {
      uuid: mockUuid,
      index: 0,
      label: 'Sku',
    },
    {
      uuid: mockUuid,
      index: 1,
      label: 'Name',
    },
  ]);

  expect(generateColumns('Sku\tName\nremote_croco\tRemote croco')).toEqual([
    {
      uuid: mockUuid,
      index: 0,
      label: 'Sku',
    },
    {
      uuid: mockUuid,
      index: 1,
      label: 'Name',
    },
  ]);

  expect(generateColumns('')).toEqual([]);
});

test('it generates column name', () => {
  expect(generateColumnName({uuid: mockUuid, index: 0, label: 'Sku'})).toEqual('Sku (A)');
  expect(generateColumnName({uuid: mockUuid, index: 25, label: 'Description'})).toEqual('Description (Z)');
  expect(generateColumnName({uuid: mockUuid, index: 26, label: 'Name'})).toEqual('Name (AA)');
  expect(generateColumnName({uuid: mockUuid, index: 51, label: 'Ref'})).toEqual('Ref (AZ)');
  expect(generateColumnName({uuid: mockUuid, index: 52, label: 'EAN'})).toEqual('EAN (BA)');
  expect(generateColumnName({uuid: mockUuid, index: 1023, label: 'Far far away column'})).toEqual(
    'Far far away column (AMJ)'
  );
  expect(generateColumnName({uuid: mockUuid, index: 702, label: 'Triple'})).toEqual('Triple (AAA)');
});
