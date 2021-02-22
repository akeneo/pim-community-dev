import React from 'react';
import {connect} from 'react-redux';
import {Button} from 'akeneo-design-system';
import __ from 'akeneoassetmanager/tools/translator';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import Form from 'akeneoassetmanager/application/component/asset-family/edit/form';
import {
  assetFamilyLabelUpdated,
  saveAssetFamily,
  attributeAsMainMediaUpdated,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import {deleteAssetFamily} from 'akeneoassetmanager/application/action/asset-family/delete';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {AssetFamilyBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
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
  public props: StateProps & DispatchProps;
  public state: {isDeleteModalOpen: boolean} = {
    isDeleteModalOpen: false,
  };

  //TODO Use DSM Dropdown
  private getSecondaryActions = () => (
    <div className="AknSecondaryActions AknDropdown AknButtonList-item">
      <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
      <div className="AknDropdown-menu AknDropdown-menu--right">
        <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
        <div>
          <button
            tabIndex={-1}
            className="AknDropdown-menuLink"
            onClick={() => this.setState({isDeleteModalOpen: true})}
          >
            {__('pim_asset_manager.asset_family.module.delete.button')}
          </button>
        </div>
      </div>
    </div>
  );

  render() {
    const {events, attributes, context, form, rights} = this.props;
    const assetFamily = form.data;
    const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

    return (
      <>
        <Header
          label={__('pim_asset_manager.asset_family.tab.properties')}
          image={assetFamily.image}
          primaryAction={(defaultFocus: React.RefObject<any>) =>
            rights.assetFamily.edit ? (
              <Button onClick={events.onSaveEditForm} ref={defaultFocus}>
                {__('pim_asset_manager.asset_family.button.save')}
              </Button>
            ) : null
          }
          secondaryActions={() => (rights.assetFamily.delete ? this.getSecondaryActions() : null)}
          withLocaleSwitcher={true}
          withChannelSwitcher={false}
          isDirty={form.state.isDirty}
          breadcrumb={<AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />}
          displayActions={rights.assetFamily.edit}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">{__('pim_asset_manager.asset_family.properties.title')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--withPadding">
            <Form
              attributes={attributes}
              onLabelUpdated={events.form.onLabelUpdated}
              onAttributeAsMainMediaUpdated={events.form.onAttributeAsMainMediaUpdated}
              onSubmit={events.form.onSubmit}
              locale={context.locale}
              data={form.data}
              errors={form.errors}
              rights={rights}
            />
          </div>
        </div>
        {this.state.isDeleteModalOpen && (
          <DeleteModal
            message={__('pim_asset_manager.asset_family.delete.message', {assetFamilyLabel})}
            title={__('pim_asset_manager.asset_family.delete.title')}
            onConfirm={() => events.onDelete(assetFamily)}
            onCancel={() => this.setState({isDeleteModalOpen: false})}
          />
        )}
      </>
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
