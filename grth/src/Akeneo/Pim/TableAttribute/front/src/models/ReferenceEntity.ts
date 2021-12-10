import {LabelCollection} from '@akeneo-pim-community/shared';

export type ReferenceEntityIdentifierOrCode = string;

export type ReferenceEntity = {
  identifier: ReferenceEntityIdentifierOrCode;
  image: {
    filePath: string;
    originalFilename: string;
  };
  labels: LabelCollection;
};
