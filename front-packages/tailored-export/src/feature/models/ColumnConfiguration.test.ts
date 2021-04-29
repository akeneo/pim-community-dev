import {createColumn, addColumn, removeColumn, updateColumn} from './ColumnConfiguration';

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
