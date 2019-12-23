import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';

export enum MediaPreviewType {
  Preview = 'preview',
  Thumbnail = 'thumbnail',
  ThumbnailSmall = 'thumbnail_small',
}

export type MediaPreview = {
  type: MediaPreviewType;
  attributeIdentifier: AttributeIdentifier;
  data: string;
};
