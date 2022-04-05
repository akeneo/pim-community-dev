import React, {FC} from 'react';
import styled from 'styled-components';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {ConnectedAppScopeListIsLoading} from './ConnectedAppScopeListIsLoading';
import {ConsentList} from '../AppWizard/steps/Authentication/ConsentList';
import {useAuthenticationScopes} from '../../hooks/use-connected-app-authentication-scopes';

const ConsentListContainer = styled.div`
    margin: 10px 20px;
`;

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppAuthentication: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const {isLoading, authenticationScopes} = useAuthenticationScopes(connectedApp.connection_code);

    const authenticationScopesExists = false === isLoading && 0 !== authenticationScopes.length;
    if (false === authenticationScopesExists) {
        return null;
    }

    const informationLinkAnchor = translate(
        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.information_link_anchor'
    );

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.title'
                    )}
                </SectionTitle.Title>
            </SectionTitle>
            <Helper level='info'>
                <div
                    dangerouslySetInnerHTML={{
                        __html: translate(
                            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.information',
                            {
                                link: `<a href='https://help.akeneo.com/pim/serenity/articles/how-to-connect-my-pim-with-apps.html#grant-authorization-to-your-app' target='_blank'>${informationLinkAnchor}</a>`, // eslint-disable-line max-len
                            }
                        ),
                    }}
                />
            </Helper>
            {isLoading && <ConnectedAppScopeListIsLoading />}
            {authenticationScopesExists && (
                <ConsentListContainer>
                    <ConsentList scopes={authenticationScopes} viewMode={'settings'} />
                </ConsentListContainer>
            )}
        </>
    );
};
