import * as React from 'react';
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
      <form>
          <label>
            Code :
            <input type="text" name="code" value={this.state.code} onChange={this.handleChange} />
          </label>
          <label>
            Label :
            <input type="text" name="label" value={this.state.label} onChange={this.handleChange} />
          </label>
      </form>
    );
  }
}
