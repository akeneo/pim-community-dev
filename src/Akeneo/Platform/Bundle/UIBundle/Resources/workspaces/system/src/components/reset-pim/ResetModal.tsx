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

const STEPS = ['summary', 'confirm'];

const Footer = styled.div`
  background-color: ${getColor('white')};
  position: fixed;
  width: 100%;
  bottom: 30px;
  left: 0;
`;

type ResetModalProps = {
  canConfirm: boolean;
  onConfirm: () => void;
  onCancel: () => void;
};

const ResetModal = ({canConfirm, onConfirm, onCancel}: ResetModalProps) => {
  const translate = useTranslate();
  const [isCurrentStep, nextStep, previousStep] = useProgress(STEPS);
  const [confirmationWord, setConfirmationWord] = useState<string>('');

  canConfirm = canConfirm && confirmationWord === translate('pim_system.reset_pim.modal.confirmation_word');

  const handleConfirm = () => {
    if (!canConfirm) {
      return;
    }

    onConfirm();
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<DeleteIllustration />}>
      {isCurrentStep('summary') && (
        <>
          <Modal.TopRightButtons>
            <Button onClick={nextStep}>{translate('pim_common.next')}</Button>
          </Modal.TopRightButtons>
          <Modal.SectionTitle color="brand">{translate('pim_system.system_navigation')}</Modal.SectionTitle>
          <Modal.Title>{translate('pim_system.reset_pim.modal.summary.title')}</Modal.Title>
          <Section>
            {translate('pim_system.reset_pim.modal.summary.text')}
            <Tiles inline={true}>
              <Tile selected={true}>
                {translate('pim_system.reset_pim.modal.summary.users.name')}
                <Tooltip direction="bottom">{translate('pim_system.reset_pim.modal.summary.users.tooltip')}</Tooltip>
              </Tile>
              <Tile selected={true}>
                {translate('pim_system.reset_pim.modal.summary.user_groups.name')}
                <Tooltip direction="bottom">
                  {translate('pim_system.reset_pim.modal.summary.user_groups.tooltip')}
                </Tooltip>
              </Tile>
              <Tile selected={true}>
                {translate('pim_system.reset_pim.modal.summary.roles.name')}
                <Tooltip direction="bottom">{translate('pim_system.reset_pim.modal.summary.roles.tooltip')}</Tooltip>
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
          <Modal.Title>{translate('pim_system.reset_pim.modal.confirm.title')}</Modal.Title>
          <Section>
            <Helper level="error">
              <b>{translate('pim_system.reset_pim.modal.confirm.helper.emphasis')}</b>&nbsp;
              {translate('pim_system.reset_pim.modal.confirm.helper.text')}
            </Helper>
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
            <Button level="tertiary" onClick={onCancel}>
              {translate('pim_common.cancel')}
            </Button>
            <Button level="danger" disabled={!canConfirm} onClick={handleConfirm}>
              {translate('pim_system.reset_pim.button.confirm')}
            </Button>
          </Modal.BottomButtons>
        </>
      )}
      <Footer>
        <ProgressIndicator>
          {STEPS.map(step => (
            <ProgressIndicator.Step key={step} current={isCurrentStep(step)}>
              {translate(`pim_system.reset_pim.modal.${step}.name`)}
            </ProgressIndicator.Step>
          ))}
        </ProgressIndicator>
      </Footer>
    </Modal>
  );
};

export {ResetModal};
