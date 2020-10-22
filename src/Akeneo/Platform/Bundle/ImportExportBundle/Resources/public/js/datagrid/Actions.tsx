import React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';

const ActionsContainer = styled.div`
  display: flex;
  gap: 10px;
`;

type ActionsProps = {
  id: string;
  isStoppable: boolean;
};

const Actions = ({id, isStoppable}: ActionsProps) => {
  const translate = useTranslate();
  const showRoute = useRoute('pim_enrich_job_tracker_show', {id});
  const stopRoute = useRoute('pim_enrich_job_tracker_rest_stop', {id});

  const stopJob = () => {
    fetch(stopRoute);
  };

  return (
    <ActionsContainer>
      <Button size="small" ghost={true} level="tertiary" href={`#${showRoute}`}>
        {translate('pim_datagrid.action.show.title')}
      </Button>
      {isStoppable && (
        <Button size="small" ghost={true} level="danger" onClick={stopJob}>
          {translate('pim_datagrid.action.stop.title')}
        </Button>
      )}
    </ActionsContainer>
  );
};

export default Actions;
