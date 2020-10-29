import React from 'react';
import styled from 'styled-components';
import {Button, ExportIllustration, Helper, Modal, SectionTitle, Title} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useToggleState} from '@akeneo-pim-community/shared';

const ActionsContainer = styled.div`
  display: flex;
  gap: 10px;
  justify-content: flex-end;
`;

type ActionsProps = {
  id: string;
  jobLabel: string;
  isStoppable: boolean;
  refreshCollection: () => void;
};

const Actions = ({id, jobLabel, isStoppable, refreshCollection}: ActionsProps) => {
  const translate = useTranslate();
  const showRoute = useRoute('pim_enrich_job_tracker_show', {id});
  const stopRoute = useRoute('pim_enrich_job_tracker_rest_stop', {id});
  const [isConfirmOpen, openConfirm, closeConfirm] = useToggleState(false);

  const handleStop = async () => {
    closeConfirm();
    await fetch(stopRoute);
    refreshCollection();
  };

  return (
    <ActionsContainer>
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
      <Button size="small" ghost={true} level="tertiary" href={`#${showRoute}`}>
        {translate('pim_datagrid.action.show.title')}
      </Button>
      {isStoppable && (
        <Button size="small" ghost={true} level="danger" onClick={openConfirm}>
          {translate('pim_datagrid.action.stop.title')}
        </Button>
      )}
    </ActionsContainer>
  );
};

export default Actions;
