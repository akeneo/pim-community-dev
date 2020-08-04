import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';

export enum MediaPreviewType {
  Preview = 'preview',
  Thumbnail = 'thumbnail',
}

export type MediaPreview = {
  type: MediaPreviewType;
  attributeIdentifier: AttributeIdentifier;
  data: string;
};

export const emptyMediaPreview = (): MediaPreview => ({
  type: MediaPreviewType.Thumbnail,
  attributeIdentifier: 'UNKNOWN',
  data: '',
});
