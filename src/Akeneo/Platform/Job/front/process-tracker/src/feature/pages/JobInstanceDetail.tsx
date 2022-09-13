import React, {useMemo} from 'react';
import {AttributesIllustration, SectionTitle} from 'akeneo-design-system';
import {NoDataSection, NoDataTitle, Section, useTranslate} from '@akeneo-pim-community/shared';
import {useJobExecutionTable} from '../hooks';
import {LastExecutionTable} from '../components';
import {JobExecutionFilter} from '../models';

type JobInstanceDetailProps = {
  code: string;
  type: string;
};

const JobInstanceDetail = ({code, type}: JobInstanceDetailProps) => {
  const translate = useTranslate();
  const filter = useMemo<JobExecutionFilter>(
    () => ({
      page: 1,
      sort: {column: 'started_at', direction: 'DESC'},
      automation: null,
      status: [],
      size: 25,
      search: '',
      user: [],
      type: [type],
      code: [code],
    }),
    [code, type]
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
      {0 < jobExecutionTable.matches_count && (
        <LastExecutionTable jobExecutionRows={jobExecutionTable.rows} onTableRefresh={refreshJobExecutionTable} />
      )}
      {0 === jobExecutionTable.matches_count && (
        <NoDataSection>
          <AttributesIllustration size={256} />
          <NoDataTitle>{translate('pim_common.no_result')}</NoDataTitle>
        </NoDataSection>
      )}
    </Section>
  );
};

export {JobInstanceDetail};
export type {JobInstanceDetailProps};
