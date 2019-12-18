import * as React from 'react';
import Value, {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import __ from 'akeneoassetmanager/tools/translator';
import {File} from 'akeneoassetmanager/domain/model/file';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import FileComponent from 'akeneoassetmanager/application/component/app/file-component';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';

const View = ({
  value,
  onChange,
  canEditData,
}: {
  value: Value;
  onChange: (value: Value) => void;
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
      image={value.data}
      attribute={value.attribute.identifier}
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
