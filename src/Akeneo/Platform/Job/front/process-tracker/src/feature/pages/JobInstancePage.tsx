import React, {useMemo} from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {Section, useTranslate} from '@akeneo-pim-community/shared';
import {useJobExecutionTable} from '../hooks';
import {LastExecutionsTable} from '../components';

type JobInstancePageProps = {
  code: string;
  type: string;
};

const JobInstancePage = () => {
  const translate = useTranslate();
  const filter = useMemo(
    () => ({page: 1, sort: {column: 'started_at', direction: 'DESC'}, status: [], size: 25, search: '', type: []}),
    []
  );

  const [jobExecutionTable, refreshJobExecutionTable] = useJobExecutionTable(filter);

  if (null === jobExecutionTable) {
    return null;
  }

  return (
    <Section>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo_job_process_tracker.last_executions.title')}</SectionTitle.Title>
      </SectionTitle>
      <LastExecutionsTable jobExecutionRows={jobExecutionTable.rows} onTableRefresh={refreshJobExecutionTable} />
    </Section>
  );
};

export {JobInstancePage};
export type {JobInstancePageProps};
