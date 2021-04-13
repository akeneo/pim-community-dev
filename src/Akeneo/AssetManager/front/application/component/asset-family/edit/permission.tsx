import React from 'react';
import {connect} from 'react-redux';
import {Button, SectionTitle} from 'akeneo-design-system';
import {useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {AssetFamilyBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import {PermissionCollectionEditor} from 'akeneoassetmanager/tools/component/permission';
import {FormState} from 'akeneoassetmanager/application/reducer/state';
import {permissionEditionUpdated} from 'akeneoassetmanager/domain/event/asset-family/permission';
import {
  denormalizePermissionCollection,
  PermissionCollection,
  RightLevel,
} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {savePermission} from 'akeneoassetmanager/application/action/asset-family/permission';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';

interface StateProps {
  assetFamily: AssetFamily;
  permission: {
    data: PermissionCollection;
    state: FormState;
  };
  context: {
    locale: string;
  };
  canEditFamily: boolean;
}

interface DispatchProps {
  events: {
    onPermissionUpdated: (updatedConfiguration: PermissionCollection) => void;
    onSavePermissionEditForm: () => void;
  };
}

const Permission = ({assetFamily, context, canEditFamily, permission, events}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const {isGranted} = useSecurity();
  const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);
  const canEditPermission = canEditFamily && isGranted('akeneo_assetmanager_asset_family_manage_permission');
  const canEditUserGroup = isGranted('pim_user_group_index') && isGranted('pim_user_group_create');

  return (
    <>
      <Header
        label={translate('pim_asset_manager.asset_family.tab.permission')}
        image={assetFamily.image}
        primaryAction={(defaultFocus: React.RefObject<any>) =>
          canEditPermission && !permission.data.isEmpty() ? (
            <Button onClick={events.onSavePermissionEditForm} ref={defaultFocus}>
              {translate('pim_asset_manager.asset_family.button.save_permission')}
            </Button>
          ) : null
        }
        withLocaleSwitcher={false}
        withChannelSwitcher={false}
        isDirty={permission.state.isDirty}
        breadcrumb={<AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />}
      />
      <div className="AknSubsection">
        <SectionTitle sticky={136}>
          <SectionTitle.Title>{translate('pim_asset_manager.asset_family.permission.title')}</SectionTitle.Title>
        </SectionTitle>
        <div className="AknFormContainer AknFormContainer--wide">
          {!permission.data.isEmpty() ? (
            <PermissionCollectionEditor
              readOnly={!canEditPermission}
              value={permission.data}
              prioritizedRightLevels={[RightLevel.View, RightLevel.Edit]}
              onChange={events.onPermissionUpdated}
            />
          ) : (
            <div className="AknGridContainer-noData">
              <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--user-group" />
              <div className="AknGridContainer-noDataTitle">
                {translate('pim_asset_manager.permission.no_data.title')}
              </div>
              <div className="AknGridContainer-noDataSubtitle">
                {translate('pim_asset_manager.permission.no_data.subtitle')}{' '}
                {canEditUserGroup && (
                  <a href={`#${router.generate('pim_user_group_index')}`} target="_blank">
                    {translate('pim_asset_manager.permission.no_data.link')}
                  </a>
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      assetFamily: state.form.data,
      permission: {
        data: denormalizePermissionCollection(state.permission.data),
        state: state.permission.state,
      },
      context: {
        locale,
      },
      canEditFamily: canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onPermissionUpdated: (permission: PermissionCollection) => {
          dispatch(permissionEditionUpdated(permission));
        },
        onSavePermissionEditForm: () => {
          dispatch(savePermission());
        },
      },
    };
  }
)(Permission);
