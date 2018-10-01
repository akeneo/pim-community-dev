import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Form from 'akeneoenrichedentity/application/component/enriched-entity/edit/form';
import {
  enrichedEntityLabelUpdated,
  saveEnrichedEntity,
  deleteEnrichedEntity,
  enrichedEntityImageUpdated,
} from 'akeneoenrichedentity/application/action/enriched-entity/edit';
import __ from 'akeneoenrichedentity/tools/translator';
import {EditionFormState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit/form';
import EnrichedEntity, {
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Header from 'akeneoenrichedentity/application/component/enriched-entity/edit/header';
import {
  SecondaryAction,
  breadcrumbConfiguration,
} from 'akeneoenrichedentity/application/component/enriched-entity/edit';
import File from 'akeneoenrichedentity/domain/model/file';
const securityContext = require('pim/security-context');

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
  };
  acls: {
    edit: boolean;
    delete: boolean;
  };
}

interface DispatchProps {
  events: {
    form: {
      onLabelUpdated: (value: string, locale: string) => void;
      onPressEnter: () => void;
      onImageUpdated: (image: File) => void;
    };
    onDelete: (enrichedEntity: EnrichedEntity) => void;
    onSaveEditForm: () => void;
  };
}

class Properties extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    const enrichedEntity = denormalizeEnrichedEntity(this.props.form.data);

    return (
      <React.Fragment>
        <Header
          label={enrichedEntity.getLabel(this.props.context.locale)}
          image={enrichedEntity.getImage()}
          primaryAction={() => {
            return this.props.acls.edit ? (
              <button className="AknButton AknButton--apply" onClick={this.props.events.onSaveEditForm}>
                {__('pim_enriched_entity.enriched_entity.button.save')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return this.props.acls.delete ? (
              <SecondaryAction
                onDelete={() => {
                  this.props.events.onDelete(enrichedEntity);
                }}
              />
            ) : null;
          }}
          withLocaleSwitcher={true}
          withChannelSwitcher={false}
          isDirty={this.props.form.state.isDirty}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">{__('pim_enriched_entity.enriched_entity.properties.title')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--withPadding">
            <Form
              onLabelUpdated={this.props.events.form.onLabelUpdated}
              onImageUpdated={this.props.events.form.onImageUpdated}
              onPressEnter={this.props.events.form.onPressEnter}
              locale={this.props.context.locale}
              data={this.props.form.data}
              errors={this.props.form.errors}
            />
          </div>
        </div>
      </React.Fragment>
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
      acls: {
        edit: true,
        delete: securityContext.isGranted('akeneo_enrichedentity_enriched_entity_delete'),
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        form: {
          onLabelUpdated: (value: string, locale: string) => {
            dispatch(enrichedEntityLabelUpdated(value, locale));
          },
          onPressEnter: () => {
            dispatch(saveEnrichedEntity());
          },
          onImageUpdated: (image: File) => {
            dispatch(enrichedEntityImageUpdated(image));
          },
        },
        onDelete: (enrichedEntity: EnrichedEntity) => {
          dispatch(deleteEnrichedEntity(enrichedEntity));
        },
        onSaveEditForm: () => {
          dispatch(saveEnrichedEntity());
        },
      },
    };
  }
)(Properties);
