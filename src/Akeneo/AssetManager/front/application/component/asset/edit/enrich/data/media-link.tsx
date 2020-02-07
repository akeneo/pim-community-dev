import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import Key from 'akeneoassetmanager/tools/key';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import styled from 'styled-components';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {
  isMediaLinkData,
  mediaLinkDataFromString,
  areMediaLinkDataEqual,
  mediaLinkDataStringValue,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {setValueData, isValueEmpty} from 'akeneoassetmanager/domain/model/asset/value';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {Fullscreen} from 'akeneoassetmanager/application/component/app/icon/fullscreen';
import {
  Action,
  DownloadAction,
  CopyUrlAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media-actions';

const Container = styled.div`
  align-items: center;
  border-radius: 2px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  display: flex;
  flex: 1;
  justify-content: center;
  max-width: 460px;
  padding: 15px;
`;

const Thumbnail = styled.img`
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey60};
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  margin-right: 15px;
  object-fit: cover;
`;

const Actions = styled.div`
  display: flex;

  > ${Action} {
    margin-left: 15px;
  }
`;

const View = ({
  value,
  locale,
  onChange,
  onSubmit,
  canEditData,
}: {
  value: EditionValue;
  locale: LocaleReference;
  onChange: (value: EditionValue) => void;
  onSubmit: () => void;
  canEditData: boolean;
}) => {
  if (!isMediaLinkData(value.data) || !isMediaLinkAttribute(value.attribute)) {
    return null;
  }

  const onValueChange = (text: string) => {
    const newData = mediaLinkDataFromString(text);
    if (areMediaLinkDataEqual(newData, value.data)) {
      return;
    }

    const newValue = setValueData(value, newData);

    onChange(newValue);
  };

  const mediaPreviewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });

  const label = getLabelInCollection(
    value.attribute.labels,
    localeReferenceStringValue(locale),
    true,
    value.attribute.code
  );

  return (
    <Container>
      <Thumbnail src={mediaPreviewUrl} alt={__('pim_asset_manager.attribute.media_type_preview')} />
      <input
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        autoComplete="off"
        className={`AknTextField AknTextField--light
        ${value.attribute.value_per_locale ? 'AknTextField--localizable' : ''}
        ${!canEditData ? 'AknTextField--disabled' : ''}`}
        value={mediaLinkDataStringValue(value.data)}
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          onValueChange(event.currentTarget.value);
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (Key.Enter === event.key) onSubmit();
        }}
        disabled={!canEditData}
        readOnly={!canEditData}
      />
      {!isValueEmpty(value) && (
        <Actions>
          <DownloadAction color={akeneoTheme.color.grey100} size={20} data={value.data} attribute={value.attribute} />
          <CopyUrlAction color={akeneoTheme.color.grey100} size={20} data={value.data} attribute={value.attribute} />
          <FullscreenPreview anchor={Action} label={label} data={value.data} attribute={value.attribute}>
            <Fullscreen
              title={__('pim_asset_manager.asset.button.fullscreen')}
              color={akeneoTheme.color.grey100}
              size={20}
            />
          </FullscreenPreview>
        </Actions>
      )}
    </Container>
  );
};

export const view = View;
