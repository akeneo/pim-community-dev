import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, Button, ExportIllustration, Helper} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Modal, ModalCloseButton, useToggleState} from '@akeneo-pim-community/shared';

const ActionsContainer = styled.div`
  display: flex;
  gap: 10px;
  justify-content: flex-end;
`;

//TODO add DSM component
const ModalContent = styled.div`
  display: flex;
  position: relative;
`;

//TODO add DSM component
const TextContent = styled.div`
  display: flex;
  flex-direction: column;
  padding: 20px 0;
`;

//TODO add DSM component
const Separator = styled.div`
  width: 1px;
  height: 100%;
  background-color: ${({theme}: AkeneoThemedProps) => theme.color.purple100};
  margin: 0 40px;
`;

//TODO add DSM component
const Subtitle = styled.div`
  height: 19px;
  color: rgb(148, 82, 186);
  font-size: 16px;
  text-transform: uppercase;
  margin-bottom: 6px;
`;

//TODO add DSM component
const Title = styled.div`
  height: 34px;
  color: rgb(17, 50, 77);
  font-size: 28px;
  margin-bottom: 10px;
`;

//TODO add DSM component
const ButtonsContainer = styled.div`
  display: flex;
  gap: 10px;
  margin-top: 20px;
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
      {isConfirmOpen && (
        <Modal>
          <ModalCloseButton onClick={closeConfirm} />
          <ModalContent>
            <ExportIllustration />
            <Separator />
            <TextContent>
              <Subtitle>{translate('pim_title.pim_enrich_job_tracker_index')} /</Subtitle>
              <Title>{translate('pim_datagrid.action.stop.confirmation.title', {jobLabel})}</Title>
              <Helper level="info">{translate('pim_datagrid.action.stop.confirmation.content')}</Helper>
              <ButtonsContainer>
                <Button level="tertiary" onClick={closeConfirm}>
                  {translate('pim_common.cancel')}
                </Button>
                <Button level="danger" onClick={handleStop}>
                  {translate('pim_datagrid.action.stop.confirmation.ok')}
                </Button>
              </ButtonsContainer>
            </TextContent>
          </ModalContent>
        </Modal>
      )}
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
