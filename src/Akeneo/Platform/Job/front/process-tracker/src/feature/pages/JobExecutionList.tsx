import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate, useRoute, PimView, PageHeader} from '@akeneo-pim-community/shared';
import {useSearchJobExecutionTableResult} from '../hooks/useSearchJobExecutionTableResult';

const JobExecutionList = () => {
  const translate = useTranslate();
  const searchJobExecutionTableResult = useSearchJobExecutionTableResult();
  const activityHref = useRoute('pim_dashboard_index');
  const resultMatches = searchJobExecutionTableResult === null ? 0 : searchJobExecutionTableResult.matches_count;

  return (
    <>
      <PageHeader showPlaceholder={null === searchJobExecutionTableResult}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${activityHref}`}>{translate('pim_menu.tab.activity')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.job_tracker')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-process-tracker-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>
          {translate(
            'pim_enrich.entity.job_execution.page_title.index',
            {count: resultMatches.toString()},
            resultMatches
          )}
        </PageHeader.Title>
      </PageHeader>
    </>
  );
};

export {JobExecutionList};
