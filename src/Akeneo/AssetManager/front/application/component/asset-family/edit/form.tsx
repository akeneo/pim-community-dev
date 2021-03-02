import React from 'react';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import {Key, useShortcut} from 'akeneo-design-system';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getLabel} from 'pimui/js/i18n';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useFocus} from 'akeneoassetmanager/application/hooks/input';

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
  const labelInputRef = useShortcut(Key.Enter, onSubmit, useFocus()[0]);

  return (
    <div>
      <div className="AknFieldContainer" data-code="identifier">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            title={translate('pim_asset_manager.asset_family.properties.identifier')}
            className="AknFieldContainer-label"
            htmlFor="pim_asset_manager.asset_family.properties.identifier"
          >
            {translate('pim_asset_manager.asset_family.properties.identifier')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            name="identifier"
            id="pim_asset_manager.asset_family.properties.identifier"
            className="AknTextField AknTextField--light AknTextField--disabled"
            value={assetFamilyIdentifierStringValue(assetFamily.identifier)}
            readOnly
          />
        </div>
        {getErrorsView(errors, 'identifier')}
      </div>
      <div className="AknFieldContainer" data-code="label">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            title={translate('pim_asset_manager.asset_family.properties.label')}
            className="AknFieldContainer-label"
            htmlFor="pim_asset_manager.asset_family.properties.label"
          >
            {translate('pim_asset_manager.asset_family.properties.label')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            name="label"
            id="pim_asset_manager.asset_family.properties.label"
            className={`AknTextField AknTextField--light ${true === canEditLabel ? '' : 'AknTextField--disabled'}`}
            value={getAssetFamilyLabel(assetFamily, locale, false)}
            onChange={event => onLabelUpdated(event.target.value, locale)}
            ref={labelInputRef}
            readOnly={!canEditLabel}
          />
          <Flag
            locale={createLocaleFromCode(locale)}
            displayLanguage={false}
            className="AknFieldContainer-inputSides"
          />
        </div>
        {getErrorsView(errors, 'labels')}
      </div>
      <div className="AknFieldContainer" data-code="attributeAsMainMedia">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            title={translate('pim_asset_manager.asset_family.properties.attribute_as_main_media')}
            className="AknFieldContainer-label"
            htmlFor="pim_asset_manager.asset_family.properties.attribute_as_main_media"
          >
            {translate('pim_asset_manager.asset_family.properties.attribute_as_main_media')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          {null !== attributes && (
            <Select2
              id="pim_asset_manager.attribute.edit.input.allowed_extensions"
              name="allowed_extensions"
              data={attributes
                .filter((attribute: NormalizedAttribute) =>
                  [MEDIA_LINK_ATTRIBUTE_TYPE, MEDIA_FILE_ATTRIBUTE_TYPE].includes(attribute.type)
                )
                .reduce((result: {[key: string]: string}, current: NormalizedAttribute) => {
                  return {...result, [current.identifier]: getLabel(current.labels, locale, current.code)};
                }, {})}
              value={assetFamily.attributeAsMainMedia}
              multiple={false}
              readOnly={!canEditAttributeAsMainMedia}
              configuration={{
                allowClear: false,
              }}
              onChange={(attributeAsMainMedia: string) => {
                onAttributeAsMainMediaUpdated(attributeAsMainMedia);
              }}
            />
          )}
        </div>
        {getErrorsView(errors, 'attributeAsMainMedia')}
      </div>
    </div>
  );
};

export {EditForm};
