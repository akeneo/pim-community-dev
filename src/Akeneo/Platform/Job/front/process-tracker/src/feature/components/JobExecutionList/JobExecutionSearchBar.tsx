import React, {useEffect, useRef, useState} from 'react';
import {useAutoFocus, useDebounce, Search} from 'akeneo-design-system';
import {useTranslate, useSecurity} from '@akeneo-pim-community/shared';
import {TypeFilter} from './TypeFilter';
import {StatusFilter} from './StatusFilter';
import {UserFilter} from './UserFilter';
import {JobExecutionFilter, JobStatus} from '../../models';
import {AutomationFilter} from './AutomationFilter';

type JobExecutionSearchBarProps = {
  jobExecutionFilter: JobExecutionFilter;
  onStatusFilterChange: (status: JobStatus[]) => void;
  onTypeFilterChange: (types: string[]) => void;
  onUserFilterChange: (users: string[]) => void;
  onAutomationFilterChange: (uatomation: null | boolean) => void;
  onSearchChange: (search: string) => void;
};

const JobExecutionSearchBar = ({
  jobExecutionFilter,
  onStatusFilterChange,
  onTypeFilterChange,
  onUserFilterChange,
  onAutomationFilterChange,
  onSearchChange,
}: JobExecutionSearchBarProps) => {
  const translate = useTranslate();
  const [userSearch, setUserSearch] = useState<string>(jobExecutionFilter.search);
  const debouncedUserSearch = useDebounce(userSearch, 250);
  const inputRef = useRef<HTMLInputElement>(null);
  const {isGranted} = useSecurity();

  const canViewAllJobs = isGranted('pim_enrich_job_tracker_view_all_jobs');

  useAutoFocus(inputRef);

  useEffect(() => {
    onSearchChange(debouncedUserSearch);
  }, [debouncedUserSearch, onSearchChange]);

  return (
    <Search
      inputRef={inputRef}
      sticky={0}
      placeholder={translate('akeneo_job_process_tracker.job_execution_list.search_placeholder')}
      searchValue={userSearch}
      onSearchChange={setUserSearch}
    >
      {canViewAllJobs && (
        <AutomationFilter
          automationFilterValue={jobExecutionFilter.automation}
          onAutomationFilterChange={onAutomationFilterChange}
        />
      )}
      <TypeFilter typeFilterValue={jobExecutionFilter.type} onTypeFilterChange={onTypeFilterChange} />
      <StatusFilter statusFilterValue={jobExecutionFilter.status} onStatusFilterChange={onStatusFilterChange} />
      <UserFilter userFilterValue={jobExecutionFilter.user} onUserFilterChange={onUserFilterChange} />
    </Search>
  );
};

export {JobExecutionSearchBar};
