import React from 'react';
import {Field, MediaFileInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeInputValue, AttributeFieldProps, isImageAttributeInputValue} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import {memoize} from 'lodash/fp';
import {useTranslate, useUploader} from '@akeneo-pim-community/shared';
import {usePreventClosing} from '../../hooks/usePreventClosing';

const unMemoizedBuildImageFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    const translate = useTranslate();
    const [uploader, isUploading] = useUploader('pim_enriched_category_rest_file_upload');
    usePreventClosing(() => isUploading, translate('pim_enrich.confirmation.discard_changes', {entity: 'category'}));

    if (!isImageAttributeInputValue(value)) {
      return null;
    }

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
          uploader={uploader}
        />
      </Field>
    );
  };

  Component.displayName = 'ImageFieldAttribute';

  return Component;
};

export const buildImageFieldAttribute = memoize(unMemoizedBuildImageFieldAttribute);
