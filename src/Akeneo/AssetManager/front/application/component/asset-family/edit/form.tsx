import React, {useRef} from 'react';
import {Field, SelectInput, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath, TextField, ValidationError} from '@akeneo-pim-community/shared';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getLabel} from 'pimui/js/i18n';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

interface FormProps {
  locale: string;
  data: AssetFamily;
  errors: ValidationError[];
  attributes: NormalizedAttribute[] | null;
  rights: {
    locale: {
      edit: boolean;
    };
    assetFamily: {
      edit: boolean;
      delete: boolean;
    };
  };
  onLabelUpdated: (value: string, locale: string) => void;
  onAttributeAsMainMediaUpdated: (attributeAsMainMedia: AttributeIdentifier) => void;
  onSubmit: () => void;
}

const EditForm = ({
  data,
  attributes,
  rights,
  errors,
  locale,
  onLabelUpdated,
  onSubmit,
  onAttributeAsMainMediaUpdated,
}: FormProps) => {
  const translate = useTranslate();
  const assetFamily = data;
  const canEditLabel = rights.assetFamily.edit && rights.locale.edit;
  const canEditAttributeAsMainMedia = rights.assetFamily.edit;
  const labelInputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(labelInputRef);

  return (
    <>
      <TextField
        value={assetFamilyIdentifierStringValue(assetFamily.identifier)}
        readOnly={true}
        label={translate('pim_asset_manager.asset_family.properties.identifier')}
        errors={getErrorsForPath(errors, 'identifier')}
      />
      <TextField
        label={translate('pim_asset_manager.asset_family.properties.label')}
        locale={locale}
        value={getAssetFamilyLabel(assetFamily, locale, false)}
        onChange={value => onLabelUpdated(value, locale)}
        ref={labelInputRef}
        onSubmit={onSubmit}
        readOnly={!canEditLabel}
        errors={getErrorsForPath(errors, 'labels')}
      />
      {null !== attributes && (
        <Field label={translate('pim_asset_manager.asset_family.properties.attribute_as_main_media')}>
          <SelectInput
            readOnly={!canEditAttributeAsMainMedia}
            emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
            clearable={false}
            value={assetFamily.attributeAsMainMedia}
            onChange={onAttributeAsMainMediaUpdated}
          >
            {attributes
              .filter(attribute => [MEDIA_LINK_ATTRIBUTE_TYPE, MEDIA_FILE_ATTRIBUTE_TYPE].includes(attribute.type))
              .map(attribute => (
                <SelectInput.Option key={attribute.identifier} value={attribute.identifier}>
                  {getLabel(attribute.labels, locale, attribute.code)}
                </SelectInput.Option>
              ))}
          </SelectInput>
          {getErrorsView(errors, 'attributeAsMainMedia')}
        </Field>
      )}
    </>
  );
};

export {EditForm};
