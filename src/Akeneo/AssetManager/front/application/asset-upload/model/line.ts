import {ValidationError} from '@akeneo-pim-community/shared';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';

export enum LineStatus {
  WaitingForUpload = 'waiting_for_upload',
  UploadInProgress = 'upload_in_progress',
  Uploaded = 'uploaded',
  Valid = 'valid',
  Invalid = 'invalid',
  Created = 'created',
}

export interface LineErrorsByTarget {
  common: ValidationError[];
  code: ValidationError[];
  locale: ValidationError[];
  channel: ValidationError[];
}

export type Thumbnail = string | null;
export type LineIdentifier = string;

export default interface Line {
  id: LineIdentifier;
  thumbnail: Thumbnail;
  assetCreated: boolean;
  isAssetCreating: boolean;
  isFileUploading: boolean;
  isFileUploadFailed: boolean;
  file: FileModel;
  filename: string;
  code: string;
  locale: LocaleReference;
  channel: ChannelReference;
  uploadProgress: number | null;
  errors: {
    back: ValidationError[];
    front: ValidationError[];
  };
}
