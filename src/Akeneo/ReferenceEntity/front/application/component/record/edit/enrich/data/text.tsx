import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import TextData, {create} from 'akeneoreferenceentity/domain/model/record/data/text';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import {ConcreteTextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import RichTextEditor from 'akeneoreferenceentity/application/component/app/rich-text-editor';

const View = ({value, onChange, onSubmit}: {value: Value; onChange: (value: Value) => void; onSubmit: () => void}) => {
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
          <RichTextEditor value={value.data.stringValue()} onChange={onValueChange} />
        ) : (
          <textarea
            className={`AknTextareaField AknTextareaField--light AknTextareaField--narrow ${
              value.attribute.valuePerLocale ? 'AknTextareaField--localizable' : ''
            }`}
            value={value.data.stringValue()}
            onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
              onValueChange(event.currentTarget.value);
            }}
          />
        )
      ) : (
        <input
          id={value.attribute.identifier.stringValue()}
          className={`AknTextField AknTextField--narrow AknTextField--light ${
            value.attribute.valuePerLocale ? 'AknTextField--localizable' : ''
          }`}
          value={value.data.stringValue()}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            onValueChange(event.currentTarget.value);
          }}
          onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
            if ('Enter' === event.key) {
              onSubmit();
            }
          }}
        />
      )}
      {value.attribute.valuePerLocale ? (
        <Flag locale={createLocaleFromCode(value.locale.stringValue())} displayLanguage={false} />
      ) : null}
    </React.Fragment>
  );
};

export const view = View;
