import React, {FC, ReactNode} from 'react';
import styled from 'styled-components';
import {CustomApp} from '../../../model/app';
import {getColor, getFontSize, DeleteIcon, IconButton} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {useHistory} from 'react-router';
import {useRouter} from '../../../shared/router/use-router';
import {useSecurity} from '../../../shared/security';

const CardContainer = styled.div`
    padding: 20px;
    border: 1px ${getColor('grey', 40)} solid;
    display: grid;
    gap: 0 20px;
    grid-template-columns: 100px 1fr 24px;
    grid-template-rows: 1fr 50px;
    grid-template-areas:
        'logo text delete'
        'logo actions actions';
`;

const LogoContainer = styled.div`
    width: 100px;
    height: 100px;
    grid-area: logo;
    border: 1px ${getColor('grey', 40)} solid;
    display: flex;
`;

const TextInformation = styled.div`
    grid-area: text;
    max-width: 100%;
`;

const Name = styled.h1`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
    font-weight: bold;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const Author = styled.h3`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('big')};
    font-weight: normal;
    margin: 0;
    margin-bottom: 5px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const Actions = styled.div`
    grid-area: actions;
    justify-self: end;
    align-self: end;
    text-align: right;

    & > * {
        margin-left: 10px;
    }
`;

type Props = {
    customApp: CustomApp;
    additionalActions?: ReactNode[];
};

export const CustomAppCard: FC<Props> = ({customApp, additionalActions}) => {
    const security = useSecurity();
    const translate = useTranslate();
    const generateUrl = useRouter();
    const history = useHistory();

    const author =
        customApp.author ?? translate('akeneo_connectivity.connection.connect.marketplace.custom_apps.removed_user');

    const onDelete = () => {
        history.push(
            generateUrl('akeneo_connectivity_connection_connect_custom_apps_delete', {
                customAppId: customApp.id,
            })
        );
    };

    return (
        <CardContainer>
            <LogoContainer>
                <img
                    src='/bundles/akeneoconnectivityconnection/img/app-illustration.png'
                    alt='App Illustration'
                    width={100}
                    height={100}
                />
            </LogoContainer>
            <TextInformation>
                <Name>{customApp.name}</Name>
                <Author>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.developed_by', {author})}
                </Author>
            </TextInformation>
            {security.isGranted('akeneo_connectivity_connection_manage_test_apps') && (
                <IconButton
                    ghost='borderless'
                    icon={<DeleteIcon />}
                    level='tertiary'
                    onClick={onDelete}
                    title={translate('pim_common.delete')}
                />
            )}
            <Actions>{additionalActions}</Actions>
        </CardContainer>
    );
};
