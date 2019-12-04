import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';

export enum LineStatus {
  WaitingForUpload = "waiting_for_upload",
  UploadInProgress = "upload_in_progress",
  Uploaded = "uploaded",
  Ready = "ready",
  Invalid = "invalid",
  Incomplete = "incomplete",
}

export default interface Line {
  id: string;
  file: FileModel;
  filename: string;
  code: string;
  locale: LocaleReference;
  channel: ChannelReference;
  status: LineStatus;
  uploadProgress: number | null;
  validation: {
    back: ValidationError[];
  };
}
