import React, {FC} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../../../shared/translate';
import {ScopeListContainer} from '../ScopeListContainer';
import ScopeMessage from '../../../../model/Apps/scope-message';

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

type Props = {
    appName: string;
    scopeMessages: ScopeMessage[];
    oldScopeMessages?: ScopeMessage[] | null;
};

export const Authorizations: FC<Props> = ({appName, scopeMessages, oldScopeMessages}) => {
    const translate = useTranslate();

    return (
        <InfoContainer>
            <Connect>{translate('akeneo_connectivity.connection.connect.apps.title')}</Connect>
            <ScopeListContainer appName={appName} scopeMessages={scopeMessages} oldScopeMessages={oldScopeMessages} />
        </InfoContainer>
    );
};
