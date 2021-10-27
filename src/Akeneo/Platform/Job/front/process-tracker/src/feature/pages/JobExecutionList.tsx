import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate, useRoute, PimView, PageHeader} from '@akeneo-pim-community/shared';
import {useJobExecutionSearchTableResult} from '../hooks/useJobExecutionSearchTableResult';

const JobExecutionList = () => {
  const translate = useTranslate();
  const jobExecutionSearchTableResult = useJobExecutionSearchTableResult();
  const activityHref = useRoute('pim_dashboard_index');
  const jobExecutionMatchesCount =
    jobExecutionSearchTableResult === null ? 0 : jobExecutionSearchTableResult.matches_count;

  return (
    <>
      <PageHeader showPlaceholder={null === jobExecutionSearchTableResult}>
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
            {count: jobExecutionMatchesCount.toString()},
            jobExecutionMatchesCount
          )}
        </PageHeader.Title>
      </PageHeader>
    </>
  );
};

export {JobExecutionList};
