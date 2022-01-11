import {generateColumns} from './configuration';

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
