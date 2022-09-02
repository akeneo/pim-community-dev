import React from 'react';
import {connect, useDispatch} from 'react-redux';
import {Button, Link, SectionTitle, UserGroupsIllustration} from 'akeneo-design-system';
import {
  NoDataSection,
  NoDataText,
  NoDataTitle,
  PageContent,
  PageHeader,
  UnsavedChanges,
  useRouter,
  useSecurity,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
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
import {UserNavigation} from 'akeneoassetmanager/application/component/app/user-navigation';
import {useAssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFamilyFetcher';

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
  };
}

const Permission = ({assetFamily, context, canEditFamily, permission, events}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const {isGranted} = useSecurity();
  const dispatch = useDispatch();
  const assetFamilyFetcher = useAssetFamilyFetcher();
  const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);
  const canEditPermission = canEditFamily && isGranted('akeneo_assetmanager_asset_family_manage_permission');
  const canEditUserGroup = isGranted('pim_user_group_index') && isGranted('pim_user_group_create');

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <UserNavigation />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          {canEditPermission && !permission.data.isEmpty() && (
            <Button onClick={() => dispatch(savePermission(assetFamilyFetcher))}>
              {translate('pim_asset_manager.asset_family.button.save_permission')}
            </Button>
          )}
        </PageHeader.Actions>
        <PageHeader.State>{permission.state.isDirty && <UnsavedChanges />}</PageHeader.State>
        <PageHeader.Title>{translate('pim_asset_manager.asset_family.tab.permission')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <SectionTitle sticky={0}>
          <SectionTitle.Title>{translate('pim_asset_manager.asset_family.permission.title')}</SectionTitle.Title>
        </SectionTitle>
        {permission.data.isEmpty() ? (
          <NoDataSection>
            <UserGroupsIllustration size={256} />
            <NoDataTitle>{translate('pim_asset_manager.permission.no_data.title')}</NoDataTitle>
            <NoDataText>
              {translate('pim_asset_manager.permission.no_data.subtitle')}
              {canEditUserGroup && (
                <p>
                  <Link href={`#${router.generate('pim_user_group_index')}`} target="_blank">
                    {translate('pim_asset_manager.permission.no_data.link')}
                  </Link>
                </p>
              )}
            </NoDataText>
          </NoDataSection>
        ) : (
          <PermissionCollectionEditor
            readOnly={!canEditPermission}
            value={permission.data}
            prioritizedRightLevels={[RightLevel.View, RightLevel.Edit]}
            onChange={events.onPermissionUpdated}
          />
        )}
      </PageContent>
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
      },
    };
  }
)(Permission);
