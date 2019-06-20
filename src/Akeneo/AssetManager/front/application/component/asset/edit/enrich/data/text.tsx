import * as React from 'react';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import TextData, {create} from 'akeneoassetmanager/domain/model/asset/data/text';
import {ConcreteTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import RichTextEditor from 'akeneoassetmanager/application/component/app/rich-text-editor';
import Key from 'akeneoassetmanager/tools/key';

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
  if (!(value.data instanceof TextData && value.attribute instanceof ConcreteTextAttribute)) {
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

  return (
    <React.Fragment>
      {value.attribute.isTextarea.booleanValue() ? (
        value.attribute.isRichTextEditor.booleanValue() ? (
          <RichTextEditor value={value.data.stringValue()} onChange={onValueChange} readOnly={!canEditData} />
        ) : (
          <textarea
            id={`pim_asset_manager.asset.enrich.${value.attribute.getCode().stringValue()}`}
            className={`AknTextareaField AknTextareaField--light
            ${value.attribute.valuePerLocale ? 'AknTextareaField--localizable' : ''}
            ${!canEditData ? 'AknTextField--disabled' : ''}`}
            value={value.data.stringValue()}
            onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
              onValueChange(event.currentTarget.value);
            }}
            readOnly={!canEditData}
          />
        )
      ) : (
        <input
          id={`pim_asset_manager.asset.enrich.${value.attribute.getCode().stringValue()}`}
          autoComplete="off"
          className={`AknTextField AknTextField--narrow AknTextField--light
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
      )}
    </React.Fragment>
  );
};

export const view = View;
