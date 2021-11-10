import React, {useState} from 'react';
import {Breadcrumb, Pagination, Search} from 'akeneo-design-system';
import {useTranslate, useRoute, PimView, PageHeader, PageContent} from '@akeneo-pim-community/shared';
import {useJobExecutionTable} from '../hooks';
import {JobExecutionTable, StatusFilter} from '../components';
import {JobStatus} from '../models';

const ITEMS_PER_PAGE = 25;

const JobExecutionList = () => {
  const translate = useTranslate();
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [statusFilterValue, setStatusFilterValue] = useState<JobStatus[]>([]);
  const jobExecutionTable = useJobExecutionTable(currentPage, ITEMS_PER_PAGE, statusFilterValue);
  const activityHref = useRoute('pim_dashboard_index');
  const matchesCount = jobExecutionTable === null ? 0 : jobExecutionTable.matches_count;

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
            {count: matchesCount.toString()},
            matchesCount
          )}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        {jobExecutionTable && (
          <Search sticky={0} placeholder="TODO RAC-938" searchValue="" onSearchChange={() => {}}>
            <StatusFilter statusFilterValue={statusFilterValue} onStatusFilterChange={setStatusFilterValue} />
          </Search>
        )}
        {jobExecutionTable && jobExecutionTable.total_count > 0 && (
          <Pagination
            sticky={44}
            itemsPerPage={ITEMS_PER_PAGE}
            currentPage={currentPage}
            totalItems={jobExecutionTable.matches_count}
            followPage={setCurrentPage}
          />
        )}
        {jobExecutionTable && (
          <JobExecutionTable
            sticky={ITEMS_PER_PAGE < jobExecutionTable.matches_count ? 88 : 44}
            jobExecutionRows={jobExecutionTable.rows}
          />
        )}
      </PageContent>
    </>
  );
};

export {JobExecutionList};
