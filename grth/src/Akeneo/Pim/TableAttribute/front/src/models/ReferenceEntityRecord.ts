import {LabelCollection} from '@akeneo-pim-community/shared';
import {ReferenceEntityIdentifierOrCode} from './ReferenceEntity';

type RecordIdentifier = string;
type RecordCode = string;

export type ReferenceEntityRecord = {
  code: RecordCode; //"alessi"
  completeness: {complete: number; required: number};
  identifier: RecordIdentifier; // "brand_alessi_dc1c552a-108c-4e1d-9d72-7f17368bdb5a"
  image: {
    extension: string;
    filePath: string;
    mimeType: string;
    originalFilename: string;
    size: number;
  } | null;
  labels: LabelCollection;
  reference_entity_identifier: ReferenceEntityIdentifierOrCode;
};
