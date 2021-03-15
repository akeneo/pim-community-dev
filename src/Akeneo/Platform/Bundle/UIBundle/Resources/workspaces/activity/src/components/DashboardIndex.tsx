import React from 'react';
import {PageContent} from '@akeneo-pim-community/shared';
import {Header} from './Header';
import {CompletenessWidget} from './Widgets/CompletenessWidget';
import {LastOperationsWidget} from './Widgets/LastOperationsWidget';

const DashboardIndex = () => {
  return (
    <>
      <Header />
      <PageContent>
        <CompletenessWidget />
        <LastOperationsWidget />
      </PageContent>
    </>
  );
};

export {DashboardIndex};
