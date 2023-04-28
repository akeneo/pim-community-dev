import React, {useEffect, useState} from 'react';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  useRouter,
  useSecurity,
  useTranslate,
  Translate,
  useFeatureFlags,
} from '@akeneo-pim-community/shared';
import {
  ActivityIcon,
  Breadcrumb,
  FileIcon,
  GroupsIcon,
  IconCard,
  IconCardGrid,
  IdIcon,
  KeyIcon,
  LockIcon,
  SectionTitle,
  SettingsIcon,
  UserIcon,
  useTheme,
} from 'akeneo-design-system';
import styled from 'styled-components';
import {CountEntities, useCountEntities} from '../hooks';
import {ResetButton} from './reset-pim';

const SectionContent = styled.div`
  margin-top: 20px;
  margin-bottom: 30px;
`;

const getPluralizedTranslation = (
  translate: Translate,
  translationId: string,
  countEntities: CountEntities,
  propertyPath: string
) => {
  return countEntities.hasOwnProperty(propertyPath)
    ? translate(translationId, {count: countEntities[propertyPath]}, countEntities[propertyPath])
    : '';
};

const SystemIndex = () => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const router = useRouter();
  const theme = useTheme();
  const {isEnabled} = useFeatureFlags();

  const [isSSOEnabled, setIsSSOEnabled] = useState<boolean>(false);

  const canAccessCatalogVolumMonitoring = isGranted('view_catalog_volume_monitoring');
  const canAccessConfiguration = isGranted('oro_config_system');
  const canAccessSystemInfos = isGranted('pim_analytics_system_info_index');
  const canAccessSSO = isGranted('pimee_sso_configuration');

  const canAccessSystemNavigation =
    canAccessCatalogVolumMonitoring || canAccessConfiguration || canAccessSystemInfos || canAccessSSO;

  const canAccessUsers = isGranted('pim_user_user_index');
  const canAccessUserGroups = isGranted('pim_user_group_index');
  const canAccessRoles = isGranted('pim_user_role_index');

  const canAccessUsersNavigation = canAccessUsers || canAccessUserGroups || canAccessRoles;

  const countEntities = useCountEntities();

  useEffect(() => {
    if (!canAccessSSO) {
      return;
    }
    (async () => {
      const response = await fetch(router.generate('authentication_sso_configuration_get'));
      const JSONResponse = await response.json();

      setIsSSOEnabled(JSONResponse.hasOwnProperty('configuration') ? JSONResponse.configuration.is_enabled : false);
    })();
  }, [canAccessSSO]);

  const redirectToRoute = (route: string) => {
    router.redirect(router.generate(route));
  };

  if (!canAccessSystemNavigation && !canAccessUsersNavigation) {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: 403})}
        message={translate('error.forbidden')}
        code={403}
      />
    );
  }

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step>{translate('pim_menu.tab.system')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{translate('pim_system.system_menu')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        {canAccessSystemNavigation && (
          <>
            <SectionTitle>
              <SectionTitle.Title>{translate('pim_system.system_navigation')}</SectionTitle.Title>
              {isEnabled('reset_pim') && (
                <>
                  <SectionTitle.Spacer />
                  <ResetButton />
                </>
              )}
            </SectionTitle>
            <SectionContent>
              <IconCardGrid>
                {canAccessCatalogVolumMonitoring && (
                  <IconCard
                    id="pim-system-catalog-volume"
                    icon={<ActivityIcon />}
                    label={translate('pim_menu.item.catalog_volume')}
                    onClick={() => redirectToRoute('pim_enrich_catalog_volume_index')}
                    content={
                      countEntities.hasOwnProperty('count_product_values') && countEntities.count_product_values
                        ? translate(
                            'pim_system.count.product_values',
                            {count: countEntities.count_product_values},
                            countEntities.count_product_values
                          )
                        : ''
                    }
                  />
                )}
                {canAccessConfiguration && (
                  <IconCard
                    id="pim-system-configuration"
                    icon={<SettingsIcon />}
                    label={translate('pim_menu.item.configuration')}
                    onClick={() => redirectToRoute('oro_config_configuration_system')}
                  />
                )}
                {canAccessSystemInfos && (
                  <IconCard
                    id="pim-system-info"
                    icon={<FileIcon />}
                    label={translate('pim_menu.item.info')}
                    onClick={() => redirectToRoute('pim_analytics_system_info_index')}
                  />
                )}
                {canAccessSSO && isEnabled('sso_configuration') && (
                  <IconCard
                    id="pim-system-sso"
                    icon={<IdIcon />}
                    label={translate('pim_system.sso.title')}
                    onClick={() => redirectToRoute('authentication_sso_configuration_edit')}
                    content={translate(isSSOEnabled ? 'pim_system.sso.enabled' : 'pim_system.sso.disabled')}
                  />
                )}
                {isEnabled('free_trial') && (
                  <DisableIconCard
                    id="pim-system-sso"
                    icon={
                      <LockIconContainer>
                        <LockIcon size={16} color={theme.color.blue100} />
                      </LockIconContainer>
                    }
                    label={translate('pim_system.sso.title')}
                    content={translate('free_trial.menu.feature_ee_only')}
                  />
                )}
              </IconCardGrid>
            </SectionContent>
          </>
        )}
        {canAccessUsersNavigation && (
          <>
            <SectionTitle>
              <SectionTitle.Title>{translate('pim_system.users_management_navigation')}</SectionTitle.Title>
            </SectionTitle>
            <SectionContent>
              <IconCardGrid>
                {canAccessUsers && (
                  <IconCard
                    id="pim-system-user-user"
                    icon={<UserIcon />}
                    label={translate('pim_menu.item.user')}
                    onClick={() => redirectToRoute('pim_user_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_system.count.users',
                      countEntities,
                      'count_users'
                    )}
                  />
                )}
                {canAccessUserGroups && !isEnabled('free_trial') && (
                  <IconCard
                    id="pim-system-user-group"
                    icon={<GroupsIcon />}
                    label={translate('pim_menu.item.user_group')}
                    onClick={() => redirectToRoute('pim_user_group_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_system.count.user_groups',
                      countEntities,
                      'count_user_groups'
                    )}
                  />
                )}
                {isEnabled('free_trial') && (
                  <DisableIconCard
                    id="pim-system-user-group"
                    icon={
                      <LockIconContainer>
                        <LockIcon size={16} color={theme.color.blue100} />
                      </LockIconContainer>
                    }
                    label={translate('pim_menu.item.user_group')}
                    content={translate('free_trial.menu.feature_ee_only')}
                  />
                )}
                {canAccessRoles && (
                  <IconCard
                    id="pim-system-user-role"
                    icon={<KeyIcon />}
                    label={translate('pim_menu.item.user_role')}
                    onClick={() => redirectToRoute('pim_user_role_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_system.count.roles',
                      countEntities,
                      'count_roles'
                    )}
                  />
                )}
              </IconCardGrid>
            </SectionContent>
          </>
        )}
      </PageContent>
    </>
  );
};

const DisableIconCard = styled(IconCard)`
  cursor: pointer;
  border: 1px rgba(240, 241, 243, 0.5) solid;

  :hover {
    background: #fff;
    border: 1px rgba(240, 241, 243, 0.5) solid;
  }

  > *:not(:first-child) {
    opacity: 0.5;
  }
`;

const LockIconContainer = styled.div`
  border: 1px solid #4ca8e0;
  border-radius: 4px;
  background: #f0f7fc;
  height: 24px;
  width: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
`;

export {SystemIndex};
