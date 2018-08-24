import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import {
  recordCreationRecordCodeUpdated,
  recordCreationLabelUpdated,
  recordCreationCancel,
} from 'akeneoenrichedentity/domain/event/record/create';
import {createRecord} from 'akeneoenrichedentity/application/action/record/create';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import EnrichedEntity, {
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {createLocaleFromCode} from 'akeneoenrichedentity/domain/model/locale';

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
  enrichedEntity: EnrichedEntity;
}

interface DispatchProps {
  events: {
    onRecordCodeUpdated: (value: string) => void;
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
    if (this.labelInput.focus()) {
      this.labelInput.focus();
    }
  }

  private onRecordCodeUpdate = (event: any) => {
    this.props.events.onRecordCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: any) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onKeyPress = (event: any) => {
    if ('Enter' === event.key) {
      this.props.events.onSubmit();
    }
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div className="modal in modal--fullPage" aria-hidden="false" style={{zIndex: 1041}}>
        <div className="modal-body  creation">
          <div className="AknFullPage AknFullPage--modal">
            <div className="AknFullPage-content">
              <div className="AknFullPage-left">
                <img src="bundles/pimui/images/illustrations/Family.svg" className="AknFullPage-image" />
              </div>
              <div className="AknFullPage-right">
                <div className="AknFullPage-subTitle">{__('pim_enriched_entity.record.create.subtitle')}</div>
                <div className="AknFullPage-title">
                  {__('pim_enriched_entity.record.create.title', {
                    entityLabel: this.props.enrichedEntity.getLabel(this.props.context.locale).toLowerCase(),
                  })}
                </div>
                <div className="AknFieldContainer" data-code="label">
                  <div className="AknFieldContainer-header">
                    <label className="AknFieldContainer-label" htmlFor="pim_enriched_entity.record.create.input.label">
                      {__('pim_enriched_entity.record.create.input.label')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      ref={(input: HTMLInputElement) => {
                        this.labelInput = input;
                      }}
                      type="text"
                      className="AknTextField"
                      id="pim_enriched_entity.record.create.input.label"
                      name="label"
                      value={this.props.data.labels[this.props.context.locale]}
                      onChange={this.onLabelUpdate}
                      onKeyPress={this.onKeyPress}
                    />
                    <Flag locale={createLocaleFromCode(this.props.context.locale)} displayLanguage={false} />
                  </div>
                  {getErrorsView(this.props.errors, 'labels')}
                </div>
                <div className="AknFieldContainer" data-code="code">
                  <div className="AknFieldContainer-header">
                    <label className="AknFieldContainer-label" htmlFor="pim_enriched_entity.record.create.input.code">
                      {__('pim_enriched_entity.record.create.input.code')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      type="text"
                      className="AknTextField"
                      id="pim_enriched_entity.record.create.input.code"
                      name="code"
                      value={this.props.data.code}
                      onChange={this.onRecordCodeUpdate}
                      onKeyPress={this.onKeyPress}
                    />
                  </div>
                  {getErrorsView(this.props.errors, 'identifier')}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="AknButtonList AknButtonList--right modal-footer">
          <span
            title="{__('pim_enriched_entity.record.create.cancel')}"
            className="AknButtonList-item AknButton AknButton--grey cancel icons-holder-text"
            onClick={this.props.events.onCancel}
          >
            {__('pim_enriched_entity.record.create.cancel')}
          </span>
          <button
            className="AknButtonList-item AknButton AknButton--apply ok icons-holder-text"
            onClick={this.props.events.onSubmit}
          >
            {__('pim_enriched_entity.record.create.confirm')}
          </button>
        </div>
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const enrichedEntity = denormalizeEnrichedEntity(state.form.data);

    return {
      data: state.createRecord.data,
      errors: state.createRecord.errors,
      context: {
        locale: locale,
      },
      enrichedEntity,
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
        onSubmit: () => {
          dispatch(createRecord());
        },
      },
    };
  }
)(Create);
