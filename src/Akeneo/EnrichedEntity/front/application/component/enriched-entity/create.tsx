import {createEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/create';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import {IndexState} from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import {
  enrichedEntityCreationCancel,
  enrichedEntityCreationCodeUpdated,
  enrichedEntityCreationLabelUpdated,
} from 'akeneoenrichedentity/domain/event/enriched-entity/create';
import {createLocaleFromCode} from 'akeneoenrichedentity/domain/model/locale';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import __ from 'akeneoenrichedentity/tools/translator';
import * as React from 'react';
import {connect} from 'react-redux';

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

  private onCodeUpdate = (event: any) => {
    this.props.events.onCodeUpdated(event.target.value);
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
        <div>
          <div className="AknFullPage AknFullPage--modal">
            <div className="AknFullPage-content">
              <div className="AknFullPage-left">
                <img src="bundles/pimui/images/illustrations/Family.svg" className="AknFullPage-image" />
              </div>
              <div className="AknFullPage-right">
                <div className="AknFullPage-subTitle">{__('pim_enriched_entity.enriched_entity.create.subtitle')}</div>
                <div className="AknFullPage-title">{__('pim_enriched_entity.enriched_entity.create.title')}</div>
                <div>
                  <div className="AknFieldContainer" data-code="label">
                    <div className="AknFieldContainer-header">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_enriched_entity.enriched_entity.create.input.label"
                      >
                        {__('pim_enriched_entity.enriched_entity.create.input.label')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        ref={(input: HTMLInputElement) => {
                          this.labelInput = input;
                        }}
                        className="AknTextField"
                        id="pim_enriched_entity.enriched_entity.create.input.label"
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
                    <div className="AknFieldContainer-header">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_enriched_entity.enriched_entity.create.input.code"
                      >
                        {__('pim_enriched_entity.enriched_entity.create.input.code')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer field-input">
                      <input
                        type="text"
                        className="AknTextField"
                        id="pim_enriched_entity.enriched_entity.create.input.code"
                        name="code"
                        value={this.props.data.code}
                        onChange={this.onCodeUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'code')}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="AknButtonList AknButtonList--right modal-footer">
          <span
            title="{__('pim_enriched_entity.enriched_entity.create.cancel')}"
            className="AknButtonList-item AknButton AknButton--grey cancel icons-holder-text"
            onClick={this.props.events.onCancel}
            tabIndex={0}
          >
            {__('pim_enriched_entity.enriched_entity.create.cancel')}
          </span>
          <button
            className="AknButtonList-item AknButton AknButton--apply ok icons-holder-text"
            onClick={this.props.events.onSubmit}
          >
            {__('pim_enriched_entity.enriched_entity.create.confirm')}
          </button>
        </div>
      </div>
    );
  }
}

export default connect(
  (state: IndexState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      data: state.create.data,
      errors: state.create.errors,
      context: {
        locale: locale,
      },
    } as StateProps;
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onCodeUpdated: (value: string) => {
          dispatch(enrichedEntityCreationCodeUpdated(value));
        },
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(enrichedEntityCreationLabelUpdated(value, locale));
        },
        onCancel: () => {
          dispatch(enrichedEntityCreationCancel());
        },
        onSubmit: () => {
          dispatch(createEnrichedEntity());
        },
      },
    } as DispatchProps;
  }
)(Create);
