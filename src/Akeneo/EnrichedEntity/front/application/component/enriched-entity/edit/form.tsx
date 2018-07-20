import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import {NormalizedEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

interface FormProps {
  locale: string;
  data: NormalizedEnrichedEntity;
  errors: ValidationError[];
  onLabelUpdated: (value: string, locale: string) => void
}

export default class EditForm extends React.Component<FormProps> {
  props: FormProps;

  constructor(props: FormProps) {
    super(props);
  }

  updateLabel = (event: any) => {
    this.props.onLabelUpdated(event.target.value, this.props.locale);
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div>
        <div className="AknComparableFields field-container">
          <div className="akeneo-text-field AknComparableFields-item AknFieldContainer original-field edit">
            <div className="AknFieldContainer-header">
              <label className="AknFieldContainer-label AknFieldContainer-label--grey"
                htmlFor="pim_enriched_entity.enriched_entity.properties.identifier"
              >
                <span className="badge-elements-container"></span>
                {__('pim_enriched_entity.enriched_entity.properties.identifier')}
                <span className="label-elements-container"></span>
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer field-input">
              <input
                type="text"
                name="identifier"
                id="pim_enriched_entity.enriched_entity.properties.identifier"
                className="AknTextField AknTextField--withDashedBottomBorder AknTextField--disabled"
                value={this.props.data.identifier}
                readOnly
              />
            </div>
          </div>
        </div>
        <div className="AknComparableFields field-container">
          <div className="akeneo-text-field AknComparableFields-item AknFieldContainer original-field edit">
            <div className="AknFieldContainer-header">
              <label className="AknFieldContainer-label AknFieldContainer-label--grey"
                htmlFor="pim_enriched_entity.enriched_entity.properties.label"
              >
                {__('pim_enriched_entity.enriched_entity.properties.label')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer field-input">
              <input
                type="text"
                name="label"
                id="pim_enriched_entity.enriched_entity.properties.label"
                className="AknTextField AknTextField--withBottomBorder"
                value={
                  undefined === this.props.data.labels[this.props.locale] ?
                  '' :
                  this.props.data.labels[this.props.locale]
                }
                onChange={this.updateLabel}
              />
              <Flag locale={this.props.locale} displayLanguage={false} />
            </div>
          </div>
        </div>
      </div>
    );
  }
}
