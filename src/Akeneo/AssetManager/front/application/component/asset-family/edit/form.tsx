import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import {Key} from 'akeneo-design-system';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
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

export default class EditForm extends React.Component<FormProps> {
  private labelInput: React.RefObject<HTMLInputElement>;

  constructor(props: FormProps) {
    super(props);

    this.labelInput = React.createRef();
  }

  componentDidMount() {
    if (this.labelInput.current) {
      this.labelInput.current.focus();
    }
  }

  updateLabel = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.onLabelUpdated(event.target.value, this.props.locale);
  };

  keyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.onSubmit();
  };

  render() {
    const assetFamily = this.props.data;
    const canEditLabel = this.props.rights.assetFamily.edit && this.props.rights.locale.edit;
    const canEditAttributeAsMainMedia = this.props.rights.assetFamily.edit;

    return (
      <div>
        <div className="AknFieldContainer" data-code="identifier">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_asset_manager.asset_family.properties.identifier')}
              className="AknFieldContainer-label"
              htmlFor="pim_asset_manager.asset_family.properties.identifier"
            >
              {__('pim_asset_manager.asset_family.properties.identifier')}
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
          {getErrorsView(this.props.errors, 'identifier')}
        </div>
        <div className="AknFieldContainer" data-code="label">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_asset_manager.asset_family.properties.label')}
              className="AknFieldContainer-label"
              htmlFor="pim_asset_manager.asset_family.properties.label"
            >
              {__('pim_asset_manager.asset_family.properties.label')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              autoComplete="off"
              name="label"
              id="pim_asset_manager.asset_family.properties.label"
              className={`AknTextField AknTextField--light ${true === canEditLabel ? '' : 'AknTextField--disabled'}`}
              value={getAssetFamilyLabel(assetFamily, this.props.locale, false)}
              onChange={this.updateLabel}
              onKeyDown={this.keyDown}
              ref={this.labelInput}
              readOnly={!canEditLabel}
            />
            <Flag
              locale={createLocaleFromCode(this.props.locale)}
              displayLanguage={false}
              className="AknFieldContainer-inputSides"
            />
          </div>
          {getErrorsView(this.props.errors, 'labels')}
        </div>
        <div className="AknFieldContainer" data-code="attributeAsMainMedia">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_asset_manager.asset_family.properties.attribute_as_main_media')}
              className="AknFieldContainer-label"
              htmlFor="pim_asset_manager.asset_family.properties.attribute_as_main_media"
            >
              {__('pim_asset_manager.asset_family.properties.attribute_as_main_media')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            {null !== this.props.attributes && (
              <Select2
                id="pim_asset_manager.attribute.edit.input.allowed_extensions"
                name="allowed_extensions"
                data={this.props.attributes
                  .filter((attribute: NormalizedAttribute) =>
                    [MEDIA_LINK_ATTRIBUTE_TYPE, MEDIA_FILE_ATTRIBUTE_TYPE].includes(attribute.type)
                  )
                  .reduce((result: {[key: string]: string}, current: NormalizedAttribute) => {
                    return {...result, [current.identifier]: getLabel(current.labels, this.props.locale, current.code)};
                  }, {})}
                value={assetFamily.attributeAsMainMedia}
                multiple={false}
                readOnly={!canEditAttributeAsMainMedia}
                configuration={{
                  allowClear: false,
                }}
                onChange={(attributeAsMainMedia: string) => {
                  this.props.onAttributeAsMainMediaUpdated(attributeAsMainMedia);
                }}
              />
            )}
          </div>
          {getErrorsView(this.props.errors, 'attributeAsMainMedia')}
        </div>
      </div>
    );
  }
}
