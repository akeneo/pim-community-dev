import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import {
  NormalizedEnrichedEntity,
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoenrichedentity/domain/model/locale';
import File from 'akeneoenrichedentity/domain/model/file';
import Image from 'akeneoenrichedentity/application/component/app/image';

interface FormProps {
  locale: string;
  data: NormalizedEnrichedEntity;
  errors: ValidationError[];
  onLabelUpdated: (value: string, locale: string) => void;
  onImageUpdated: (image: File) => void;
  onPressEnter: () => void;
}

export default class EditForm extends React.Component<FormProps> {
  private labelInput: HTMLInputElement;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  updateLabel = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.onLabelUpdated(event.target.value, this.props.locale);
  };

  keyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if ('Enter' === event.key) {
      this.props.onPressEnter();
    }
  };

  render() {
    const enrichedEntity = denormalizeEnrichedEntity(this.props.data);

    return (
      <div>
        <div className="AknFieldContainer" data-code="identifier">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_enriched_entity.enriched_entity.properties.identifier')}
              className="AknFieldContainer-label"
              htmlFor="pim_enriched_entity.enriched_entity.properties.identifier"
            >
              {__('pim_enriched_entity.enriched_entity.properties.identifier')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              name="identifier"
              id="pim_enriched_entity.enriched_entity.properties.identifier"
              className="AknTextField AknTextField--light AknTextField--disabled"
              value={enrichedEntity.getIdentifier().stringValue()}
              readOnly
            />
          </div>
          {getErrorsView(this.props.errors, 'identifier')}
        </div>
        <div className="AknFieldContainer" data-code="label">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              title={__('pim_enriched_entity.enriched_entity.properties.label')}
              className="AknFieldContainer-label"
              htmlFor="pim_enriched_entity.enriched_entity.properties.label"
            >
              {__('pim_enriched_entity.enriched_entity.properties.label')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              name="label"
              id="pim_enriched_entity.enriched_entity.properties.label"
              className="AknTextField AknTextField--light"
              value={enrichedEntity.getLabel(this.props.locale, false)}
              onChange={this.updateLabel}
              onKeyDown={this.keyDown}
              ref={(input: HTMLInputElement) => {
                this.labelInput = input;
              }}
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
              title={__('pim_enriched_entity.enriched_entity.properties.image')}
              className="AknFieldContainer-label"
              htmlFor="pim_enriched_entity.enriched_entity.properties.image"
            >
              {__('pim_enriched_entity.enriched_entity.properties.image')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <Image
              alt={__('pim_enriched_entity.enriched_entity.img', {
                '{{ label }}': enrichedEntity.getLabel(this.props.locale),
              })}
              image={enrichedEntity.getImage()}
              wide={true}
              onImageChange={this.props.onImageUpdated}
            />
          </div>
          {getErrorsView(this.props.errors, 'image')}
        </div>
      </div>
    );
  }
}
