import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import Form from 'akeneoreferenceentity/application/component/reference-entity/edit/form';
import {
  referenceEntityLabelUpdated,
  saveReferenceEntity,
  referenceEntityImageUpdated,
} from 'akeneoreferenceentity/application/action/reference-entity/edit';
import {deleteReferenceEntity} from 'akeneoreferenceentity/application/action/reference-entity/delete';
import __ from 'akeneoreferenceentity/tools/translator';
import {EditionFormState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit/form';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {breadcrumbConfiguration} from 'akeneoreferenceentity/application/component/reference-entity/edit';
import File from 'akeneoreferenceentity/domain/model/file';
const securityContext = require('pim/security-context');
import DeleteModal from 'akeneoreferenceentity/application/component/app/delete-modal';
import {openDeleteModal, cancelDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';
import {canEditReferenceEntity} from 'akeneoreferenceentity/infrastructure/permission/edit';

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
  };
  rights: {
    referenceEntity: {
      edit: boolean;
      delete: boolean;
    };
  };
  confirmDelete: {
    isActive: boolean;
  };
}

interface DispatchProps {
  events: {
    form: {
      onLabelUpdated: (value: string, locale: string) => void;
      onSubmit: () => void;
      onImageUpdated: (image: File) => void;
    };
    onDelete: (referenceEntity: ReferenceEntity) => void;
    onOpenDeleteModal: () => void;
    onCancelDeleteModal: () => void;
    onSaveEditForm: () => void;
  };
}

class Properties extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  private getSecondaryActions = () => {
    return (
      <div className="AknSecondaryActions AknDropdown AknButtonList-item">
        <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
        <div className="AknDropdown-menu AknDropdown-menu--right">
          <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
          <div>
            <button
              tabIndex={-1}
              className="AknDropdown-menuLink"
              onClick={() => this.props.events.onOpenDeleteModal()}
            >
              {__('pim_reference_entity.reference_entity.module.delete.button')}
            </button>
          </div>
        </div>
      </div>
    );
  };

  render() {
    const referenceEntity = denormalizeReferenceEntity(this.props.form.data);
    const label = referenceEntity.getLabel(this.props.context.locale);

    return (
      <React.Fragment>
        <Header
          label={referenceEntity.getLabel(this.props.context.locale)}
          image={referenceEntity.getImage()}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return this.props.rights.referenceEntity.edit ? (
              <button
                className="AknButton AknButton--apply"
                onClick={this.props.events.onSaveEditForm}
                ref={defaultFocus}
              >
                {__('pim_reference_entity.reference_entity.button.save')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return this.props.rights.referenceEntity.delete ? this.getSecondaryActions() : null;
          }}
          withLocaleSwitcher={true}
          withChannelSwitcher={false}
          isDirty={this.props.form.state.isDirty}
          breadcrumbConfiguration={breadcrumbConfiguration}
          displayActions={this.props.rights.referenceEntity.edit}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">{__('pim_reference_entity.reference_entity.properties.title')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--withPadding">
            <Form
              onLabelUpdated={this.props.events.form.onLabelUpdated}
              onImageUpdated={this.props.events.form.onImageUpdated}
              onSubmit={this.props.events.form.onSubmit}
              locale={this.props.context.locale}
              data={this.props.form.data}
              errors={this.props.form.errors}
              canEditReferenceEntity={this.props.rights.referenceEntity.edit}
            />
          </div>
        </div>
        {this.props.confirmDelete.isActive && (
          <DeleteModal
            message={__('pim_reference_entity.reference_entity.delete.message', {referenceEntityLabel: label})}
            title={__('pim_reference_entity.reference_entity.delete.title')}
            onConfirm={() => {
              this.props.events.onDelete(referenceEntity);
            }}
            onCancel={this.props.events.onCancelDeleteModal}
          />
        )}
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const confirmDelete = state.confirmDelete;

    return {
      form: state.form,
      context: {
        locale,
      },
      rights: {
        referenceEntity: {
          edit: securityContext.isGranted('akeneo_referenceentity_reference_entity_edit') && canEditReferenceEntity(),
          delete:
            securityContext.isGranted('akeneo_referenceentity_reference_entity_edit') &&
            securityContext.isGranted('akeneo_referenceentity_reference_entity_delete') &&
            canEditReferenceEntity(),
        },
      },
      confirmDelete,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        form: {
          onLabelUpdated: (value: string, locale: string) => {
            dispatch(referenceEntityLabelUpdated(value, locale));
          },
          onSubmit: () => {
            dispatch(saveReferenceEntity());
          },
          onImageUpdated: (image: File) => {
            dispatch(referenceEntityImageUpdated(image));
          },
        },
        onDelete: (referenceEntity: ReferenceEntity) => {
          dispatch(deleteReferenceEntity(referenceEntity));
        },
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
        },
        onOpenDeleteModal: () => {
          dispatch(openDeleteModal());
        },
        onSaveEditForm: () => {
          dispatch(saveReferenceEntity());
        },
      },
    };
  }
)(Properties);
