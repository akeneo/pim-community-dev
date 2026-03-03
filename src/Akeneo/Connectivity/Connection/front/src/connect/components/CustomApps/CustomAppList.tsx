import React, {FC} from 'react';
import {CustomApp, CustomApps} from '../../../model/app';
import {Section} from '../Section';
import {useTranslate} from '../../../shared/translate';
import {CustomAppCard} from './CustomAppCard';
import {ActivateAppButton} from '../ActivateAppButton';
import {useSecurity} from '../../../shared/security';
import {useCustomAppsLimitReached} from '../../hooks/use-custom-apps-limit-reached';

interface Props {
    customApps: CustomApps;
    isConnectLimitReached: boolean;
}

export const CustomAppList: FC<Props> = ({customApps, isConnectLimitReached}) => {
    const security = useSecurity();
    const translate = useTranslate();
    const {data: isCreateLimitReached} = useCustomAppsLimitReached();

    const warningMessages = [];
    if (isConnectLimitReached) {
        warningMessages.push(
            translate('akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached')
        );
    }
    if (isCreateLimitReached) {
        warningMessages.push(translate('akeneo_connectivity.connection.connect.custom_apps.creation_limit_reached'));
    }
    const warningMessage = warningMessages.length === 0 ? null : warningMessages.join('\r\n');

    const customAppsList = customApps.apps.map((customApp: CustomApp) => (
        <CustomAppCard
            key={customApp.id}
            customApp={customApp}
            additionalActions={[
                <ActivateAppButton
                    key={1}
                    id={customApp.id}
                    isConnected={customApp.connected}
                    isDisabled={
                        !security.isGranted('akeneo_connectivity_connection_manage_apps') || isConnectLimitReached
                    }
                    isPending={false}
                />,
            ]}
        />
    ));

    return (
        <Section
            title={translate('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')}
            information={translate(
                'akeneo_connectivity.connection.connect.marketplace.apps.total',
                {
                    total: customApps.total.toString(),
                },
                customApps.total
            )}
            emptyMessage={translate('akeneo_connectivity.connection.connect.marketplace.apps.empty')}
            warningMessage={warningMessage}
        >
            {customAppsList}
        </Section>
    );
};
