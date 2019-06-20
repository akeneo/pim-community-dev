import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {NormalizedAssetFamily, denormalizeAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Flag from 'akeneoassetmanager/tools/component/flag';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import File from 'akeneoassetmanager/domain/model/file';
import Image from 'akeneoassetmanager/application/component/app/image';
import Key from 'akeneoassetmanager/tools/key';

interface FormProps {
  locale: string;
  data: NormalizedAssetFamily;
  errors: ValidationError[];
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
  onImageUpdated: (image: File) => void;
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
    const assetFamily = denormalizeAssetFamily(this.props.data);
    const canEditLabel = this.props.rights.assetFamily.edit && this.props.rights.locale.edit;
    const canEditImage = this.props.rights.assetFamily.edit;

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
              value={assetFamily.getIdentifier().stringValue()}
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
              value={assetFamily.getLabel(this.props.locale, false)}
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
        <div className="AknFieldContainer" data-code="image">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_asset_manager.asset_family.properties.image')}
              className="AknFieldContainer-label"
              htmlFor="pim_asset_manager.asset_family.properties.image"
            >
              {__('pim_asset_manager.asset_family.properties.image')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <Image
              alt={__('pim_asset_manager.asset_family.img', {
                '{{ label }}': assetFamily.getLabel(this.props.locale),
              })}
              image={assetFamily.getImage()}
              wide={true}
              onImageChange={this.props.onImageUpdated}
              readOnly={!canEditImage}
            />
          </div>
          {getErrorsView(this.props.errors, 'image')}
        </div>
      </div>
    );
  }
}
