import React from 'react';
import {connect} from 'react-redux';
import {Button, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {DeleteModal} from '@akeneo-pim-community/shared';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {EditForm} from 'akeneoassetmanager/application/component/asset-family/edit/form';
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

const Properties = ({events, attributes, context, form, rights}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();
  const assetFamily = form.data;
  const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

  return (
    <>
      <Header
        label={translate('pim_asset_manager.asset_family.tab.properties')}
        image={assetFamily.image}
        primaryAction={(defaultFocus: React.RefObject<any>) =>
          rights.assetFamily.edit ? (
            <Button onClick={events.onSaveEditForm} ref={defaultFocus}>
              {translate('pim_asset_manager.asset_family.button.save')}
            </Button>
          ) : null
        }
        secondaryActions={() => (
          <>
            {/* TODO Use DSM Dropdown */}
            {rights.assetFamily.delete && (
              <div className="AknSecondaryActions AknDropdown AknButtonList-item">
                <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
                <div className="AknDropdown-menu AknDropdown-menu--right">
                  <div className="AknDropdown-menuTitle">{translate('pim_datagrid.actions.other')}</div>
                  <div>
                    <button tabIndex={-1} className="AknDropdown-menuLink" onClick={openDeleteModal}>
                      {translate('pim_asset_manager.asset_family.module.delete.button')}
                    </button>
                  </div>
                </div>
              </div>
            )}
          </>
        )}
        withLocaleSwitcher={true}
        withChannelSwitcher={false}
        isDirty={form.state.isDirty}
        breadcrumb={<AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />}
        displayActions={rights.assetFamily.edit}
      />
      <div className="AknSubsection">
        <header className="AknSubsection-title">
          <span className="group-label">{translate('pim_asset_manager.asset_family.properties.title')}</span>
        </header>
        <div className="AknFormContainer AknFormContainer--withPadding">
          <EditForm
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
      {isDeleteModalOpen && (
        <DeleteModal
          title={translate('pim_asset_manager.asset_family.delete.title')}
          onConfirm={() => events.onDelete(assetFamily)}
          onCancel={closeDeleteModal}
        >
          {translate('pim_asset_manager.asset_family.delete.message', {assetFamilyLabel})}
        </DeleteModal>
      )}
    </>
  );
};

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
