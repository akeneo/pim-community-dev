import React, {FC} from 'react';
import {CustomApp, CustomApps} from '../../../model/app';
import {Section} from '../Section';
import {useTranslate} from '../../../shared/translate';
import {CustomAppCard} from './CustomAppCard';
import {ActivateAppButton} from '../ActivateAppButton';
import {useSecurity} from '../../../shared/security';

interface Props {
    customApps: CustomApps;
    isLimitReached: boolean;
}

export const CustomAppList: FC<Props> = ({customApps, isLimitReached}) => {
    const security = useSecurity();
    const translate = useTranslate();

    if (customApps.total <= 0) {
        return null;
    }

    const customAppsList = customApps.apps.map((customApp: CustomApp) => (
        <CustomAppCard
            key={customApp.id}
            customApp={customApp}
            additionalActions={[
                <ActivateAppButton
                    key={1}
                    id={customApp.id}
                    isConnected={customApp.connected}
                    isDisabled={!security.isGranted('akeneo_connectivity_connection_manage_apps') || isLimitReached}
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
            warningMessage={
                !isLimitReached
                    ? null
                    : translate('akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached')
            }
        >
            {customAppsList}
        </Section>
    );
};
