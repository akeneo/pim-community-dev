import React, {FC} from 'react';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useTranslate} from '../../../shared/translate';
import styled from 'styled-components';
import {DangerIcon, getColor, getFontSize, useTheme} from 'akeneo-design-system';

const Warning = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('small')};
    font-weight: normal;
    margin: 0;
    margin-bottom: 5px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; ;
`;

const WarningIcon = styled(DangerIcon)`
    color: ${getColor('yellow', 100)};
    vertical-align: middle;
    margin-right: 5px;
`;

const Error = styled.div`
    color: ${getColor('red', 100)};
    font-size: ${getFontSize('small')};
    font-weight: normal;
    margin: 0;
    margin-bottom: 5px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; ;
`;

const ErrorIcon = styled(DangerIcon)`
    color: ${getColor('red', 100)};
    vertical-align: middle;
    margin-right: 5px;
`;

const Tag = styled.span`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('small')};
    text-transform: uppercase;
    font-weight: normal;

    border: 1px ${getColor('grey', 120)} solid;
    background: ${getColor('white')};
    border-radius: 2px;

    display: inline-block;
    line-height: ${getFontSize('small')};
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    padding: 2px 5px;
    margin-right: 5px;
`;

const Author = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('big')};
    font-weight: normal;
    margin: 0;
    margin-bottom: 5px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const IconBox = styled.span`
    vertical-align: middle;
    margin-right: 5px;
`;

type Props = {
    connectedApp: ConnectedApp;
};

const ConnectedAppCardDescription: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const theme = useTheme();
    const author =
        connectedApp.author ??
        translate('akeneo_connectivity.connection.connect.connected_apps.list.custom_apps.removed_user');

    if (true !== connectedApp.is_loaded) {
        return null;
    }

    if (false === connectedApp.is_listed_on_the_appstore && false === connectedApp.is_custom_app) {
        const message = translate(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.not_listed_on_the_appstore'
        );

        return (
            <Error title={message}>
                <ErrorIcon size={14} />
                {message}
            </Error>
        );
    }

    if (true === connectedApp.is_pending) {
        return (
            <>
                <IconBox>
                    <DangerIcon size={13} color={theme.color.yellow100} />
                </IconBox>
                {translate('akeneo_connectivity.connection.connect.connected_apps.list.card.pending')}
            </>
        );
    }

    if (true === connectedApp.has_outdated_scopes) {
        const message = translate(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.new_access_authorization_required'
        );

        return (
            <>
                <Warning title={message}>
                    <WarningIcon size={14} />
                    {message}
                </Warning>
                {connectedApp.categories.length > 0 && <Tag>{connectedApp.categories[0]}</Tag>}
            </>
        );
    }

    return (
        <>
            <Author>
                {translate('akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by', {
                    author,
                })}
            </Author>
            {connectedApp.categories.length > 0 && <Tag>{connectedApp.categories[0]}</Tag>}
        </>
    );
};

export default ConnectedAppCardDescription;
