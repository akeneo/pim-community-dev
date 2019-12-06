import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {NormalizedValidationError as ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';

export enum LineStatus {
  WaitingForUpload = 'waiting_for_upload',
  UploadInProgress = 'upload_in_progress',
  Uploaded = 'uploaded',
  Valid = 'valid',
  Invalid = 'invalid',
  Created = 'created',
}

export type Thumbnail = string | null;

export default interface Line {
  id: string;
  thumbnail: Thumbnail;
  created: boolean;
  isSending: boolean;
  file: FileModel;
  filename: string;
  code: string;
  locale: LocaleReference;
  channel: ChannelReference;
  status: LineStatus;
  uploadProgress: number | null;
  errors: {
    back: ValidationError[];
  };
}
