import React from 'react';
import {isTextData, textDataStringValue, textDataFromString} from 'akeneoassetmanager/domain/model/asset/data/text';
import {isTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import RichTextEditor from 'akeneoassetmanager/application/component/app/rich-text-editor';
import {Key, TextInput} from 'akeneo-design-system';
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
        value.attribute.is_rich_text_editor ? (
          <RichTextEditor value={textDataStringValue(value.data)} onChange={onValueChange} readOnly={!canEditData} />
        ) : (
          <textarea
            id={id}
            className={`AknTextareaField AknTextareaField--light
            ${value.attribute.value_per_locale ? 'AknTextareaField--localizable' : ''}
            ${!canEditData ? 'AknTextField--disabled' : ''}`}
            value={textDataStringValue(value.data)}
            onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => onValueChange(event.currentTarget.value)}
            readOnly={!canEditData}
          />
        )
      ) : (
        <TextInput
          id={id}
          autoComplete="off"
          readOnly={!canEditData}
          value={textDataStringValue(value.data)}
          invalid={invalid}
          onChange={onValueChange}
          onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
            if (Key.Enter === event.key) onSubmit?.();
          }}
          disabled={!canEditData}
        />
      )}
    </>
  );
};

export const view = View;
