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

export const emptyMediaPreview = (): MediaPreview => ({
  type: MediaPreviewType.Thumbnail,
  attributeIdentifier: 'UNKNOWN',
  data: '',
});
