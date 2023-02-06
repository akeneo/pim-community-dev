import React, {FC} from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {ConnectedAppCard} from './ConnectedAppCard';
import {useTranslate} from '../../../shared/translate';
import {CardGrid} from '../Section';

type Props = {
    connectedTestApps: ConnectedApp[];
};

export const ConnectedTestAppList: FC<Props> = ({connectedTestApps}) => {
    const translate = useTranslate();

    if (connectedTestApps.length === 0) {
        return null;
    }

    const connectedAppCards = connectedTestApps.map((connectedApp: ConnectedApp) => (
        <ConnectedAppCard key={connectedApp.id} item={connectedApp} />
    ));

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.list.test_apps.title')}
                </SectionTitle.Title>
                <SectionTitle.Spacer />
                <SectionTitle.Information>
                    {translate(
                        'akeneo_connectivity.connection.connect.connected_apps.list.apps.total',
                        {
                            total: connectedTestApps.length.toString(),
                        },
                        connectedTestApps.length
                    )}
                </SectionTitle.Information>
            </SectionTitle>
            <CardGrid>{connectedAppCards}</CardGrid>
        </>
    );
};
