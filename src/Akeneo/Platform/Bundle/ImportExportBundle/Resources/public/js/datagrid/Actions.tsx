import React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {StopJobAction} from 'pimui/js/job/execution/StopJobAction';

const ActionsContainer = styled.div`
  display: flex;
  gap: 10px;
  justify-content: flex-end;
`;

type ActionsProps = {
  id: string;
  jobLabel: string;
  isStoppable: boolean;
  showLink: string;
  refreshCollection: () => void;
};

const Actions = ({id, jobLabel, isStoppable, showLink, refreshCollection}: ActionsProps) => {
  const translate = useTranslate();
  return (
    <ActionsContainer>
      <Button size="small" ghost={true} level="tertiary" href={`#${showLink}`}>
        {translate('pim_datagrid.action.show.title')}
      </Button>
      <StopJobAction
        id={id}
        jobLabel={jobLabel}
        isStoppable={isStoppable}
        onStop={refreshCollection}
        ghost={true}
        size="small"
      />
    </ActionsContainer>
  );
};

export default Actions;
