import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import MediaLinkData, {create} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {ConcreteMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import Key from 'akeneoassetmanager/tools/key';
import {getMediaLinkPreviewUrl, getMediaLinkUrl, MediaPreviewTypes} from 'akeneoassetmanager/tools/media-url-generator';
import {copyToClipboard} from 'akeneoassetmanager/tools/media-url-generator';

const View = ({
  value,
  onChange,
  onSubmit,
  canEditData,
}: {
  value: Value;
  onChange: (value: Value) => void;
  onSubmit: () => void;
  canEditData: boolean;
}) => {
  if (!(value.data instanceof MediaLinkData && value.attribute instanceof ConcreteMediaLinkAttribute)) {
    return null;
  }

  const onValueChange = (text: string) => {
    const newData = create(text);
    if (newData.equals(value.data)) {
      return;
    }

    const newValue = value.setData(newData);

    onChange(newValue);
  };

  const mediaDownloadUrl = getMediaLinkUrl(value.data, value.attribute);
  const mediaPreviewUrl = getMediaLinkPreviewUrl(MediaPreviewTypes.ThumbnailSmall, value.data, value.attribute);

  return (
    <div className="AknMediaTypeField">
      <img
        src={mediaPreviewUrl}
        className="AknMediaTypeField-preview"
        alt={__('pim_asset_manager.attribute.media_type_preview')}
      />
      <input
        id={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
        autoComplete="off"
        className={`AknTextField AknTextField--light
        ${value.attribute.valuePerLocale ? 'AknTextField--localizable' : ''}
        ${!canEditData ? 'AknTextField--disabled' : ''}`}
        value={value.data.stringValue()}
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          onValueChange(event.currentTarget.value);
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (Key.Enter === event.key) onSubmit();
        }}
        disabled={!canEditData}
        readOnly={!canEditData}
      />
      <a
        href={mediaDownloadUrl}
        className="AknIconButton AknIconButton--light AknIconButton--download AknMediaTypeField-button"
        target="_blank"
      />
      <button
        className="AknIconButton AknIconButton--light AknIconButton--link AknMediaTypeField-button"
        onClick={() => {
          copyToClipboard(mediaDownloadUrl);
        }}
      />
    </div>
  );
};

export const view = View;
