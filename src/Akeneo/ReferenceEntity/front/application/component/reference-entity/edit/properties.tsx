import React from 'react';
import {connect} from 'react-redux';
import {DeleteModal} from '@akeneo-pim-community/shared';
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
import {RefEntityBreadcrumb} from 'akeneoreferenceentity/application/component/app/breadcrumb';
import File from 'akeneoreferenceentity/domain/model/file';
import {openDeleteModal, cancelDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';
import {canEditLocale, canEditReferenceEntity} from 'akeneoreferenceentity/application/reducer/right';
const securityContext = require('pim/security-context');

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
  };
  rights: {
    locale: {
      edit: boolean;
    };
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
      <>
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
          breadcrumb={<RefEntityBreadcrumb referenceEntityIdentifier={referenceEntity.getIdentifier().stringValue()} />}
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
              rights={this.props.rights}
            />
          </div>
        </div>
        {this.props.confirmDelete.isActive && (
          <DeleteModal
            title={__('pim_reference_entity.reference_entity.delete.title')}
            onConfirm={() => this.props.events.onDelete(referenceEntity)}
            onCancel={this.props.events.onCancelDeleteModal}
          >
            {__('pim_reference_entity.reference_entity.delete.message', {referenceEntityLabel: label})}
          </DeleteModal>
        )}
      </>
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
      },
      rights: {
        locale: {
          edit: canEditLocale(state.right.locale, locale),
        },
        referenceEntity: {
          edit:
            securityContext.isGranted('akeneo_referenceentity_reference_entity_edit') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.identifier),
          delete:
            securityContext.isGranted('akeneo_referenceentity_reference_entity_edit') &&
            securityContext.isGranted('akeneo_referenceentity_reference_entity_delete') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.identifier),
        },
      },
      confirmDelete: state.confirmDelete,
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
