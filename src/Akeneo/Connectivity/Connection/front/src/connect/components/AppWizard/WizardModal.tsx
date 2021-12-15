import {Button, Modal, ProgressIndicator} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../shared/translate';
import {useStepProgress} from './useStepProgress';

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
    margin: auto;
    max-width: 220px;
    max-height: 220px;
`;

const ProgressIndicatorContainer = styled(ProgressIndicator)`
    width: 456px;
    height: 70px;
    position: fixed;
    bottom: 20px;
`;

type Step = {
    name: 'authentication' | 'authorizations' | 'permissions' | 'summary';
    action: 'next' | 'allow_and_next' | 'confirm' | 'allow_and_finish';
};

type Props = {
    appLogo: string;
    appName: string;
    children: (currentStep: Step) => React.ReactNode;
    onClose: () => void;
    onConfirm: () => void;
    steps: Array<Step>;
};

export const WizardModal = ({appLogo, appName, children, onClose, onConfirm, steps}: Props) => {
    const translate = useTranslate();

    const {current, isFirst, isLast: isLast, next, previous} = useStepProgress(steps);

    const isSingleStepWizard = steps.length === 1;

    return (
        <Modal
            onClose={onClose}
            closeTitle={translate('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')}
        >
            {!isSingleStepWizard && (
                <Modal.TopRightButtons>
                    {!isFirst && (
                        <Button level={'tertiary'} onClick={previous}>
                            {translate('akeneo_connectivity.connection.connect.apps.wizard.action.previous')}
                        </Button>
                    )}
                    <Button onClick={isLast ? onConfirm : next}>
                        {translate(`akeneo_connectivity.connection.connect.apps.wizard.action.${current.action}`)}
                    </Button>
                </Modal.TopRightButtons>
            )}

            <Content>
                <LogoContainer>
                    <Logo src={appLogo} alt={appName} />
                </LogoContainer>
                {children(current)}
            </Content>

            {!isSingleStepWizard && (
                <ProgressIndicatorContainer>
                    {steps.map(step => (
                        <ProgressIndicator.Step key={step.name} current={step.name === current.name}>
                            {translate(`akeneo_connectivity.connection.connect.apps.wizard.progress.${step.name}`)}
                        </ProgressIndicator.Step>
                    ))}
                </ProgressIndicatorContainer>
            )}

            {isSingleStepWizard && (
                <Modal.BottomButtons>
                    <Button level={'tertiary'} onClick={onClose}>
                        {translate('akeneo_connectivity.connection.connect.apps.wizard.action.cancel')}
                    </Button>
                    <Button onClick={onConfirm}>
                        {translate('akeneo_connectivity.connection.connect.apps.wizard.action.confirm')}
                    </Button>
                </Modal.BottomButtons>
            )}
        </Modal>
    );
};
