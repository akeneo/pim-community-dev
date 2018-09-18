import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoenrichedentity/application/reducer/record/edit';
import {recordLabelUpdated, saveRecord, recordValueUpdated} from 'akeneoenrichedentity/application/action/record/edit';
import __ from 'akeneoenrichedentity/tools/translator';
import {EditionFormState} from 'akeneoenrichedentity/application/reducer/record/edit/form';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoenrichedentity/domain/model/locale';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import denormalizeRecord from 'akeneoenrichedentity/application/denormalizer/record';
import {createLocaleReference} from 'akeneoenrichedentity/domain/model/locale-reference';
import {createChannelReference} from 'akeneoenrichedentity/domain/model/channel-reference';
import renderValues from 'akeneoenrichedentity/application/component/record/edit/enrich/value';
import Value from 'akeneoenrichedentity/domain/model/record/value';

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
    channel: string;
  };
}

interface DispatchProps {
  events: {
    form: {
      onLabelUpdated: (value: string, locale: string) => void;
      onPressEnter: () => void;
      onValueChange: (value: Value) => void;
    };
  };
}

class Enrich extends React.Component<StateProps & DispatchProps> {
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
    const record = denormalizeRecord(this.props.form.data);

    return (
      <div className="AknSubsection">
        <header className="AknSubsection-title AknSubsection-title--blockDown">
          <span className="group-label">{__('pim_enriched_entity.record.enrich.title')}</span>
        </header>
        <div className="AknFormContainer AknFormContainer--withPadding">
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
                value={record.getLabel(this.props.context.locale, true)}
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
          {renderValues(
            record,
            createChannelReference(this.props.context.channel),
            createLocaleReference(this.props.context.locale),
            this.props.form.errors,
            this.props.events.form.onValueChange
          )}
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
        channel: 'ecommerce',
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
          onValueChange: (value: Value) => {
            dispatch(recordValueUpdated(value));
          }
        },
      },
    };
  }
)(Enrich);
