import React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';

const ActionsContainer = styled.div`
  display: flex;
  gap: 10px;
`;

type ActionsProps = {
  jobId: string;
  isStoppable: boolean;
};

const Actions = ({jobId, isStoppable}: ActionsProps) => {
  const translate = useTranslate();
  const showRoute = useRoute('pim_enrich_job_tracker_show', {id: jobId});
  const stopRoute = useRoute('pim_enrich_job_tracker_rest_stop', {id: jobId});

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

export {Actions};
