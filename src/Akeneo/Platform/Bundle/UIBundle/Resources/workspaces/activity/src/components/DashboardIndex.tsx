import React from 'react';
import {PageContent} from '@akeneo-pim-community/shared';
import {Header} from './Header';
import {CompletenessWidget} from './Widgets/CompletenessWidget';
import {LastOperationsWidget} from './Widgets/LastOperationsWidget';
import styled from 'styled-components';
import {PimVersion} from './PimVersion';

const StyledPageContent = styled(PageContent)`
  height: calc(100vh - 202px);
  padding-bottom: 30px;
`;

const DashboardIndex = () => {
  return (
    <>
      <Header />
      <StyledPageContent>
        <CompletenessWidget />
        <LastOperationsWidget />
        <PimVersion />
      </StyledPageContent>
    </>
  );
};

export {DashboardIndex};
