import React, {FC} from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {ConnectedAppCard} from './ConnectedAppCard';
import {useTranslate} from '../../../shared/translate';
import {CardGrid} from '../Section';

type Props = {
    connectedCustomApps: ConnectedApp[];
};

export const ConnectedCustomAppList: FC<Props> = ({connectedCustomApps}) => {
    const translate = useTranslate();

    if (connectedCustomApps.length === 0) {
        return null;
    }

    const connectedAppCards = connectedCustomApps.map((connectedApp: ConnectedApp) => (
        <ConnectedAppCard key={connectedApp.id} item={connectedApp} />
    ));

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.list.custom_apps.title')}
                </SectionTitle.Title>
                <SectionTitle.Spacer />
                <SectionTitle.Information>
                    {translate(
                        'akeneo_connectivity.connection.connect.connected_apps.list.apps.total',
                        {
                            total: connectedCustomApps.length.toString(),
                        },
                        connectedCustomApps.length
                    )}
                </SectionTitle.Information>
            </SectionTitle>
            <CardGrid>{connectedAppCards}</CardGrid>
        </>
    );
};
