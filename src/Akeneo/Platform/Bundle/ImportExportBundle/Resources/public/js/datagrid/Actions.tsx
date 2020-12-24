import React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneo-design-system';
import {useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
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
  isVisible: boolean;
  type: string;
};

const Actions = ({id, jobLabel, isStoppable, showLink, refreshCollection, type}: ActionsProps) => {
  const translate = useTranslate();
  const security = useSecurity();

  const isAllowedToViewJobDetails = security.isGranted(`pim_importexport_${type}_execution_show`);

    return (
    <ActionsContainer className="AknGrid-onHoverElement">
      {isAllowedToViewJobDetails &&
        <Button size="small" ghost={true} level="tertiary" href={`#${showLink}`}>
          {translate('pim_datagrid.action.show.title')}
        </Button>
      }
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
