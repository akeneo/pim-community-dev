import React, {useCallback} from 'react';
import {AttributesIllustration, Breadcrumb, Helper, Pagination} from 'akeneo-design-system';
import {
  useTranslate,
  useRoute,
  PimView,
  PageHeader,
  PageContent,
  NoDataSection,
  NoDataTitle,
} from '@akeneo-pim-community/shared';
import {useJobExecutionTable} from '../hooks';
import {JobExecutionSearchBar, JobExecutionTable} from '../components';
import {isDefaultJobExecutionFilter, JobExecutionFilterSort, JobStatus} from '../models';
import {useStoredJobExecutionFilter} from '../hooks/useStoredJobExecutionFilter';

const MAX_PAGE_WITHOUT_FILTER = 50;

const JobExecutionList = () => {
  const activityHref = useRoute('pim_dashboard_index');
  const translate = useTranslate();

  const [jobExecutionFilter, setJobExecutionFilter] = useStoredJobExecutionFilter();
  const [jobExecutionTable, refreshJobExecutionTable] = useJobExecutionTable(jobExecutionFilter);
  const matchesCount = jobExecutionTable === null ? 0 : jobExecutionTable.matches_count;
  const displayPaginationWarning = MAX_PAGE_WITHOUT_FILTER === jobExecutionFilter.page;

  const handlePageChange = (page: number) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page}));
  };

  const handleSortChange = (sort: JobExecutionFilterSort) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, sort}));
  };

  const handleStatusFilterChange = (status: JobStatus[]) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, status}));
  };

  const handleTypeFilterChange = (type: string[]) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, type}));
  };

  const handleUserFilterChange = (user: string[]) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, user}));
  };

  const handleAutomationFilterChange = (automation: null | boolean) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, automation}));
  };

  const handleSearchChange = useCallback(
    (search: string) => {
      setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, search}));
    },
    [setJobExecutionFilter]
  );

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
          <>
            <JobExecutionSearchBar
              jobExecutionFilter={jobExecutionFilter}
              onStatusFilterChange={handleStatusFilterChange}
              onTypeFilterChange={handleTypeFilterChange}
              onUserFilterChange={handleUserFilterChange}
              onAutomationFilterChange={handleAutomationFilterChange}
              onSearchChange={handleSearchChange}
            />
            {0 < matchesCount && (
              <>
                {displayPaginationWarning && (
                  <Helper level="warning" sticky={44}>
                    {translate('akeneo_job_process_tracker.max_page_without_filter_helper')}
                  </Helper>
                )}
                <Pagination
                  sticky={displayPaginationWarning ? 88 : 44}
                  itemsPerPage={jobExecutionFilter.size}
                  currentPage={jobExecutionFilter.page}
                  totalItems={Math.min(matchesCount, MAX_PAGE_WITHOUT_FILTER * jobExecutionFilter.size)}
                  followPage={handlePageChange}
                />
                <JobExecutionTable
                  sticky={jobExecutionFilter.size < matchesCount ? (displayPaginationWarning ? 132 : 88) : 44}
                  jobExecutionRows={jobExecutionTable.rows}
                  onSortChange={handleSortChange}
                  onTableRefresh={refreshJobExecutionTable}
                  currentSort={jobExecutionFilter.sort}
                />
              </>
            )}
            {0 === matchesCount && isDefaultJobExecutionFilter(jobExecutionFilter) && (
              <NoDataSection>
                <AttributesIllustration size={256} />
                <NoDataTitle>{translate('pim_common.no_result')}</NoDataTitle>
              </NoDataSection>
            )}
            {0 === matchesCount && !isDefaultJobExecutionFilter(jobExecutionFilter) && (
              <NoDataSection>
                <AttributesIllustration size={256} />
                <NoDataTitle>{translate('pim_common.no_search_result')}</NoDataTitle>
              </NoDataSection>
            )}
          </>
        )}
      </PageContent>
    </>
  );
};

export {JobExecutionList};
