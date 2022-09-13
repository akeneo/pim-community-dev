import React from 'react';
import {Field, MediaFileInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeFieldProps, isImageAttributeInputValue} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import {memoize} from 'lodash/fp';
import {useTranslate} from '@akeneo-pim-community/shared';

const unMemoizedBuildImageFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    const translate = useTranslate();

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
          placeholder={translate('pim_common.media_upload')}
          uploadingLabel={translate('pim_common.media_uploading')}
          uploadErrorLabel={translate('pim_common.media_upload_error')}
          clearTitle={translate('pim_common.clear_value')}
          thumbnailUrl={null}
          uploader={dumbUploader}
        />
      </Field>
    );
  };

  Component.displayName = 'ImageFieldAttribute';

  return Component;
};

export const buildImageFieldAttribute = memoize(unMemoizedBuildImageFieldAttribute);
