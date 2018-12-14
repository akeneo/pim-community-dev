import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import {
  NormalizedReferenceEntity,
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import File from 'akeneoreferenceentity/domain/model/file';
import Image from 'akeneoreferenceentity/application/component/app/image';
import Key from 'akeneoreferenceentity/tools/key';

interface FormProps {
  locale: string;
  data: NormalizedReferenceEntity;
  errors: ValidationError[];
  canEditReferenceEntity: boolean;
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
    const referenceEntity = denormalizeReferenceEntity(this.props.data);

    return (
      <div>
        <div className="AknFieldContainer" data-code="identifier">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_reference_entity.reference_entity.properties.identifier')}
              className="AknFieldContainer-label"
              htmlFor="pim_reference_entity.reference_entity.properties.identifier"
            >
              {__('pim_reference_entity.reference_entity.properties.identifier')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              name="identifier"
              id="pim_reference_entity.reference_entity.properties.identifier"
              className="AknTextField AknTextField--light AknTextField--disabled"
              value={referenceEntity.getIdentifier().stringValue()}
              readOnly
            />
          </div>
          {getErrorsView(this.props.errors, 'identifier')}
        </div>
        <div className="AknFieldContainer" data-code="label">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_reference_entity.reference_entity.properties.label')}
              className="AknFieldContainer-label"
              htmlFor="pim_reference_entity.reference_entity.properties.label"
            >
              {__('pim_reference_entity.reference_entity.properties.label')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              name="label"
              id="pim_reference_entity.reference_entity.properties.label"
              className={`AknTextField AknTextField--light ${true === this.props.canEditReferenceEntity ? '' : 'AknTextField--disabled'}`}
              value={referenceEntity.getLabel(this.props.locale, false)}
              onChange={this.updateLabel}
              onKeyDown={this.keyDown}
              ref={this.labelInput}
              readOnly={!this.props.canEditReferenceEntity}
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
              title={__('pim_reference_entity.reference_entity.properties.image')}
              className="AknFieldContainer-label"
              htmlFor="pim_reference_entity.reference_entity.properties.image"
            >
              {__('pim_reference_entity.reference_entity.properties.image')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <Image
              alt={__('pim_reference_entity.reference_entity.img', {
                '{{ label }}': referenceEntity.getLabel(this.props.locale),
              })}
              image={referenceEntity.getImage()}
              wide={true}
              onImageChange={this.props.onImageUpdated}
              readOnly={!this.props.canEditReferenceEntity}
            />
          </div>
          {getErrorsView(this.props.errors, 'image')}
        </div>
      </div>
    );
  }
}
