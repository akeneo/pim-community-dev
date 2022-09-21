export enum MediaPreviewType {
  Preview = 'preview',
  Thumbnail = 'thumbnail',
  ThumbnailSmall = 'thumbnail_small',
}

export type MediaPreview = {
  type: MediaPreviewType;
  attributeCode: string;
  data: string;
};
