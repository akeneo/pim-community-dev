const SAMPLE_DATA_MAX_LENGTH = 100;
const ELLIPSIS_CHARACTER = '…';

type SampleData = string | null;

const formatSampleData = (sampleData: string): string =>
  sampleData.substring(0, SAMPLE_DATA_MAX_LENGTH) +
  (sampleData.length > SAMPLE_DATA_MAX_LENGTH ? ELLIPSIS_CHARACTER : '');

const replaceSampleData = (sampleData: SampleData[], index: number, value: SampleData) => [
  ...sampleData.slice(0, index),
  value,
  ...sampleData.slice(index + 1),
];

export {formatSampleData, replaceSampleData};
export type {SampleData};
