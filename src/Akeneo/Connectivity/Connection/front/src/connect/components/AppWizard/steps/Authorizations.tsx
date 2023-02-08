import React, {FC} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../../../shared/translate';
import {ScopeListContainer} from '../ScopeListContainer';
import ScopeMessage from '../../../../model/Apps/scope-message';
import {ConsentCheckbox} from './Authentication/ConsentCheckbox';
import {CertificationConsentCheckbox} from './Authorization/CertificationConsentCheckbox';

const InfoContainer = styled.div`
    grid-area: INFO;
    padding: 20px 0 20px 40px;
    border-left: 1px solid ${getColor('brand', 100)};
    height: 570px;
    overflow-y: scroll;
`;

const Connect = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0;
`;

const WrappedConsentCheckbox = styled.div`
    margin-top: 31px;
`;

const WrappedCertificationConsentCheckbox = styled.div`
    margin-top: 20px;
`;

type Props = {
    appName: string;
    appUrl: string | null;
    scopeMessages: ScopeMessage[];
    oldScopeMessages?: ScopeMessage[] | null;
    scopesConsentGiven: boolean;
    setScopesConsent: (newValue: boolean) => void;
    certificationConsentGiven: boolean;
    setCertificationConsent: (newValue: boolean) => void;
    displayCertificationConsent: boolean;
    displayCheckboxConsent: boolean;
};

export const Authorizations: FC<Props> = ({
    appName,
    appUrl,
    scopeMessages,
    oldScopeMessages,
    scopesConsentGiven,
    setScopesConsent,
    certificationConsentGiven,
    setCertificationConsent,
    displayCertificationConsent,
    displayCheckboxConsent,
}) => {
    const translate = useTranslate();

    return (
        <InfoContainer>
            <Connect>{translate('akeneo_connectivity.connection.connect.apps.title')}</Connect>
            <ScopeListContainer appName={appName} scopeMessages={scopeMessages} oldScopeMessages={oldScopeMessages} />
            <WrappedConsentCheckbox>
                <ConsentCheckbox
                    isChecked={scopesConsentGiven}
                    onChange={setScopesConsent}
                    appUrl={appUrl}
                    displayCheckbox={displayCheckboxConsent}
                />
            </WrappedConsentCheckbox>
            {displayCertificationConsent && (
                <WrappedCertificationConsentCheckbox>
                    <CertificationConsentCheckbox
                        isChecked={certificationConsentGiven}
                        onChange={setCertificationConsent}
                    />
                </WrappedCertificationConsentCheckbox>
            )}
        </InfoContainer>
    );
};
