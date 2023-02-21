import React, {FC} from 'react';
import styled from 'styled-components';
import {AppIllustration, Button, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useRouter} from '../../../shared/router/use-router';
import {useSecurity} from '../../../shared/security';
import ConnectedAppCardDescription from './ConnectedAppCardDescription';

const Grid = styled.section`
    margin: 20px 0;
    display: grid;
    grid-template-columns: repeat(2, minmax(300px, 1fr));
    gap: 20px;
`;

const CardContainer = styled.div`
    padding: 20px;
    border: 1px ${getColor('grey', 40)} solid;
    display: grid;
    gap: 0 20px;
    grid-template-columns: 100px 1fr 1px; /* 1px column only for ellipsis working */
    grid-template-rows: 75px 25px;
    grid-template-areas:
        'logo text text'
        'logo actions actions';
`;

const LogoContainer = styled.div`
    width: 100px;
    height: 100px;
    grid-area: logo;
    border: 1px ${getColor('grey', 40)} solid;
    display: flex;
`;

const Logo = styled.img`
    margin: auto;
    max-height: 98px;
    max-width: 98px;
`;

const TextInformation = styled.div`
    grid-area: text;
    max-width: 100%;
`;

const Name = styled.div`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
    font-weight: bold;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const Actions = styled.div`
    grid-area: actions;
    justify-self: end;
    align-self: end;

    & > * {
        margin-left: 10px;
    }
`;

type Props = {
    item: ConnectedApp;
};

const ConnectedAppCard: FC<Props> = ({item}) => {
    const translate = useTranslate();
    const security = useSecurity();
    const generateUrl = useRouter();
    const manageConnectedAppUrl = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps_edit', {
        connectionCode: item.connection_code,
    })}`;
    const openConnectedAppUrl = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps_open', {
        connectionCode: item.connection_code,
    })}`;
    const logo = item.logo ? <Logo src={item.logo} alt={item.name} /> : <AppIllustration width={100} height={100} />;

    const canManageApp = security.isGranted('akeneo_connectivity_connection_manage_apps');

    const canOpenApp = security.isGranted('akeneo_connectivity_connection_open_apps');

    return (
        <CardContainer>
            <LogoContainer> {logo} </LogoContainer>
            <TextInformation>
                <Name>{item.name}</Name>
                <ConnectedAppCardDescription connectedApp={item} />
            </TextInformation>
            <Actions>
                <Button ghost level='tertiary' href={manageConnectedAppUrl} disabled={!canManageApp}>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.list.card.manage_app')}
                </Button>
                <Button
                    level={item.is_pending || item.has_outdated_scopes ? 'warning' : 'secondary'}
                    href={openConnectedAppUrl}
                    disabled={!item.activate_url || !canOpenApp}
                    target='_blank'
                >
                    {translate('akeneo_connectivity.connection.connect.connected_apps.list.card.open_app')}
                </Button>
            </Actions>
        </CardContainer>
    );
};

export {ConnectedAppCard, Grid};
