import React from 'react';
import {connect} from 'react-redux';
import {Checkbox, Key} from 'akeneo-design-system';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {
  recordCreationRecordCodeUpdated,
  recordCreationLabelUpdated,
  recordCreationCancel,
} from 'akeneoreferenceentity/domain/event/record/create';
import {createRecord} from 'akeneoreferenceentity/application/action/record/create';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';

interface StateProps {
  context: {
    locale: string;
  };
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
  };
  errors: ValidationError[];
  referenceEntity: ReferenceEntity;
}

interface DispatchProps {
  events: {
    onRecordCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onCancel: () => void;
    onSubmit: (createAnother: boolean) => void;
  };
}

interface CreateProps extends StateProps, DispatchProps {}

class Create extends React.Component<CreateProps, {createAnother: boolean}> {
  private labelInput: React.RefObject<HTMLInputElement>;
  state = {createAnother: false};
  public props: CreateProps;

  constructor(props: CreateProps) {
    super(props);

    this.labelInput = React.createRef();
  }

  componentDidMount() {
    if (null !== this.labelInput.current) {
      this.labelInput.current.focus();
    }
  }

  private onRecordCodeUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onRecordCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.onSubmit(this.state.createAnother);
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
        <div className="modal-body  creation">
          <div className="AknFullPage">
            <div className="AknFullPage-content AknFullPage-content--withIllustration">
              <div>
                <img src="bundles/pimui/images/illustrations/Records.svg" className="AknFullPage-image" />
              </div>
              <div>
                <div className="AknFormContainer">
                  <div className="AknFullPage-titleContainer">
                    <div className="AknFullPage-subTitle">{__('pim_reference_entity.record.create.subtitle')}</div>
                    <div className="AknFullPage-title">
                      {__('pim_reference_entity.record.create.title', {
                        entityLabel: this.props.referenceEntity.getLabel(this.props.context.locale).toLowerCase(),
                      })}
                    </div>
                  </div>
                  <div className="AknFieldContainer" data-code="label">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_reference_entity.record.create.input.label"
                      >
                        {__('pim_reference_entity.record.create.input.label')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        ref={this.labelInput}
                        autoComplete="off"
                        type="text"
                        className="AknTextField AknTextField--light"
                        id="pim_reference_entity.record.create.input.label"
                        name="label"
                        value={
                          undefined === this.props.data.labels[this.props.context.locale]
                            ? ''
                            : this.props.data.labels[this.props.context.locale]
                        }
                        onChange={this.onLabelUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                      <Flag
                        locale={createLocaleFromCode(this.props.context.locale)}
                        displayLanguage={false}
                        className="AknFieldContainer-inputSides"
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'labels')}
                    {getErrorsView(this.props.errors, 'label')}
                  </div>
                  <div className="AknFieldContainer" data-code="code">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_reference_entity.record.create.input.code"
                      >
                        {__('pim_reference_entity.record.create.input.code')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        autoComplete="off"
                        className="AknTextField AknTextField--light"
                        id="pim_reference_entity.record.create.input.code"
                        name="code"
                        value={this.props.data.code}
                        onChange={this.onRecordCodeUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'code')}
                  </div>
                  <div className="AknFieldContainer" data-code="create_another">
                    <Checkbox
                      id="pim_reference_entity.record.create.input.create_another"
                      checked={this.state.createAnother}
                      onChange={newValue => this.setState({createAnother: newValue})}
                    >
                      {__('pim_reference_entity.record.create.input.create_another')}
                    </Checkbox>
                  </div>
                  <button
                    className="AknButton AknButton--apply ok"
                    onClick={() => {
                      this.props.events.onSubmit(this.state.createAnother);
                    }}
                  >
                    {__('pim_reference_entity.record.create.confirm')}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          title="{__('pim_reference_entity.record.create.cancel')}"
          className="AknFullPage-cancel cancel"
          onClick={this.props.events.onCancel}
        />
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    return {
      data: state.createRecord.data,
      errors: state.createRecord.errors,
      context: {
        locale: state.user.catalogLocale,
      },
      referenceEntity: denormalizeReferenceEntity(state.form.data),
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRecordCodeUpdated: (value: string) => {
          dispatch(recordCreationRecordCodeUpdated(value));
        },
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(recordCreationLabelUpdated(value, locale));
        },
        onCancel: () => {
          dispatch(recordCreationCancel());
        },
        onSubmit: (createAnother: boolean) => {
          dispatch(createRecord(createAnother));
        },
      },
    };
  }
)(Create);
