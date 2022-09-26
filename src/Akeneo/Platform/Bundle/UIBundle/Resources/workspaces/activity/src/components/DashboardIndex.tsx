import React from 'react';
import styled from 'styled-components';
import {PageContent, useFeatureFlags} from '@akeneo-pim-community/shared';
import {LastOperationsWidget} from '@akeneo-pim-community/process-tracker';
import {Header} from '@akeneo-pim-community/activity/src/components/Header';
import {CompletenessWidget} from '@akeneo-pim-community/activity/src/components/Widgets/CompletenessWidget';
import {PimVersion} from '@akeneo-pim-community/activity/src/components/PimVersion';
import {TeamworkAssistantWidget, WorkflowWidget} from './Widgets';

const StyledPageContent = styled(PageContent)`
  height: calc(100vh - 202px);
  padding-bottom: 30px;
`;

const DashboardIndex = () => {
  const {isEnabled} = useFeatureFlags();

  return (
    <>
      <Header />
      <StyledPageContent>
        {isEnabled('teamwork_assistant') && <TeamworkAssistantWidget />}
        <CompletenessWidget />
        <LastOperationsWidget />
        {isEnabled('proposal') && <WorkflowWidget />}
        <PimVersion />
      </StyledPageContent>
    </>
  );
};

export {DashboardIndex};
