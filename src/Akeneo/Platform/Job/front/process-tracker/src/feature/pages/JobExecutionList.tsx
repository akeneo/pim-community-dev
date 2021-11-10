import React, {useState} from 'react';
import {Breadcrumb, Pagination} from 'akeneo-design-system';
import {useTranslate, useRoute, PimView, PageHeader, PageContent} from '@akeneo-pim-community/shared';
import {useJobExecutionTable} from '../hooks/useJobExecutionTable';
import {JobExecutionTable} from '../components/JobExecutionList/JobExecutionTable';
import {TypeFilter} from '../components/TypeFilter';

const ITEMS_PER_PAGE = 25;

const JobExecutionList = () => {
  const activityHref = useRoute('pim_dashboard_index');
  const translate = useTranslate();

  const [currentPage, setCurrentPage] = useState<number>(1);
  const [typeFilterValue, setTypeFilterValue] = useState<string[]>([]);
  const jobExecutionTable = useJobExecutionTable(currentPage, ITEMS_PER_PAGE, typeFilterValue);
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
        <TypeFilter typeFilterValue={typeFilterValue} onTypeFilterChange={setTypeFilterValue} />
        {matchesCount && matchesCount > 0 && (
          <Pagination
            sticky={0}
            itemsPerPage={ITEMS_PER_PAGE}
            currentPage={currentPage}
            totalItems={matchesCount}
            followPage={setCurrentPage}
          />
        )}
        {jobExecutionTable && (
          <JobExecutionTable
            sticky={ITEMS_PER_PAGE < matchesCount ? 44 : 0}
            jobExecutionRows={jobExecutionTable.rows}
          />
        )}
      </PageContent>
    </>
  );
};

export {JobExecutionList};
