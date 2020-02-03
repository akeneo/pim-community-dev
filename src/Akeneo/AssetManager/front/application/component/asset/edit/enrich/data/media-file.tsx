import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {File} from 'akeneoassetmanager/domain/model/file';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import FileComponent from 'akeneoassetmanager/application/component/app/file-component';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';

const View = ({
  value,
  channel,
  locale,
  onChange,
  canEditData,
}: {
  value: EditionValue;
  channel: ChannelReference;
  locale: LocaleReference;
  onChange: (value: EditionValue) => void;
  canEditData: boolean;
}) => {
  if (!isMediaFileData(value.data) || !isMediaFileAttribute(value.attribute)) {
    return null;
  }

  return (
    <FileComponent
      id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
      alt={__('pim_asset_manager.asset.value.file', {
        '{{ attribute_code }}': getLabelInCollection(
          value.attribute.labels,
          localeReferenceStringValue(value.locale),
          true,
          value.attribute.code
        ),
      })}
      context={{channel, locale}}
      image={value.data}
      attribute={value.attribute}
      wide={true}
      readOnly={!canEditData}
      onImageChange={(image: File) => {
        const newValue = setValueData(value, image);

        onChange(newValue);
      }}
    />
  );
};

export const view = View;
