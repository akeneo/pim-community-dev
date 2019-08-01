import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import Form from 'akeneoassetmanager/application/component/asset-family/edit/form';
import {
  assetFamilyLabelUpdated,
  saveAssetFamily,
  assetFamilyImageUpdated,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import {deleteAssetFamily} from 'akeneoassetmanager/application/action/asset-family/delete';
import __ from 'akeneoassetmanager/tools/translator';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import AssetFamily, {denormalizeAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import File from 'akeneoassetmanager/domain/model/file';
// const securityContext = require('pim/security-context');
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
import {openDeleteModal, cancelDeleteModal} from 'akeneoassetmanager/application/event/confirmDelete';
import {canEditLocale, canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
  };
  rights: {
    locale: {
      edit: boolean;
    };
    assetFamily: {
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
    onDelete: (assetFamily: AssetFamily) => void;
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
              {__('pim_asset_manager.asset_family.module.delete.button')}
            </button>
          </div>
        </div>
      </div>
    );
  };

  render() {
    const assetFamily = denormalizeAssetFamily(this.props.form.data);
    const label = assetFamily.getLabel(this.props.context.locale);

    return (
      <React.Fragment>
        <Header
          label={assetFamily.getLabel(this.props.context.locale)}
          image={assetFamily.getImage()}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return this.props.rights.assetFamily.edit ? (
              <button
                className="AknButton AknButton--apply"
                onClick={this.props.events.onSaveEditForm}
                ref={defaultFocus}
              >
                {__('pim_asset_manager.asset_family.button.save')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return this.props.rights.assetFamily.delete ? this.getSecondaryActions() : null;
          }}
          withLocaleSwitcher={true}
          withChannelSwitcher={false}
          isDirty={this.props.form.state.isDirty}
          breadcrumbConfiguration={breadcrumbConfiguration}
          displayActions={this.props.rights.assetFamily.edit}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">{__('pim_asset_manager.asset_family.properties.title')}</span>
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
            message={__('pim_asset_manager.asset_family.delete.message', {assetFamilyLabel: label})}
            title={__('pim_asset_manager.asset_family.delete.title')}
            onConfirm={() => {
              this.props.events.onDelete(assetFamily);
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
        assetFamily: {
          edit:
            // securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
          delete:
            // securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            // securityContext.isGranted('akeneo_assetmanager_asset_family_delete') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
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
            dispatch(assetFamilyLabelUpdated(value, locale));
          },
          onSubmit: () => {
            dispatch(saveAssetFamily());
          },
          onImageUpdated: (image: File) => {
            dispatch(assetFamilyImageUpdated(image));
          },
        },
        onDelete: (assetFamily: AssetFamily) => {
          dispatch(deleteAssetFamily(assetFamily));
        },
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
        },
        onOpenDeleteModal: () => {
          dispatch(openDeleteModal());
        },
        onSaveEditForm: () => {
          dispatch(saveAssetFamily());
        },
      },
    };
  }
)(Properties);
