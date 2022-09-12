import React from 'react';
import {Field, MediaFileInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeFieldProps, isImageAttributeInputValue} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import { memoize } from 'lodash/fp';

const unMemoizedBuildImageFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    if (!isImageAttributeInputValue(value)) {
      return null;
    }

    const dumbUploader = async (file: File, onProgress: (ratio: number) => void) => ({
      filePath: 'foo',
      originalFilename: 'bar',
    });

    return (
      <Field label={getLabelFromAttribute(attribute, locale)}>
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

  Component.displayName = 'ImageFieldAttribute';

  return Component;
};


export const buildImageFieldAttribute=memoize(unMemoizedBuildImageFieldAttribute);
