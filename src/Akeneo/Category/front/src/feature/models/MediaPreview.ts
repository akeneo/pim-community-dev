import {Attribute} from './Attribute';

export enum MediaPreviewType {
  Preview = 'preview',
  Thumbnail = 'thumbnail',
  ThumbnailSmall = 'thumbnail_small',
}

export type MediaPreview = {
  type: MediaPreviewType;
  attributeIdentifier: string;
  data: string;
};
