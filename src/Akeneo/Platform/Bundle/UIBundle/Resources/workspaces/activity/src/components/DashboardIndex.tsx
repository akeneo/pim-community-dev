import React from 'react';
import {PageContent} from '@akeneo-pim-community/shared';
import {Header} from '@akeneo-pim-community/activity/src/components/Header';
import {CompletenessWidget} from '@akeneo-pim-community/activity/src/components/Widgets/CompletenessWidget';
import {LastOperationsWidget} from '@akeneo-pim-community/activity/src/components/Widgets/LastOperationsWidget';
import {PimVersion} from '@akeneo-pim-community/activity/src/components/PimVersion';
import styled from 'styled-components';
import {TeamworkAssistantWidget, WorkflowWidget} from './Widgets';

const StyledPageContent = styled(PageContent)`
  height: calc(100vh - 202px);
  padding-bottom: 30px;
`;

const DashboardIndex = () => {
  return (
    <>
      <Header />
      <StyledPageContent>
        <TeamworkAssistantWidget />
        <CompletenessWidget />
        <LastOperationsWidget />
        <WorkflowWidget />
        <PimVersion />
      </StyledPageContent>
    </>
  );
};

export {DashboardIndex};
