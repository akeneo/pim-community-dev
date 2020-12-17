import React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {StopJobAction} from 'pimui/js/job/execution/StopJobAction';

const securityContext = require('pim/security-context');

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
};

const checkAuthorization = () => {
    return securityContext.isGranted('pim_importexport_import_execution_show');
};

const Actions = ({id, jobLabel, isStoppable, showLink, refreshCollection, isVisible}: ActionsProps) => {
  const translate = useTranslate();
  return (
    <ActionsContainer className="AknGrid-onHoverElement">
      <Button size="small" ghost={true} level="tertiary" href={`#${showLink}`} isVisible={checkAuthorization} >
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
