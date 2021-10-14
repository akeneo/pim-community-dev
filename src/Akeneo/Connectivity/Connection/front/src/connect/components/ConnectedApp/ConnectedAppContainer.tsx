import React, {FC, SetStateAction, useState} from 'react';
import {Breadcrumb, TabBar, useTabBar} from 'akeneo-design-system';
import {Translate, useTranslate} from '../../../shared/translate';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useRouter} from '../../../shared/router/use-router';
import {useMediaUrlGenerator} from '../../../settings/use-media-url-generator';
import {ApplyButton, PageContent, PageHeader} from '../../../common';
import {UserButtons} from '../../../shared/user';
import {ConnectedAppSettings} from './ConnectedAppSettings';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {ConnectedAppPermissions} from './ConnectedAppPermissions';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {PermissionsByProviderKey} from '../../../model/Apps/permissions-by-provider-key';
import usePermissionsFormProviders from '../../hooks/use-permissions-form-providers';

type Props = {
    connectedApp: ConnectedApp;
};

const settingsTabName = '#connected-app-tab-settings';
const permissionsTabName = '#connected-app-tab-permissions';

export const ConnectedAppContainer: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const generateMediaUrl = useMediaUrlGenerator();
    const [providers, permissions, setPermissions] = usePermissionsFormProviders(connectedApp.user_group_name);
    const [hasUnsavedChanges, setHasUnsavedChanges] = useState<boolean>(false);
    const [activeTab, setActiveTab] = useSessionStorageState(settingsTabName, 'pim_connectedApp_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);
    const notify = useNotify();

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppsListHref}>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
            <Breadcrumb.Step>{connectedApp.name}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const SaveButton = () => {
        return (
            <ApplyButton
                onClick={handleSave}
                disabled={!hasUnsavedChanges}
                classNames={['AknButtonList-item']}
            >
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
            translate('akeneo_connectivity.connection.connect.connected_apps.edit.flash.permissions_error.description'),
            {
                titleMessage: translate('akeneo_connectivity.connection.connect.connected_apps.edit.flash.permissions_error.title', {
                    entity: entity,
                }),
            }
        );
    };

    const handleSave = async () => {
        if (null !== providers) {
            for (const provider of providers) {
                if (false !== permissions[provider.key]) {
                    try {
                        await provider.save(connectedApp.user_group_name, permissions[provider.key]);
                    } catch {
                        notifyPermissionProviderError(provider.label);
                    }
                }
            }
        }

        notify(
            NotificationLevel.SUCCESS,
            translate('akeneo_connectivity.connection.connect.connected_apps.edit.flash.success')
        );
    };

    const handleSetPermissions = (state: SetStateAction<PermissionsByProviderKey>) => {
        setPermissions(state);
        setHasUnsavedChanges(true);
    }

    return (
        <>
            <PageHeader
                breadcrumb={breadcrumb}
                buttons={[<SaveButton />]}
                userButtons={<UserButtons />}
                state={<FormState />}
                imageSrc={generateMediaUrl(connectedApp.logo, 'thumbnail')}
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
                        setPermissions={handleSetPermissions}
                        permissions={permissions}
                    />
                )}
            </PageContent>
        </>
    );
};
