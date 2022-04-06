import {formatSampleData, replaceSampleData} from './SampleData';

test('it replaces a sample data', () => {
  const sampleData = ['sample data 1', 'sample data 2', 'sample data 3'];

  expect(replaceSampleData(sampleData, 0, 'new sample data')).toEqual([
    'new sample data',
    'sample data 2',
    'sample data 3',
  ]);

  expect(replaceSampleData(sampleData, 1, 'another sample data')).toEqual([
    'sample data 1',
    'another sample data',
    'sample data 3',
  ]);

  expect(replaceSampleData(sampleData, 2, 'refreshed sample data')).toEqual([
    'sample data 1',
    'sample data 2',
    'refreshed sample data',
  ]);
});

test('it truncates a too long sample data and add an ellipsis character', () => {
  expect(formatSampleData('sample data')).toHaveLength(11);
  expect(formatSampleData('X'.repeat(110))).toEqual('X'.repeat(100) + '…');
  expect(formatSampleData('X'.repeat(110))).toHaveLength(101);
});
