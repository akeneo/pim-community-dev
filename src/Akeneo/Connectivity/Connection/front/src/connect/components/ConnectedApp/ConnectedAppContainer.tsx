import React, {FC, useCallback, useEffect, useState} from 'react';
import {AppIllustration, Breadcrumb, DangerIcon, Helper, TabBar, useTabBar} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../../shared/translate';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useRouter} from '../../../shared/router/use-router';
import {ApplyButton, DropdownLink, PageContent, PageHeader, SecondaryActionsDropdownButton} from '../../../common';
import {UserButtons} from '../../../shared/user';
import {ConnectedAppSettings} from './ConnectedAppSettings';
import {useFeatureFlags, useSessionStorageState} from '@akeneo-pim-community/shared';
import {ConnectedAppPermissions} from './ConnectedAppPermissions';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import usePermissionsFormProviders from '../../hooks/use-permissions-form-providers';
import {useHistory} from 'react-router';
import {useSaveConnectedAppMonitoringSettings} from '../../hooks/use-save-connected-app-monitoring-settings';
import {useFetchConnectedAppMonitoringSettings} from '../../hooks/use-fetch-connected-app-monitoring-settings';
import {MonitoringSettings} from '../../../model/Apps/monitoring-settings';
import {ConnectedAppErrorMonitoring} from './ErrorMonitoring/ConnectedAppErrorMonitoring';
import isGrantedOnProduct from '../../is-granted-on-product';
import isGrantedOnCatalog from '../../is-granted-on-catalog';
import {CatalogList} from '@akeneo-pim-community/catalogs';
import styled from 'styled-components';
import {OpenAppButton} from './OpenAppButton';

const ConnectedAppCatalogList = styled.div`
    margin-top: 10px;
`;

type Props = {
    connectedApp: ConnectedApp;
};

const settingsTabName = '#connected-app-tab-settings';
const permissionsTabName = '#connected-app-tab-permissions';
const catalogsTabName = '#connected-app-tab-catalogs';
const errorMonitoringTabName = '#connected-app-tab-error-monitoring';

