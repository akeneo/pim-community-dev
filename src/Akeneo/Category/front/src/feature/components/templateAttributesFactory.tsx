import React from 'react';
import {Attribute} from '../models';
import {Field, FileInfo, MediaFileInput, TextAreaInput, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';

type TextAttributeInputValue = string;

type ImageAttributeInputValue = FileInfo | null;

export type AttributeInputValue = TextAttributeInputValue | ImageAttributeInputValue;

export const isImageAttributeInputValue = (value: AttributeInputValue): value is ImageAttributeInputValue =>
  value !== null && value.hasOwnProperty('originalFilename') && value.hasOwnProperty('filePath');

export const getLabelFromAttribute = (attr: Attribute, locale: string): string =>
  attr.labels[locale] ?? '[' + attr.code + ']';

interface AttributeFieldBuilder<ValueType extends AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<ValueType>>;
}

declare type AttributeProps<ValueType> = {
  locale: string;
  value: ValueType;
  onChange: (value: ValueType) => void;
};

class TextFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> {
    return ({locale, value, onChange}: AttributeProps<AttributeInputValue>) => {
      if (typeof value !== 'string') {
        return null;
      }

      return (
        <Field label={getLabelFromAttribute(attr, locale)} locale={locale}>
          <TextInput name={attr.code} value={value} onChange={onChange} />
        </Field>
      );
    };
  }
}

class RichTextFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> {
    const Field960 = styled(Field)`
      max-width: 960px;
    `;

    return ({locale, value, onChange}: AttributeProps<AttributeInputValue>) => {
      if (typeof value !== 'string') {
        return null;
      }

      return (
        <Field960 label={getLabelFromAttribute(attr, locale)} locale={locale}>
          <TextAreaInput isRichText name={attr.code} value={value} onChange={onChange} />
        </Field960>
      );
    };
  }
}

class TextAreaFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> {
    return ({locale, value, onChange}: AttributeProps<AttributeInputValue>) => {
      if (typeof value !== 'string') {
        return null;
      }

      return (
        <Field label={getLabelFromAttribute(attr, locale)} locale={locale}>
          <TextAreaInput isRichText name={attr.code} value={value} onChange={onChange} />
        </Field>
      );
    };
  }
}

class ImageFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
  buildAttributeField(attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> {
    return ({locale, value, onChange}: AttributeProps<AttributeInputValue>) => {
      if (!isImageAttributeInputValue(value)) {
        return null;
      }

      const dumbUploader = async (file: File, onProgress: (ratio: number) => void) => ({
        filePath: 'foo',
        originalFilename: 'bar',
      });

      return (
        <Field label={getLabelFromAttribute(attr, locale)}>
          <MediaFileInput
            value={value}
            onChange={onChange}
            placeholder="Drag and drop to upload or click here"
            uploadingLabel="Uploading..."
            uploadErrorLabel="An error occurred during upload"
            clearTitle="Clear"
            thumbnailUrl={null}
            uploader={dumbUploader}
          />
        </Field>
      );
    };
  }
}

const attributeFieldBuilders: {[attributeType: string]: AttributeFieldBuilder<AttributeInputValue>} = {
  text: new TextFieldAttributeBuilder(),
  richtext: new RichTextFieldAttributeBuilder(),
  textarea: new TextAreaFieldAttributeBuilder(),
  image: new ImageFieldAttributeBuilder(),
};

const attributeFieldFactory = (attr: Attribute): React.FC<AttributeProps<AttributeInputValue>> | null => {
  const builder = attributeFieldBuilders[attr.type];
  if (!builder) {
    return null;
  }
  return builder.buildAttributeField(attr);
};

export {attributeFieldFactory};
