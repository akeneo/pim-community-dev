import React, {useState} from 'react';
import styled from 'styled-components';
import {
  Button,
  DeleteIllustration,
  Helper,
  Modal,
  ProgressIndicator,
  Tile,
  Tiles,
  Tooltip,
  getColor,
  useProgress,
} from 'akeneo-design-system';
import {Section, TextField, useTranslate} from '@akeneo-pim-community/shared';

const STEPS = ['impact', 'confirm'];

const Footer = styled.div`
  background-color: ${getColor('white')};
  position: fixed;
  width: 100%;
  bottom: 30px;
  left: 0;
`;

type ResetModalProps = {
  onConfirm: () => void;
  onCancel: () => void;
};

const ResetModal = ({onConfirm, onCancel}: ResetModalProps) => {
  const translate = useTranslate();
  const [isCurrentStep, nextStep, previousStep] = useProgress(STEPS);
  const [confirmationWord, setConfirmationWord] = useState<string>('');

  const handleMoveToConfirmStep = () => {
    nextStep();
  };

  const handleCancel = () => {
    onCancel();
  };

  const handleConfirm = () => {
    onConfirm();
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={handleCancel} illustration={<DeleteIllustration />}>
      {isCurrentStep('impact') && (
        <>
          <Modal.TopRightButtons>
            <Button onClick={handleMoveToConfirmStep}>{translate('pim_common.next')}</Button>
          </Modal.TopRightButtons>
          <Modal.SectionTitle color="brand">{translate('pim_system.system_navigation')}</Modal.SectionTitle>
          <Modal.Title>{translate('pim_system.reset_pim.modal.steps.impact.title')}</Modal.Title>
          <Section>
            {translate('pim_system.reset_pim.modal.steps.impact.text')}
            <Tiles inline={true}>
              <Tile selected={true}>
                {translate('pim_system.reset_pim.modal.steps.impact.users.name')}
                <Tooltip direction="bottom">
                  {translate('pim_system.reset_pim.modal.steps.impact.users.tooltip')}
                </Tooltip>
              </Tile>
              <Tile selected={true}>
                {translate('pim_system.reset_pim.modal.steps.impact.user_groups.name')}
                <Tooltip direction="bottom">
                  {translate('pim_system.reset_pim.modal.steps.impact.user_groups.tooltip')}
                </Tooltip>
              </Tile>
              <Tile selected={true}>
                {translate('pim_system.reset_pim.modal.steps.impact.roles.name')}
                <Tooltip direction="bottom">
                  {translate('pim_system.reset_pim.modal.steps.impact.roles.tooltip')}
                </Tooltip>
              </Tile>
            </Tiles>
          </Section>
        </>
      )}
      {isCurrentStep('confirm') && (
        <>
          <Modal.TopLeftButtons>
            <Button onClick={previousStep}>{translate('pim_common.previous')}</Button>
          </Modal.TopLeftButtons>
          <Modal.SectionTitle color="brand">{translate('pim_system.system_navigation')}</Modal.SectionTitle>
          <Modal.Title>{translate('pim_system.reset_pim.modal.steps.confirm.title')}</Modal.Title>
          <Section>
            <Helper level="error">{translate('pim_system.reset_pim.modal.steps.confirm.helper')}</Helper>
            <TextField
              value={confirmationWord}
              label={translate('pim_system.reset_pim.modal.confirmation_phrase', {
                confirmation_word: translate('pim_system.reset_pim.modal.confirmation_word'),
              })}
              onChange={setConfirmationWord}
              onSubmit={handleConfirm}
            />
          </Section>
          <Modal.BottomButtons>
            <Button level="tertiary" onClick={handleCancel}>
              {translate('pim_common.cancel')}
            </Button>
            <Button
              level="danger"
              disabled={confirmationWord !== translate('pim_system.reset_pim.modal.confirmation_word')}
              onClick={handleConfirm}
            >
              {translate('pim_system.reset_pim.button.confirm')}
            </Button>
          </Modal.BottomButtons>
        </>
      )}
      <Footer>
        <ProgressIndicator>
          {STEPS.map(step => (
            <ProgressIndicator.Step key={step} current={isCurrentStep(step)}>
              {translate(`pim_system.reset_pim.modal.steps.${step}.name`)}
            </ProgressIndicator.Step>
          ))}
        </ProgressIndicator>
      </Footer>
    </Modal>
  );
};

export {ResetModal};
