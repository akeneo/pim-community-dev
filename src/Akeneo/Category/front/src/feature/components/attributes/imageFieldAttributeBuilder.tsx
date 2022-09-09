import {Attribute} from '../../models';
import React from 'react';
import {Field, MediaFileInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeProps} from './types';
import {getLabelFromAttribute, isImageAttributeInputValue} from './templateAttributesFactory';

export class ImageFieldAttributeBuilder implements AttributeFieldBuilder<AttributeInputValue> {
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
