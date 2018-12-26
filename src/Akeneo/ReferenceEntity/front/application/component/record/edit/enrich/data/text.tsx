import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import TextData, {create} from 'akeneoreferenceentity/domain/model/record/data/text';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import {ConcreteTextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import RichTextEditor from 'akeneoreferenceentity/application/component/app/rich-text-editor';
import Key from 'akeneoreferenceentity/tools/key';

const View = ({
  value,
  onChange,
  onSubmit,
  rights,
}: {
  value: Value;
  onChange: (value: Value) => void;
  onSubmit: () => void;
  rights: {
    record: {
      edit: boolean;
      delete: boolean;
    };
  };
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
          <RichTextEditor value={value.data.stringValue()} onChange={onValueChange} readOnly={!rights.record.edit} />
        ) : (
          <textarea
            id={`pim_reference_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
            className={`AknTextareaField AknTextareaField--light
            ${value.attribute.valuePerLocale ? 'AknTextareaField--localizable' : ''}
            ${!rights.record.edit ? 'AknTextField--disabled' : ''}
            `}
            value={value.data.stringValue()}
            onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
              onValueChange(event.currentTarget.value);
            }}
            readOnly={!rights.record.edit}
          />
        )
      ) : (
        <input
          id={`pim_reference_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
          className={`AknTextField AknTextField--narrow AknTextField--light
            ${value.attribute.valuePerLocale ? 'AknTextField--localizable' : ''}
            ${!rights.record.edit ? 'AknTextField--disabled' : ''}
            `}
          value={value.data.stringValue()}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            onValueChange(event.currentTarget.value);
          }}
          onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
            if (Key.Enter === event.key) onSubmit();
          }}
          disabled={!rights.record.edit}
          readOnly={!rights.record.edit}
        />
      )}
      {value.attribute.valuePerLocale ? (
        <Flag locale={createLocaleFromCode(value.locale.stringValue())} displayLanguage={false} />
      ) : null}
    </React.Fragment>
  );
};

export const view = View;
