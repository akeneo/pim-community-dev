import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoenrichedentity/application/reducer/record/edit';
import {recordLabelUpdated, saveRecord} from 'akeneoenrichedentity/application/action/record/edit';
import __ from 'akeneoenrichedentity/tools/translator';
import {EditionFormState} from 'akeneoenrichedentity/application/reducer/record/edit/form';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoenrichedentity/domain/model/locale';
import Flag from 'akeneoenrichedentity/tools/component/flag';

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
  };
}

interface DispatchProps {
  events: {
    form: {
      onLabelUpdated: (value: string, locale: string) => void;
      onPressEnter: () => void;
    };
  };
}

class Properties extends React.Component<StateProps & DispatchProps> {
  private labelInput: HTMLInputElement;
  props: StateProps & DispatchProps;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  updateLabel = (event: any) => {
    this.props.events.form.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  keyDown = (event: any) => {
    if ('Enter' === event.key) {
      this.props.events.form.onPressEnter();
    }
  };

  render() {
    return (
      <div className="AknSubsection">
        <header className="AknSubsection-title AknSubsection-title--blockDown">
          <span className="group-label">{__('pim_enriched_entity.record.enrich.title')}</span>
        </header>
        <div className="AknFormContainer AknFormContainer--withPadding">
          <div className="AknFieldContainer" data-code="identifier">
            <div className="AknFieldContainer-header">
              <label
                title="{__('pim_enriched_entity.record.enrich.identifier')}"
                className="AknFieldContainer-label"
                htmlFor="pim_enriched_entity.record.enrich.identifier"
              >
                {__('pim_enriched_entity.record.enrich.identifier')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <input
                type="text"
                name="identifier"
                id="pim_enriched_entity.record.enrich.identifier"
                className="AknTextField AknTextField--withDashedBottomBorder AknTextField--disabled"
                value={this.props.form.data.identifier}
                readOnly
              />
            </div>
            {getErrorsView(this.props.form.errors, 'identifier')}
          </div>
          <div className="AknFieldContainer" data-code="label">
            <div className="AknFieldContainer-header">
              <label
                title="{__('pim_enriched_entity.record.enrich.label')}"
                className="AknFieldContainer-label"
                htmlFor="pim_enriched_entity.record.enrich.label"
              >
                {__('pim_enriched_entity.record.create.input.label')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <input
                type="text"
                name="label"
                id="pim_enriched_entity.record.enrich.label"
                className="AknTextField AknTextField--withBottomBorder"
                value={
                  undefined === this.props.form.data.labels[this.props.context.locale]
                    ? ''
                    : this.props.form.data.labels[this.props.context.locale]
                }
                onChange={this.updateLabel}
                onKeyDown={this.keyDown}
                ref={(input: HTMLInputElement) => {
                  this.labelInput = input;
                }}
              />
              <Flag locale={createLocaleFromCode(this.props.context.locale)} displayLanguage={false} />
            </div>
            {getErrorsView(this.props.form.errors, 'labels')}
          </div>
        </div>
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      form: state.form,
      context: {
        locale,
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        form: {
          onLabelUpdated: (value: string, locale: string) => {
            dispatch(recordLabelUpdated(value, locale));
          },
          onPressEnter: () => {
            dispatch(saveRecord());
          },
        },
      },
    };
  }
)(Properties);
