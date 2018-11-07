import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
import {recordLabelUpdated, saveRecord, recordValueUpdated} from 'akeneoreferenceentity/application/action/record/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import {EditionFormState} from 'akeneoreferenceentity/application/reducer/record/edit/form';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import renderValues from 'akeneoreferenceentity/application/component/record/edit/enrich/value';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import Key from 'akeneoreferenceentity/tools/key';

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
      onValueChange: (value: Value) => void;
      onSubmit: () => void;
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

  updateLabel = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.form.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  keyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.form.onSubmit();
  };

  render() {
    const record = denormalizeRecord(this.props.form.data);

    return (
      <div className="AknSubsection">
        <div className="AknFormContainer AknFormContainer--wide AknFormContainer--withPadding">
          <div className="AknFieldContainer AknFieldContainer--narrow" data-code="label">
            <div className="AknFieldContainer-header AknFieldContainer-header--light AknFieldContainer-header AknFieldContainer-header--light--small">
              <label
                title="{__('pim_reference_entity.record.enrich.label')}"
                className="AknFieldContainer-label"
                htmlFor="pim_reference_entity.record.enrich.label"
              >
                {__('pim_reference_entity.record.create.input.label')}
              </label>
            </div>
            <div className="AknFieldContainer-inputContainer">
              <input
                type="text"
                name="label"
                id="pim_reference_entity.record.enrich.label"
                className="AknTextField AknTextField--narrow AknTextField--light"
                value={record.getLabel(this.props.context.locale, false)}
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
            this.props.events.form.onValueChange,
            this.props.events.form.onSubmit
          )}
        </div>
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const channel =
      undefined === state.user || undefined === state.user.catalogChannel ? '' : state.user.catalogChannel;

    return {
      form: state.form,
      context: {
        locale,
        channel,
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
          onValueChange: (value: Value) => {
            dispatch(recordValueUpdated(value));
          },
          onSubmit: () => {
            dispatch(saveRecord());
          },
        },
      },
    };
  }
)(Enrich);