export const ConnectedAppContainer: FC<Props> = ({connectedApp}) => {
    const history = useHistory();
    const translate = useTranslate();
    const generateUrl = useRouter();
    const notify = useNotify();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const [providers, permissions, setPermissions] = usePermissionsFormProviders(connectedApp.user_group_name);
    const [hasUnsavedChanges, setHasUnsavedChanges] = useState<boolean>(false);
    const fetchConnectedAppMonitoringSettings = useFetchConnectedAppMonitoringSettings(connectedApp.connection_code);
    const saveConnectedAppMonitoringSettings = useSaveConnectedAppMonitoringSettings(connectedApp.connection_code);
    const [monitoringSettings, setMonitoringSettings] = useState<MonitoringSettings | null>(null);
    const [activeTab, setActiveTab] = useSessionStorageState(settingsTabName, 'pim_connectedApp_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);
    const featureFlags = useFeatureFlags();

    useEffect(() => {
        fetchConnectedAppMonitoringSettings().then(setMonitoringSettings);
    }, [connectedApp.connection_code]);

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppsListHref}>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
            <Breadcrumb.Step>{connectedApp.name}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const SaveButton = () => {
        return (
            <ApplyButton onClick={handleSave} disabled={!hasUnsavedChanges} classNames={['AknButtonList-item']}>
                <Translate id='pim_common.save' />
            </ApplyButton>
        );
    };

    const FormState = () => {
        return (
            (hasUnsavedChanges && (
                <div className='updated-status'>
                    <span className='AknState'>
                        <Translate id='pim_common.entity_updated' />
                    </span>
                </div>
            )) ||
            null
        );
    };

    const notifyPermissionProviderError = (entity: string): void => {
        notify(
            NotificationLevel.ERROR,
            translate(
                'akeneo_connectivity.connection.connect.connected_apps.edit.flash.save_permissions_error.description'
            ),
            {
                titleMessage: translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.flash.save_permissions_error.title',
                    {
                        entity: entity,
                    }
                ),
            }
        );
    };

    const notifyMonitoringSettingsSaveError = () => {
        notify(
            NotificationLevel.ERROR,
            translate(
                'akeneo_connectivity.connection.connect.connected_apps.edit.flash.monitoring_settings_error.description'
            )
        );
    };

    const handleSave = async () => {
        let hasStillUnsavedChanged = false;
        let hasSavedSomething = false;

        if (null !== monitoringSettings) {
            try {
                await saveConnectedAppMonitoringSettings(monitoringSettings);
                hasSavedSomething = true;
            } catch {
                notifyMonitoringSettingsSaveError();
                hasStillUnsavedChanged = true;
            }
        }

        if (null !== providers) {
            for (const provider of providers) {
                if (false !== permissions[provider.key]) {
                    try {
                        await provider.save(connectedApp.user_group_name, permissions[provider.key]);
                        hasSavedSomething = true;
                    } catch {
                        notifyPermissionProviderError(provider.label);
                        hasStillUnsavedChanged = true;
                    }
                }
            }
        }

        setHasUnsavedChanges(hasStillUnsavedChanged);

        if (hasSavedSomething) {
            notify(
                NotificationLevel.SUCCESS,
                translate('akeneo_connectivity.connection.connect.connected_apps.edit.flash.success')
            );
        }
    };

    const handleSetProviderPermissions = useCallback(
        (providerKey: string, providerPermissions: object) => {
            // early return when the state has not changed
            if (JSON.stringify(permissions[providerKey]) === JSON.stringify(providerPermissions)) {
                return;
            }

            setPermissions(state => ({...state, [providerKey]: providerPermissions}));
            setHasUnsavedChanges(true);
        },
        [setPermissions, setHasUnsavedChanges, permissions]
    );

    const handleSetMonitoringSettings = useCallback(
        (newMonitoringSettings: MonitoringSettings) => {
            if (JSON.stringify(newMonitoringSettings) === JSON.stringify(monitoringSettings)) {
                return;
            }
            setMonitoringSettings(newMonitoringSettings);
            setHasUnsavedChanges(true);
        },
        [monitoringSettings, setMonitoringSettings, setHasUnsavedChanges]
    );

    const handleCatalogClick = (catalogId: string) => {
        const catalogEditUrl = generateUrl('akeneo_connectivity_connection_connect_connected_apps_catalogs_edit', {
            connectionCode: connectedApp.connection_code,
            catalogId: catalogId,
        });

        history.push(catalogEditUrl);
    };

    const isAtLeastGrantedToViewProducts = isGrantedOnProduct(connectedApp, 'view');
    const isAtLeastGrantedToViewCatalogs = isGrantedOnCatalog(connectedApp, 'view');
    const supportsPermissions = true === featureFlags.isEnabled('connect_app_with_permissions');

    return (
        <>
            <PageHeader
                breadcrumb={breadcrumb}
                buttons={[
                    <SecondaryActionsDropdownButton key={0}>
                        <DropdownLink
                            onClick={() => {
                                history.push(`/connect/connected-apps/${connectedApp.connection_code}/delete`);
                            }}
                        >
                            <Translate id='pim_common.delete' />
                        </DropdownLink>
                    </SecondaryActionsDropdownButton>,
                    <OpenAppButton connectedApp={connectedApp} key={2} />,
                    <SaveButton key={1} />,
                ]}
                userButtons={<UserButtons />}
                state={<FormState />}
                imageSrc={connectedApp.logo ?? undefined}
                imageIllustration={connectedApp.logo ? undefined : <AppIllustration />}
            >
                {connectedApp.name}
            </PageHeader>

            <PageContent pageHeaderHeight={202}>
                <TabBar moreButtonTitle='More'>
                    <TabBar.Tab
                        isActive={isCurrent(settingsTabName)}
                        onClick={() => {
                            setActiveTab(settingsTabName);
                            switchTo(settingsTabName);
                        }}
                    >
                        {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')}
                    </TabBar.Tab>
                    {null !== providers && providers.length > 0 && isAtLeastGrantedToViewProducts && true === supportsPermissions && (
                        <TabBar.Tab
                            isActive={isCurrent(permissionsTabName)}
                            onClick={() => {
                                setActiveTab(permissionsTabName);
                                switchTo(permissionsTabName);
                            }}
                        >
                            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')}
                        </TabBar.Tab>
                    )}
                    {isAtLeastGrantedToViewCatalogs && (
                        <TabBar.Tab
                            isActive={isCurrent(catalogsTabName)}
                            onClick={() => {
                                setActiveTab(catalogsTabName);
                                switchTo(catalogsTabName);
                            }}
                        >
                            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.catalogs')}
                        </TabBar.Tab>
                    )}
                    <TabBar.Tab
                        isActive={isCurrent(errorMonitoringTabName)}
                        onClick={() => {
                            setActiveTab(errorMonitoringTabName);
                            switchTo(errorMonitoringTabName);
                        }}
                    >
                        {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.error_monitoring')}
                    </TabBar.Tab>
                </TabBar>
                {isCurrent(settingsTabName) && connectedApp.is_pending && (
                    <Helper icon={<DangerIcon />} level='warning'>
                        {translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.pending')}
                    </Helper>
                )}
                {isCurrent(settingsTabName) && (
                    <ConnectedAppSettings
                        connectedApp={connectedApp}
                        monitoringSettings={monitoringSettings}
                        handleSetMonitoringSettings={handleSetMonitoringSettings}
                    />
                )}

                {isCurrent(permissionsTabName) && null !== providers && isAtLeastGrantedToViewProducts && (
                    <ConnectedAppPermissions
                        providers={providers}
                        setProviderPermissions={handleSetProviderPermissions}
                        permissions={permissions}
                        onlyDisplayViewPermissions={!isGrantedOnProduct(connectedApp, 'edit')}
                    />
                )}

                {isCurrent(catalogsTabName) && isAtLeastGrantedToViewCatalogs && (
                    <ConnectedAppCatalogList>
                        <CatalogList owner={connectedApp.connection_username} onCatalogClick={handleCatalogClick} />
                    </ConnectedAppCatalogList>
                )}

                {isCurrent(errorMonitoringTabName) && <ConnectedAppErrorMonitoring connectedApp={connectedApp} />}
            </PageContent>
        </>
    );
};
