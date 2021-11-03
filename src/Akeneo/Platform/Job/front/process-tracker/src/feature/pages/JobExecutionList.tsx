import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate, useRoute, PimView, PageHeader, PageContent} from '@akeneo-pim-community/shared';
import {useJobExecutionTable} from '../hooks/useJobExecutionTable';
import {JobExecutionTable} from "../components/JobExecutionList/JobExecutionTable";

const JobExecutionList = () => {
  const translate = useTranslate();
  const jobExecutionTable = useJobExecutionTable();
  const activityHref = useRoute('pim_dashboard_index');
  const jobExecutionMatches = jobExecutionTable === null ? 0 : jobExecutionTable.matches_count;

  return (
    <>
      <PageHeader showPlaceholder={null === jobExecutionTable}>
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
            {count: jobExecutionMatches.toString()},
            jobExecutionMatches
          )}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        <JobExecutionTable jobExecutionRows={jobExecutionTable?.rows ?? []} />
      </PageContent>
    </>
  );
};

export {JobExecutionList};
