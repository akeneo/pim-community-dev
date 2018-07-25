import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import {NormalizedEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';

interface FormProps {
  locale: string;
  data: NormalizedEnrichedEntity;
  errors: ValidationError[];
  onLabelUpdated: (value: string, locale: string) => void;
  onPressEnter: () => void;
}

export default class EditForm extends React.Component<FormProps> {
  private labelInput: HTMLInputElement;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  updateLabel = (event: any) => {
    this.props.onLabelUpdated(event.target.value, this.props.locale);
  };

  keyDown = (event: any) => {
    if ('Enter' === event.key) {
      this.props.onPressEnter();
    }
  };

  render() {
    return (
      <div>
        <div className="AknFieldContainer" data-code="identifier">
          <div className="AknFieldContainer-header">
            <label
              title="{__('pim_enriched_entity.enriched_entity.properties.identifier')}"
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
              className="AknTextField AknTextField--withDashedBottomBorder AknTextField--disabled"
              value={this.props.data.identifier}
              readOnly
            />
          </div>
          {getErrorsView(this.props.errors, 'identifier')}
        </div>
        <div className="AknFieldContainer" data-code="label">
          <div className="AknFieldContainer-header">
            <label
              title="{__('pim_enriched_entity.enriched_entity.properties.label')}"
              className="AknFieldContainer-label"
              htmlFor="pim_enriched_entity.enriched_entity.properties.label"
            >
              {__('pim_enriched_entity.enriched_entity.create.input.label')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              name="label"
              id="pim_enriched_entity.enriched_entity.properties.label"
              className="AknTextField AknTextField--withBottomBorder"
              value={
                undefined === this.props.data.labels[this.props.locale] ? '' : this.props.data.labels[this.props.locale]
              }
              onChange={this.updateLabel}
              onKeyDown={this.keyDown}
              ref={(input: HTMLInputElement) => {
                this.labelInput = input;
              }}
            />
            <Flag locale={this.props.locale} displayLanguage={false} />
          </div>
          {getErrorsView(this.props.errors, 'labels')}
        </div>
      </div>
    );
  }
}
