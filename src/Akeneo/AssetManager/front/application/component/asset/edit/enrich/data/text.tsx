import React from 'react';
import {isTextData, textDataStringValue, textDataFromString} from 'akeneoassetmanager/domain/model/asset/data/text';
import {isTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {TextAreaInput, TextInput} from 'akeneo-design-system';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import {ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';

const View = ({id, value, invalid, onChange, onSubmit, canEditData}: ViewGeneratorProps) => {
  if (!isTextData(value.data) || !isTextAttribute(value.attribute)) {
    return null;
  }

  const onValueChange = (text: string) => {
    const newValue = setValueData(value, textDataFromString(text));

    onChange(newValue);
  };

  if (id === undefined) {
    id = `pim_asset_manager.asset.enrich.${value.attribute.code}`;
  }

  return (
    <>
      {value.attribute.is_textarea ? (
        <TextAreaInput
          id={id}
          value={textDataStringValue(value.data)}
          onChange={onValueChange}
          readOnly={!canEditData}
          isRichText={value.attribute.is_rich_text_editor}
        />
      ) : (
        <TextInput
          id={id}
          autoComplete="off"
          readOnly={!canEditData}
          value={textDataStringValue(value.data)}
          invalid={invalid}
          onChange={onValueChange}
          onSubmit={onSubmit}
          disabled={!canEditData}
        />
      )}
    </>
  );
};

export const view = View;
