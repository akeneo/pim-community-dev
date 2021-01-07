import * as React from 'react';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {
  isTextData,
  areTextDataEqual,
  textDataStringValue,
  textDataFromString,
} from 'akeneoassetmanager/domain/model/asset/data/text';
import {isTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import RichTextEditor from 'akeneoassetmanager/application/component/app/rich-text-editor';
import {Key} from 'akeneo-design-system';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';

const View = ({
  value,
  onChange,
  onSubmit,
  canEditData,
}: {
  value: EditionValue;
  onChange: (value: EditionValue) => void;
  onSubmit: () => void;
  canEditData: boolean;
}) => {
  if (!isTextData(value.data) || !isTextAttribute(value.attribute)) {
    return null;
  }

  const onValueChange = (text: string) => {
    const newData = textDataFromString(text);
    if (areTextDataEqual(newData, value.data)) {
      return;
    }

    const newValue = setValueData(value, newData);

    onChange(newValue);
  };

  return (
    <React.Fragment>
      {value.attribute.is_textarea ? (
        value.attribute.is_rich_text_editor ? (
          <RichTextEditor value={textDataStringValue(value.data)} onChange={onValueChange} readOnly={!canEditData} />
        ) : (
          <textarea
            id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
            className={`AknTextareaField AknTextareaField--light
            ${value.attribute.value_per_locale ? 'AknTextareaField--localizable' : ''}
            ${!canEditData ? 'AknTextField--disabled' : ''}`}
            value={textDataStringValue(value.data)}
            onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
              onValueChange(event.currentTarget.value);
            }}
            readOnly={!canEditData}
          />
        )
      ) : (
        <input
          id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
          autoComplete="off"
          className={`AknTextField AknTextField--narrow AknTextField--light
          ${value.attribute.value_per_locale ? 'AknTextField--localizable' : ''}
          ${!canEditData ? 'AknTextField--disabled' : ''}`}
          value={textDataStringValue(value.data)}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            onValueChange(event.currentTarget.value);
          }}
          onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
            if (Key.Enter === event.key) onSubmit();
          }}
          disabled={!canEditData}
          readOnly={!canEditData}
        />
      )}
    </React.Fragment>
  );
};

export const view = View;
