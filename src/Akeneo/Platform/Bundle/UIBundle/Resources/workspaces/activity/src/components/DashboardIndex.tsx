import React from 'react';
import {PageContent} from '@akeneo-pim-community/shared';
import {Header} from './Header';
import {CompletenessWidget} from './Widgets/CompletenessWidget';
import {LastOperationsWidget} from './Widgets/LastOperationsWidget';
import styled from 'styled-components';

const StyledPageContent = styled(PageContent)`
  height: calc(100vh - 240px);
`;

const DashboardIndex = () => {
  return (
    <>
      <Header />
      <StyledPageContent>
        <CompletenessWidget />
        <LastOperationsWidget />
      </StyledPageContent>
    </>
  );
};

export {DashboardIndex};
