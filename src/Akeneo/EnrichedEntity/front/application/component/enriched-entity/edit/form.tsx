import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

interface FormProps {
  locale: string;
  enrichedEntity: EnrichedEntity|null;
  updateEditForm: (enrichedEntity: EnrichedEntity) => void
}

interface FormState {
  code: string,
  label: string
}

export default class EditForm extends React.Component<FormProps> {
  state: FormState;
  props: FormProps;

  constructor(props: FormProps) {
    super(props);

    const {
      enrichedEntity,
      locale
    } = this.props;

    this.state = {
      code: null !== enrichedEntity ? enrichedEntity.getIdentifier().stringValue() : '',
      label: null !== enrichedEntity ? enrichedEntity.getLabel(locale) : ''
    };
  }

  componentDidUpdate(prevProps: FormProps, prevState: FormState) {
    if (this.props === prevProps && this.state !== prevState) {
      const identifier: Identifier = createIdentifier(this.state.code);
      const labelCollection: LabelCollection = createLabelCollection({ [this.props.locale]: this.state.label });
      const enrichedEntityUpdated: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);
      this.props.updateEditForm(enrichedEntityUpdated);
    }
  }

  handleChange = (event: any) => {
    this.setState({ [event.target.name]: event.target.value });
  };

  render(): JSX.Element | JSX.Element[] {
    return (
      <div className="AknSubsection">
        <header className="AknSubsection-title">
            <span className="group-label">{__('pim_enriched_entity.enriched_entity.properties_title')}</span>
        </header>
        <div>
          <div className="AknComparableFields field-container">
            <div className="akeneo-text-field AknComparableFields-item AknFieldContainer original-field edit">
              <div className="AknFieldContainer-header">
                <label className="AknFieldContainer-label">
                  <span className="badge-elements-container"></span>
                  {__('pim_enriched_entity.enriched_entity.code')}
                  <span className="label-elements-container"></span>
                </label>
              </div>
              <div className="AknFieldContainer-inputContainer field-input">
                <input type="text" name="code" className="AknTextField" value={this.state.code} onChange={this.handleChange} />
              </div>
            </div>
          </div>
          <div className="AknComparableFields field-container">
            <div className="akeneo-text-field AknComparableFields-item AknFieldContainer original-field edit">
              <div className="AknFieldContainer-header">
                <label className="AknFieldContainer-label">
                  {__('pim_enriched_entity.enriched_entity.label')}
                </label>
              </div>
              <div className="AknFieldContainer-inputContainer field-input">
                <input type="text" name="label" className="AknTextField" value={this.state.label} onChange={this.handleChange} />
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
