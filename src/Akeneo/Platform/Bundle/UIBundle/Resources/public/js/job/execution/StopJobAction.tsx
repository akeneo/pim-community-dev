import React from 'react';
import {useRoute, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button, ButtonProps, ExportIllustration, Helper, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {useToggleState} from '@akeneo-pim-community/shared';

type StopJobActionProps = {
  id: string;
  jobLabel: string;
  isStoppable: boolean;
  onStop: () => void;
} & ButtonProps;

const StopJobAction = ({id, jobLabel, isStoppable, onStop, children, ...rest}: StopJobActionProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const stopRoute = useRoute('pim_enrich_job_tracker_rest_stop', {id});
  const [isConfirmOpen, openConfirm, closeConfirm] = useToggleState(false);

  const handleStop = async () => {
    closeConfirm();
    await fetch(stopRoute);
    onStop();
  };

  if (!isStoppable || !isGranted('pim_importexport_stop_job')) return null;

  return (
    <>
      <Modal isOpen={isConfirmOpen} onClose={closeConfirm} illustration={<ExportIllustration />}>
        <SectionTitle>{translate('pim_title.pim_enrich_job_tracker_index')} /</SectionTitle>
        <Title>{translate('pim_datagrid.action.stop.confirmation.title', {jobLabel})}</Title>
        <Helper level="info">{translate('pim_datagrid.action.stop.confirmation.content')}</Helper>
        <Modal.BottomButtons>
          <Button level="tertiary" onClick={closeConfirm}>
            {translate('pim_common.cancel')}
          </Button>
          <Button level="danger" onClick={handleStop}>
            {translate('pim_datagrid.action.stop.confirmation.ok')}
          </Button>
        </Modal.BottomButtons>
      </Modal>
      <Button onClick={openConfirm} level="danger" {...rest}>
        {translate('pim_datagrid.action.stop.title')}
      </Button>
    </>
  );
};

export {StopJobAction};
