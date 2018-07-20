import * as React from 'react';
import {connect} from 'react-redux';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import {
  recordCreationRecordCodeUpdated,
  recordCreationLabelUpdated,
  recordCreationCancel
} from 'akeneoenrichedentity/domain/event/record/create';
import {createRecord} from 'akeneoenrichedentity/application/action/record/create';

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
    onRecordCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onCancel: () => void;
    onSubmit: (code: string, labels: { [localeCode: string]: string }) => void;
  }
}

interface CreateProps extends StateProps, DispatchProps {}

class Create extends React.Component<CreateProps> {
  private labelInput: HTMLInputElement;
  public props: CreateProps;

  constructor(props: CreateProps) {
    super(props);
  }

  componentDidMount(){
    this.labelInput.focus();
  }

  private onRecordCodeUpdate = (event: any) => {
    this.props.events.onRecordCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: any) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onCancel = () => {
    this.props.events.onCancel();
  };

  private onSubmit = () => {
    this.props.events.onSubmit(this.props.data.code, this.props.data.labels);
  };

  private getCodeValidationErrorsMessages = () => {
    const errors = this.props.errors.filter((error: ValidationError) => {
      return 'identifier' == error.propertyPath;
    });

    const errorMessages = errors.map((error: ValidationError, key:number) => {
      return <span className="error-message" key={key}>{__(error.messageTemplate, error.parameters)}</span>;
    });

    if (errorMessages.length > 0) {
      return (
        <div className="AknFieldContainer-footer AknFieldContainer-validationErrors validation-errors">
      <span className="AknFieldContainer-validationError">
        <i className="icon-warning-sign"></i>
        {errorMessages}
      </span>
        </div>
      );
    }

    return null;
  };

  render(): JSX.Element | JSX.Element[] | null {
    const errorContainer: JSX.Element | null = this.getCodeValidationErrorsMessages();

    return (
      <div className="modal in modal--fullPage" aria-hidden="false" style={{zIndex: 1041}}>
        <div className="modal-body  creation">
          <div className="AknFullPage AknFullPage--modal">
            <div className="AknFullPage-content">
              <div className="AknFullPage-left">
                <img src="bundles/pimui/images/illustrations/Product.svg" className="AknFullPage-image"/>
              </div>
              <div className="AknFullPage-right">
                <div
                  className="AknFullPage-subTitle">{__('pim_enriched_entity.record.create.subtitle')}</div>
                <div className="AknFullPage-title">{__('pim_enriched_entity.record.create.title')}</div>
                <div className="AknFieldContainer" data-code="label">
                  <div className="AknFieldContainer-header">
                    <label className="AknFieldContainer-label control-label required truncate"
                      htmlFor="pim_enriched_entity.record.create.input.label">{__('pim_enriched_entity.record.create.input.label')}</label>
                  </div>
                  <div className="AknFieldContainer-inputContainer field-input">
                    <input ref={(input: HTMLInputElement) => { this.labelInput = input; }}
                      type="text"
                      className="AknTextField" id="pim_enriched_entity.record.create.input.label"
                      name="label"
                      value={this.props.data.labels[this.props.context.locale]}
                      onChange={this.onLabelUpdate} />
                    <Flag locale={this.props.context.locale} displayLanguage={false}/>
                  </div>
                </div>
                <div className="AknFieldContainer" data-code="code">
                  <div className="AknFieldContainer-header">
                    <label title="Code" className="AknFieldContainer-label control-label required truncate"
                      htmlFor="pim_enriched_entity.record.create.input.code">{__('pim_enriched_entity.record.create.input.code')}</label>
                  </div>
                  <div className="AknFieldContainer-inputContainer field-input">
                    <input type="text" className="AknTextField" id="pim_enriched_entity.record.create.input.code" name="code"
                      value={this.props.data.code}
                      onChange={this.onRecordCodeUpdate} />
                  </div>
                  {errorContainer}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="AknButtonList AknButtonList--right modal-footer">
          <span title="{__('pim_enriched_entity.record.create.cancel')}"
            className="AknButtonList-item AknButton AknButton--grey cancel icons-holder-text"
            onClick={this.onCancel}
          >
            {__('pim_enriched_entity.record.create.cancel')}
          </span>
          <button
            className="AknButtonList-item AknButton AknButton--apply ok icons-holder-text"
            onClick={this.onSubmit}
          >
            {__('pim_enriched_entity.record.create.confirm')}
          </button>
        </div>
      </div>
    );
  };
}

export default connect((state: State): StateProps => {
  const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

  return {
    data: state.createRecord.data,
    errors: state.createRecord.errors,
    context: {
      locale: locale
    }
  };
}, (dispatch: any): DispatchProps => {
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
      onSubmit: (code: string, labels: { [localeCode: string]: string }) => {
        dispatch(createRecord(code, labels));
      }
    }
  }
})(Create);
