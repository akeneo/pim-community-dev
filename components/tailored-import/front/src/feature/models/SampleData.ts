import {Translate} from '@akeneo-pim-community/shared';

const SAMPLE_DATA_MAX_LENGTH = 100;
const ELLIPSIS_CHARACTER = '…';

type SampleData = string | null;
type PreviewData = string | string[] | null;

const formatSampleData = (translate: Translate, sampleData: SampleData): string => {
  if (sampleData === null) {
    return translate('akeneo.tailored_import.data_mapping.preview.placeholder');
  }

  return (
    sampleData.substring(0, SAMPLE_DATA_MAX_LENGTH) +
    (sampleData.length > SAMPLE_DATA_MAX_LENGTH ? ELLIPSIS_CHARACTER : '')
  );
};

const replaceSampleData = (sampleData: SampleData[], index: number, value: SampleData) => [
  ...sampleData.slice(0, index),
  value,
  ...sampleData.slice(index + 1),
];

export {formatSampleData, replaceSampleData};
export type {PreviewData, SampleData};
