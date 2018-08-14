import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Form from 'akeneoenrichedentity/application/component/enriched-entity/edit/form';
import {enrichedEntityLabelUpdated, saveEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/edit';
import __ from 'akeneoenrichedentity/tools/translator';
import {EditionFormState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit/form';

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
    }
  }
}

class Properties extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    return(
      <div className="AknSubsection">
        <header className="AknSubsection-title">
            <span className="group-label">{__('pim_enriched_entity.enriched_entity.properties.title')}</span>
        </header>
        <div className="AknFormContainer AknFormContainer--withPadding">
          <Form
            onLabelUpdated={this.props.events.form.onLabelUpdated}
            onPressEnter={this.props.events.form.onPressEnter}
            locale={this.props.context.locale}
            data={this.props.form.data}
            errors={this.props.form.errors}
          />
        </div>
      </div>
    );
  }
}

export default connect((state: EditState): StateProps => {
  const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

  return {
    form: state.form,
    context: {
      locale
    },
  }
}, (dispatch: any): DispatchProps => {
  return {
    events: {
      form: {
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(enrichedEntityLabelUpdated(value, locale));
        },
        onPressEnter: () => {
          dispatch(saveEnrichedEntity());
        }
      }
    }
  }
})(Properties);
