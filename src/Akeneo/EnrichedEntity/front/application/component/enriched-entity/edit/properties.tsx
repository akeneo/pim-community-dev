import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import Form from 'akeneoreferenceentity/application/component/reference-entity/edit/form';
import {
  referenceEntityLabelUpdated,
  saveReferenceEntity,
  deleteReferenceEntity,
  referenceEntityImageUpdated,
} from 'akeneoreferenceentity/application/action/reference-entity/edit';
import __ from 'akeneoreferenceentity/tools/translator';
import {EditionFormState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit/form';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {
  SecondaryAction,
  breadcrumbConfiguration,
} from 'akeneoreferenceentity/application/component/reference-entity/edit';
import File from 'akeneoreferenceentity/domain/model/file';
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
    onDelete: (referenceEntity: ReferenceEntity) => void;
    onSaveEditForm: () => void;
  };
}

class Properties extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    const referenceEntity = denormalizeReferenceEntity(this.props.form.data);

    return (
      <React.Fragment>
        <Header
          label={referenceEntity.getLabel(this.props.context.locale)}
          image={referenceEntity.getImage()}
          primaryAction={() => {
            return this.props.acls.edit ? (
              <button className="AknButton AknButton--apply" onClick={this.props.events.onSaveEditForm}>
                {__('pim_reference_entity.reference_entity.button.save')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return this.props.acls.delete ? (
              <SecondaryAction
                onDelete={() => {
                  this.props.events.onDelete(referenceEntity);
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
            <span className="group-label">{__('pim_reference_entity.reference_entity.properties.title')}</span>
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
        delete: securityContext.isGranted('akeneo_referenceentity_reference_entity_delete'),
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        form: {
          onLabelUpdated: (value: string, locale: string) => {
            dispatch(referenceEntityLabelUpdated(value, locale));
          },
          onPressEnter: () => {
            dispatch(saveReferenceEntity());
          },
          onImageUpdated: (image: File) => {
            dispatch(referenceEntityImageUpdated(image));
          },
        },
        onDelete: (referenceEntity: ReferenceEntity) => {
          dispatch(deleteReferenceEntity(referenceEntity));
        },
        onSaveEditForm: () => {
          dispatch(saveReferenceEntity());
        },
      },
    };
  }
)(Properties);
