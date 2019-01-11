import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
import {recordLabelUpdated, recordValueUpdated, saveRecord} from 'akeneoreferenceentity/application/action/record/edit';
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
import {canEditReferenceEntity, canEditLocale} from 'akeneoreferenceentity/application/reducer/user';

const securityContext = require('pim/security-context');

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
    channel: string;
  };
  rights: {
    record: {
      edit: boolean;
      delete: boolean;
    };
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
    const inputTextClassName = `AknTextField AknTextField--narrow AknTextField--light ${
      !this.props.rights.record.edit ? 'AknTextField--disabled' : ''
    }`;

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
                className={inputTextClassName}
                value={record.getLabel(this.props.context.locale, false)}
                onChange={this.updateLabel}
                onKeyDown={this.keyDown}
                ref={(input: HTMLInputElement) => {
                  this.labelInput = input;
                }}
                disabled={!this.props.rights.record.edit}
                readOnly={!this.props.rights.record.edit}
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
            this.props.events.form.onSubmit,
            this.props.rights
          )}
        </div>
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = state.user.catalogLocale;

    return {
      form: state.form,
      context: {
        locale: locale,
        channel: state.user.catalogChannel,
      },
      rights: {
        record: {
          edit:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            canEditReferenceEntity(
              state.user.permission.referenceEntity,
              state.form.data.reference_entity_identifier
            ) &&
            canEditLocale(state.user.permission.locale, locale),
          delete:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            securityContext.isGranted('akeneo_referenceentity_record_delete') &&
            canEditReferenceEntity(
              state.user.permission.referenceEntity,
              state.form.data.reference_entity_identifier
            ) &&
            canEditLocale(state.user.permission.locale, locale),
        },
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
