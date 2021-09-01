import React, {FC, useEffect, useState} from 'react';
import {useHistory} from 'react-router';
import {Button, Modal, useProgress, ProgressIndicator} from 'akeneo-design-system';
import styled from 'styled-components';
import {Permissions} from './Permissions';
import {Authorizations} from './Authorizations';
import {AppWizardData} from '../../../model/Apps/wizard-data';
import {useFetchAppWizardData} from '../../hooks/use-fetch-app-wizard-data';
import {useTranslate} from '../../../shared/translate';

const Content = styled.div`
    display: grid;
    grid-template-columns: 260px 593px;
    grid-template-areas: 'LOGO INFO';
`;
const LogoContainer = styled.div`
    grid-area: LOGO;
    padding-right: 40px;
`;
const Logo = styled.img`
    width: 220px;
    height: 220px;
`;

const AllowAndNextButton = styled(Button)`
    position: fixed;
    top: 40px;
    right: 40px;
`;
const PreviousButton = styled(Button)`
    position: fixed;
    top: 40px;
    left: 80px;
`;
const ProgressIndicatorContainer = styled(ProgressIndicator)`
    width: 456px;
    height: 70px;
    position: fixed;
    bottom: 20px;
`;

interface Props {
    clientId: string;
}

export const AppWizardWithSteps: FC<Props> = ({clientId}) => {
    const translate = useTranslate();
    const history = useHistory();
    const [wizardData, setWizardData] = useState<AppWizardData | null>(null);
    const fetchWizardData = useFetchAppWizardData(clientId);
    const steps: string[] = [
        'authorizations',
        'permission',
        'well_done',
    ];
    const [isCurrent, next, previous] = useProgress(steps);
    useEffect(() => {
        fetchWizardData().then(setWizardData);
    }, [fetchWizardData]);

    const redirectToMarketplace = () => {
        history.push('/connect/marketplace');
    };

    if (wizardData === null) {
        return null;
    }

    return (
        <Modal
            onClose={redirectToMarketplace}
            closeTitle={translate('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')}
        >
            {
                !isCurrent('authorizations') &&
                <PreviousButton level={'tertiary'} onClick={previous}>
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.action.previous')}
                </PreviousButton>
            }
            <AllowAndNextButton onClick={next}>
                {translate('akeneo_connectivity.connection.connect.apps.wizard.action.allow_and_next')}
            </AllowAndNextButton>

            <Content>
                <LogoContainer>
                    <Logo src={wizardData.appLogo} alt={wizardData.appName} />
                </LogoContainer>
                {
                    isCurrent('authorizations') &&
                    <Authorizations
                        appName={wizardData.appName}
                        scopeMessages={wizardData.scopeMessages}
                    />
                }
                {
                    isCurrent('permission') && <Permissions />
                }
            </Content>

            <ProgressIndicatorContainer>
                {steps.map(step =>
                    <ProgressIndicator.Step key={step} current={isCurrent(step)}>
                        {translate(`akeneo_connectivity.connection.connect.apps.wizard.progress.${step}`)}
                    </ProgressIndicator.Step>
                )}
            </ProgressIndicatorContainer>
        </Modal>
    );
};
