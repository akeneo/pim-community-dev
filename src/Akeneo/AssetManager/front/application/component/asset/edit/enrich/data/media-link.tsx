import * as React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import Key from 'akeneoassetmanager/tools/key';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {
  isMediaLinkData,
  mediaLinkDataFromString,
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
  Container,
  Thumbnail,
  Actions,
  ThumbnailPlaceholder,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';

const MediaLinkInput = styled.input`
  ::placeholder {
    color: ${props => props.theme.color.grey120};
  }

  :disabled,
  :read-only {
    cursor: not-allowed;
    border: none;
    color: ${props => props.theme.color.grey100};

    ::placeholder {
      color: ${props => props.theme.color.grey100};
    }
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

  const mediaPreviewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });

  const [regenerate, doRegenerate] = useRegenerate(mediaPreviewUrl);

  const label = getLabelInCollection(
    value.attribute.labels,
    localeReferenceStringValue(locale),
    true,
    value.attribute.code
  );

  return (
    <Container>
      {regenerate ? (
        <ThumbnailPlaceholder className="AknLoadingPlaceHolder" />
      ) : (
        <Thumbnail
          onClick={doRegenerate}
          src={mediaPreviewUrl}
          alt={__('pim_asset_manager.attribute.media_type_preview')}
        />
      )}
      <MediaLinkInput
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        autoComplete="off"
        className={`AknTextField AknTextField--light ${
          value.attribute.value_per_locale ? 'AknTextField--localizable' : ''
        }`}
        value={mediaLinkDataStringValue(value.data)}
        onChange={e => onChange(setValueData(value, mediaLinkDataFromString(e.currentTarget.value)))}
        onKeyDown={e => Key.Enter === e.key && onSubmit()}
        disabled={!canEditData}
        readOnly={!canEditData}
        placeholder={__(`pim_asset_manager.attribute.media_link.${canEditData ? 'placeholder' : 'read_only'}`)}
      />
      {!isValueEmpty(value) && (
        <Actions>
          <CopyUrlAction color={akeneoTheme.color.grey100} size={20} data={value.data} attribute={value.attribute} />
          <DownloadAction color={akeneoTheme.color.grey100} size={20} data={value.data} attribute={value.attribute} />
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
