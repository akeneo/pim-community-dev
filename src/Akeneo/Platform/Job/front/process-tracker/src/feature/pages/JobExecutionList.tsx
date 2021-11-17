import React, {useCallback} from 'react';
import {AttributesIllustration, Breadcrumb, Pagination} from 'akeneo-design-system';
import {
  useStorageState,
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
import {
  getDefaultJobExecutionFilter,
  isDefaultJobExecutionFilter,
  JobExecutionFilter,
  JobExecutionFilterSort,
  JobStatus,
} from '../models';

const FILTER_LOCAL_STORAGE_KEY = 'process-tracker.filters';

const JobExecutionList = () => {
  const activityHref = useRoute('pim_dashboard_index');
  const translate = useTranslate();
  const [jobExecutionFilter, setJobExecutionFilter] = useStorageState<JobExecutionFilter>(
    getDefaultJobExecutionFilter(),
    FILTER_LOCAL_STORAGE_KEY
  );
  const jobExecutionTable = useJobExecutionTable(jobExecutionFilter);
  const matchesCount = jobExecutionTable === null ? 0 : jobExecutionTable.matches_count;

  const handlePageChange = (page: number) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page}));
  };

  const handleSortChange = (sort: JobExecutionFilterSort) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, sort}));
  };

  const handleStatusFilterChange = (status: JobStatus[]) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, status}));
  };

  const handleTypeFilterChange = (type: string[]) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, type}));
  };

  const handleSearchChange = useCallback((search: string) => {
    setJobExecutionFilter(jobExecutionFilter => ({...jobExecutionFilter, page: 1, search}));
  }, []);

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
              onSearchChange={handleSearchChange}
            />
            {0 < matchesCount && (
              <>
                <Pagination
                  sticky={44}
                  itemsPerPage={jobExecutionFilter.size}
                  currentPage={jobExecutionFilter.page}
                  totalItems={matchesCount}
                  followPage={handlePageChange}
                />
                <JobExecutionTable
                  sticky={jobExecutionFilter.size < matchesCount ? 88 : 44}
                  jobExecutionRows={jobExecutionTable.rows}
                  onSortChange={handleSortChange}
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
