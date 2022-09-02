import {ChannelCode, LabelCollection, LocaleCode} from '@akeneo-pim-community/shared';
import {ReferenceEntityIdentifierOrCode} from './ReferenceEntity';

type RecordIdentifier = string; //"alessi";
export type RecordCode = string; // "brand_alessi_dc1c552a-108c-4e1d-9d72-7f17368bdb5a";

export type RecordCompleteness = {complete: number; required: number};

/**
 * When you fetch several records, you will get "completeness" field
 * When you fetch unique records, you will get "created_at", "updated_at" and "permission" fields
 */
export type ReferenceEntityRecord = {
  code: RecordCode;
  identifier: RecordIdentifier;
  image: {
    extension: string;
    filePath: string;
    mimeType: string;
    originalFilename: string;
    size: number;
  } | null;
  labels: LabelCollection;
  reference_entity_identifier: ReferenceEntityIdentifierOrCode;
  values: {
    [key: string]: {
      attribute: string;
      channel: ChannelCode | null;
      data: any;
      locale: LocaleCode | null;
    };
  };
  completeness?: RecordCompleteness;
  created_at?: string;
  updated_at?: string;
  permission?: {[key: string]: boolean};
};
