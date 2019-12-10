import * as React from 'react';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import FileData, {create} from 'akeneoassetmanager/domain/model/asset/data/file';
import __ from 'akeneoassetmanager/tools/translator';
import {File} from 'akeneoassetmanager/domain/model/file';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import FileComponent from "akeneoassetmanager/application/component/app/file-component";

const View = ({
  value,
  onChange,
  canEditData,
}: {
  value: Value;
  onChange: (value: Value) => void;
  canEditData: boolean;
}) => {
  if (!(value.data instanceof FileData)) {
    return null;
  }

  return (
    <FileComponent
      id={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
      alt={__('pim_asset_manager.asset.value.file', {
        '{{ attribute_code }}': value.attribute.getLabel(localeReferenceStringValue(value.locale)),
      })}
      image={value.data.getFile()}
      attribute={value.attribute.identifier}
      wide={true}
      readOnly={!canEditData}
      onImageChange={(image: File) => {
        const newData = create(image);
        const newValue = value.setData(newData);

        onChange(newValue);
      }}
    />
  );
};

export const view = View;
