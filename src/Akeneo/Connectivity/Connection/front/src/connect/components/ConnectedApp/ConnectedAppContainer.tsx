import React, {FC, useCallback, useState} from 'react';
import {Breadcrumb, TabBar, useTabBar} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../../shared/translate';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useRouter} from '../../../shared/router/use-router';
import {ApplyButton, DropdownLink, PageContent, PageHeader, SecondaryActionsDropdownButton} from '../../../common';
import {UserButtons} from '../../../shared/user';
import {ConnectedAppSettings} from './ConnectedAppSettings';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {ConnectedAppPermissions} from './ConnectedAppPermissions';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import usePermissionsFormProviders from '../../hooks/use-permissions-form-providers';
import {useHistory} from 'react-router';

type Props = {
    connectedApp: ConnectedApp;
};

const settingsTabName = '#connected-app-tab-settings';
const permissionsTabName = '#connected-app-tab-permissions';

export const ConnectedAppContainer: FC<Props> = ({connectedApp}) => {
    const history = useHistory();
    const translate = useTranslate();
    const generateUrl = useRouter();
    const notify = useNotify();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const [providers, permissions, setPermissions] = usePermissionsFormProviders(connectedApp.user_group_name);
    const [hasUnsavedChanges, setHasUnsavedChanges] = useState<boolean>(false);
    const [activeTab, setActiveTab] = useSessionStorageState(settingsTabName, 'pim_connectedApp_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);

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

    const handleSave = async () => {
        let hasStillUnsavedChanged = false;
        let hasSavedSomething = false;

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
                    <SaveButton key={0} />,
                ]}
                userButtons={<UserButtons />}
                state={<FormState />}
                imageSrc={connectedApp.logo}
            >
                {connectedApp.name}
            </PageHeader>

            <PageContent>
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
                    {null !== providers && providers.length > 0 && (
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
                </TabBar>

                {isCurrent(settingsTabName) && <ConnectedAppSettings connectedApp={connectedApp} />}

                {isCurrent(permissionsTabName) && null !== providers && (
                    <ConnectedAppPermissions
                        providers={providers}
                        setProviderPermissions={handleSetProviderPermissions}
                        permissions={permissions}
                    />
                )}
            </PageContent>
        </>
    );
};
