import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
import {recordLabelUpdated, recordValueUpdated, saveRecord} from 'akeneoreferenceentity/application/action/record/edit';
import {EditionFormState} from 'akeneoreferenceentity/application/reducer/record/edit/form';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import renderValues from 'akeneoreferenceentity/application/component/record/edit/enrich/value';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import Key from 'akeneoreferenceentity/tools/key';
import {canEditReferenceEntity, canEditLocale} from 'akeneoreferenceentity/application/reducer/right';

const securityContext = require('pim/security-context');

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
    channel: string;
  };
  rights: {
    locale: {
      edit: boolean;
    };
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

    return (
      <div className="AknSubsection">
        <div className="AknFormContainer AknFormContainer--wide AknFormContainer--withPadding">
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
        locale: {
          edit: canEditLocale(state.right.locale, locale),
        },
        record: {
          edit:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.reference_entity_identifier),
          delete:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            securityContext.isGranted('akeneo_referenceentity_record_delete') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.reference_entity_identifier),
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
