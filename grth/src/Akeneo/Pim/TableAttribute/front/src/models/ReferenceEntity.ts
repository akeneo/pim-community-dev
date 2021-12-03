import {LabelCollection} from '@akeneo-pim-community/shared';

export type ReferenceEntityIdentifier = string;

export type ReferenceEntity = {
  identifier: ReferenceEntityIdentifier;
  image: {
    filePath: string;
    originalFilename: string;
  };
  labels: LabelCollection;
};
