import styled from 'styled-components';
import {getColor, getFontSize} from 'akeneo-design-system';
import React, {FC, PropsWithChildren} from 'react';
import {useTranslate} from '../../../shared/translate';
import {ScopeListContainer} from './ScopeListContainer';

const InfoContainer = styled.div`
    grid-area: INFO;
    padding: 20px 0 20px 40px;
    border-left: 1px solid ${getColor('brand', 100)};
`;

const Connect = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0;
`;

type ScopeMessages = {
    icon: string;
    type: string;
    entities: string;
};

type Props = {
    appName: string;
    scopeMessages: ScopeMessages[];
};

export const Authorizations: FC<Props & PropsWithChildren<{}>> = ({appName, scopeMessages, children}) => {
    const translate = useTranslate();

    return (
        <InfoContainer>
            <Connect>{translate('akeneo_connectivity.connection.connect.apps.title')}</Connect>
            <ScopeListContainer appName={appName} scopeMessages={scopeMessages} />
            {children}
        </InfoContainer>
    );
};
