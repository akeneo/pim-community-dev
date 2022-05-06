import {AppIllustration, Button, Modal, ProgressIndicator} from 'akeneo-design-system';
import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../shared/translate';
import {useStepProgress} from './useStepProgress';
import getStepConfirmationLabel from './getStepConfirmationLabel';

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

export type Step = {
    name: 'authentication' | 'authorizations' | 'permissions' | 'summary';
    requires_explicit_approval: boolean;
};

type Props = {
    appLogo: string | null;
    appName: string;
    children: (currentStep: Step) => React.ReactNode;
    onClose: () => void;
    onConfirm: () => void;
    steps: Array<Step>;
    maxAllowedStep?: Step['name'] | null;
};

export const WizardModal: FC<Props> = ({
    appLogo,
    appName,
    children,
    onClose,
    onConfirm,
    steps,
    maxAllowedStep = null,
}) => {
    const translate = useTranslate();

    const {current, isFirst, isLast, next, previous} = useStepProgress(steps);

    const confirmLabel = getStepConfirmationLabel(current, isFirst, isLast);

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
                    <Button onClick={isLast ? onConfirm : next} disabled={current.name === maxAllowedStep}>
                        {translate(confirmLabel)}
                    </Button>
                </Modal.TopRightButtons>
            )}

            <Content>
                <LogoContainer>
                    {appLogo ? <Logo src={appLogo} alt={appName} /> : <AppIllustration size={220} />}
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
                    <Button onClick={onConfirm} disabled={current.name === maxAllowedStep}>
                        {translate(confirmLabel)}
                    </Button>
                </Modal.BottomButtons>
            )}
        </Modal>
    );
};
