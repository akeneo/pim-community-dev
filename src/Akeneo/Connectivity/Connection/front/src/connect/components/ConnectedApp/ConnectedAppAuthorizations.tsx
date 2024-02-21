import React, {FC, useEffect, useState} from 'react';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {CheckRoundIcon, getColor, getFontSize, Helper, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {useFeatureFlags} from '../../../shared/feature-flags';
import {useFetchConnectedAppScopeMessages} from '../../hooks/use-fetch-connected-app-scope-messages';
import ScopeMessage from '../../../model/Apps/scope-message';
import {ConnectedAppScopeListIsLoading} from './ConnectedAppScopeListIsLoading';
import {ScopeList} from '../ScopeList';
import styled from 'styled-components';
import isGrantedOnProduct from '../../is-granted-on-product';

const ScopeListContainer = styled.div`
    margin: 10px 20px 20px;
`;

const NoScope = styled.div`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    line-height: 21px;
    margin-bottom: 13px;
    display: flex;
    align-items: center;

    & > svg {
        margin-right: 10px;
        color: ${getColor('grey', 100)};
    }
`;

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppAuthorizations: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const featureFlag = useFeatureFlags();
    const fetchConnectedAppScopeMessages = useFetchConnectedAppScopeMessages(connectedApp.connection_code);
    const [connectedAppScopeMessages, setConnectedAppScopeMessages] = useState<ScopeMessage[] | null | false>(null);

    useEffect(() => {
        if (!featureFlag.isEnabled('marketplace_activate')) {
            setConnectedAppScopeMessages(false);
            return;
        }

        fetchConnectedAppScopeMessages()
            .then(setConnectedAppScopeMessages)
            .catch(() => setConnectedAppScopeMessages(false));
    }, [fetchConnectedAppScopeMessages]);

    const informationLinkAnchor = translate(
        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information_link_anchor'
    );
    const isNotAllowedToViewProducts = !isGrantedOnProduct(connectedApp, 'view');

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    {translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.title'
                    )}
                </SectionTitle.Title>
            </SectionTitle>
            <Helper level='info'>
                {isNotAllowedToViewProducts && (
                    <span>
                        {translate(
                            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_access_to_product_information'
                        )}
                        &nbsp;
                    </span>
                )}
                <div
                    dangerouslySetInnerHTML={{
                        __html: translate(
                            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information',
                            {
                                link: `<a href='https://help.akeneo.com/pim/serenity/articles/how-to-connect-my-pim-with-apps.html#all-editions-authorization-step' target='_blank'>${informationLinkAnchor}</a>`, // eslint-disable-line max-len
                            }
                        ),
                    }}
                />
            </Helper>

            {null === connectedAppScopeMessages && <ConnectedAppScopeListIsLoading />}
            {false !== connectedAppScopeMessages && null !== connectedAppScopeMessages && (
                <ScopeListContainer>
                    {0 === connectedAppScopeMessages.length ? (
                        <NoScope>
                            <CheckRoundIcon size={24} />
                            {translate(
                                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope'
                            )}
                        </NoScope>
                    ) : (
                        <ScopeList scopeMessages={connectedAppScopeMessages} itemFontSize='default' />
                    )}
                </ScopeListContainer>
            )}
        </>
    );
};
