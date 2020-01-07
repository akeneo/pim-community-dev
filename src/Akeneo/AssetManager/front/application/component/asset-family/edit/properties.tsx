import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import Form from 'akeneoassetmanager/application/component/asset-family/edit/form';
import {
  assetFamilyLabelUpdated,
  saveAssetFamily,
  attributeAsMainMediaUpdated,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import {deleteAssetFamily} from 'akeneoassetmanager/application/action/asset-family/delete';
import __ from 'akeneoassetmanager/tools/translator';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
// import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
import {canEditAssetFamily, canEditLocale} from 'akeneoassetmanager/application/reducer/right';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
const securityContext = require('pim/security-context');

interface StateProps {
  form: EditionFormState;
  attributes: NormalizedAttribute[] | null;
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
}

interface DispatchProps {
  events: {
    form: {
      onLabelUpdated: (value: string, locale: string) => void;
      onSubmit: () => void;
      onAttributeAsMainMediaUpdated: (attributeAsMainMedia: AttributeIdentifier) => void;
    };
    onDelete: (assetFamily: AssetFamily) => void;
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
              // onClick={() => this.props.events.onOpenDeleteModal()} TODO
            >
              {__('pim_asset_manager.asset_family.module.delete.button')}
            </button>
          </div>
        </div>
      </div>
    );
  };

  render() {
    const assetFamily = this.props.form.data;

    return (
      <React.Fragment>
        <Header
          label={getAssetFamilyLabel(assetFamily, this.props.context.locale)}
          image={assetFamily.image}
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
              attributes={this.props.attributes}
              onLabelUpdated={this.props.events.form.onLabelUpdated}
              onAttributeAsMainMediaUpdated={this.props.events.form.onAttributeAsMainMediaUpdated}
              onSubmit={this.props.events.form.onSubmit}
              locale={this.props.context.locale}
              data={this.props.form.data}
              errors={this.props.form.errors}
              rights={this.props.rights}
            />
          </div>
        </div>
        {/* {this.props.confirmDelete.isActive && ( //TODO
          <DeleteModal
            message={__('pim_asset_manager.asset_family.delete.message', {assetFamilyLabel: label})}
            title={__('pim_asset_manager.asset_family.delete.title')}
            onConfirm={() => {
              this.props.events.onDelete(assetFamily);
            }}
            onCancel={() => {
              //TODO
            }}
          />
        )} */}
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = state.user.catalogLocale;

    return {
      attributes: state.attributes.attributes,
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
            securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
          delete:
            securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_family_delete') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
      },
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
          onAttributeAsMainMediaUpdated: (attributeAsMainMedia: AttributeIdentifier) => {
            dispatch(attributeAsMainMediaUpdated(attributeAsMainMedia));
          },
        },
        onDelete: (assetFamily: AssetFamily) => {
          dispatch(deleteAssetFamily(assetFamily));
        },
        onSaveEditForm: () => {
          dispatch(saveAssetFamily());
        },
      },
    };
  }
)(Properties);
