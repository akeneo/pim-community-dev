import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import Flag from 'akeneoenrichedentity/tools/component/flag';

interface FormProps {
  locale: string;
  enrichedEntity: EnrichedEntity|null;
  updateEditForm: (enrichedEntity: EnrichedEntity) => void
}

export default class EditForm extends React.Component<FormProps> {
  props: FormProps;

  constructor(props: FormProps) {
    super(props);
  }

  updateLabel = (event: any) => {
    if (null === this.props.enrichedEntity) {
      return;
    }

    const identifier: Identifier = createIdentifier(this.props.enrichedEntity.getIdentifier().stringValue());
    const labelCollection: LabelCollection = this.props.enrichedEntity.getLabelCollection().setLabel(this.props.locale, event.target.value);
    const enrichedEntityUpdated: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    this.props.updateEditForm(enrichedEntityUpdated);
  };

  render(): JSX.Element | JSX.Element[] | null {
    if (null === this.props.enrichedEntity) {
      return null;
    }

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
                value={this.props.enrichedEntity.getIdentifier().stringValue()}
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
                value={this.props.enrichedEntity.getLabelCollection().hasLabel(this.props.locale) ?
                  this.props.enrichedEntity.getLabelCollection().getLabel(this.props.locale) :
                  ''
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
