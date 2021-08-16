import React from 'react';
import {PageContent, useTranslate} from '@akeneo-pim-community/shared';
import {Header} from './Header';
import {CompletenessWidget, LastOperationsWidget} from './Widgets';
import styled from 'styled-components';
import {PimVersion} from './PimVersion';
import {Helper, LockIcon, SectionTitle, useTheme} from 'akeneo-design-system';

const featureFlags = require('pim/feature-flags');

const StyledPageContent = styled(PageContent)`
  height: calc(100vh - 202px);
  padding-bottom: 30px;
`;

const DashboardIndex = () => {
  const translate = useTranslate();
  const theme = useTheme();

  return (
    <>
      <Header />
      <StyledPageContent>
        {featureFlags.isEnabled('free_trial') && (
          <div style={{marginBottom: '20px'}}>
            <SectionTitle>
              <SectionTitle.Title style={{color: theme.color.grey100}}>
                {translate('free_trial.activity.dashboard.projects')}
              </SectionTitle.Title>
              <LockIconContainer>
                <LockIcon size={16} color={theme.color.blue100} />
              </LockIconContainer>
            </SectionTitle>
            <Helper level="info">{translate('free_trial.activity.dashboard.helper')}</Helper>
          </div>
        )}
        <CompletenessWidget />
        <LastOperationsWidget />
        <PimVersion />
      </StyledPageContent>
    </>
  );
};

const LockIconContainer = styled.div`
  border: 1px solid #4ca8e0;
  border-radius: 4px;
  background: #f0f7fc;
  height: 24px;
  width: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
`;

export {DashboardIndex};
