import {createReferenceEntity} from 'akeneoreferenceentity/application/action/reference-entity/create';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {IndexState} from 'akeneoreferenceentity/application/reducer/reference-entity/index';
import {
  referenceEntityCreationCancel,
  referenceEntityCreationCodeUpdated,
  referenceEntityCreationLabelUpdated,
} from 'akeneoreferenceentity/domain/event/reference-entity/create';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import __ from 'akeneoreferenceentity/tools/translator';
import * as React from 'react';
import {connect} from 'react-redux';
import Key from 'akeneoreferenceentity/tools/key';

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
}

interface DispatchProps {
  events: {
    onCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface CreateProps extends StateProps, DispatchProps {}

class Create extends React.Component<CreateProps> {
  private labelInput: HTMLInputElement;
  public props: CreateProps;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  private onCodeUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.onSubmit();
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
        <div>
          <div className="AknFullPage">
            <div className="AknFullPage-content AknFullPage-content--withIllustration">
              <div>
                <img src="bundles/pimui/images/illustrations/Reference-entities.svg" className="AknFullPage-image" />
              </div>
              <div>
                <div className="AknFullPage-titleContainer">
                  <div className="AknFullPage-subTitle">
                    {__('pim_reference_entity.reference_entity.create.subtitle')}
                  </div>
                  <div className="AknFullPage-title">{__('pim_reference_entity.reference_entity.create.title')}</div>
                </div>
                <div className="AknFormContainer">
                  <div className="AknFieldContainer" data-code="label">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_reference_entity.reference_entity.create.input.label"
                      >
                        {__('pim_reference_entity.reference_entity.create.input.label')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        ref={(input: HTMLInputElement) => {
                          this.labelInput = input;
                        }}
                        className="AknTextField AknTextField--light"
                        id="pim_reference_entity.reference_entity.create.input.label"
                        name="label"
                        value={this.props.data.labels[this.props.context.locale]}
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
                  </div>
                  <div className="AknFieldContainer" data-code="code">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_reference_entity.reference_entity.create.input.code"
                      >
                        {__('pim_reference_entity.reference_entity.create.input.code')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer field-input">
                      <input
                        type="text"
                        className="AknTextField AknTextField--light"
                        id="pim_reference_entity.reference_entity.create.input.code"
                        name="code"
                        value={this.props.data.code}
                        onChange={this.onCodeUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'code')}
                  </div>
                  <button className="AknButton AknButton--apply ok" onClick={this.props.events.onSubmit}>
                    {__('pim_reference_entity.reference_entity.create.confirm')}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          title="{__('pim_reference_entity.reference_entity.create.cancel')}"
          className="AknFullPage-cancel cancel"
          onClick={this.props.events.onCancel}
          tabIndex={0}
        />
      </div>
    );
  }
}

export default connect(
  (state: IndexState): StateProps => {
    return {
      data: state.create.data,
      errors: state.create.errors,
      context: {
        locale: state.user.catalogLocale,
      },
    } as StateProps;
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onCodeUpdated: (value: string) => {
          dispatch(referenceEntityCreationCodeUpdated(value));
        },
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(referenceEntityCreationLabelUpdated(value, locale));
        },
        onCancel: () => {
          dispatch(referenceEntityCreationCancel());
        },
        onSubmit: () => {
          dispatch(createReferenceEntity());
        },
      },
    } as DispatchProps;
  }
)(Create);
