import {PimView, useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useMountedRef} from '@akeneo-pim-community/settings-ui';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb, Link} from 'akeneo-design-system';
import {Status} from './Status';
import {StopJobAction} from './StopJobAction';
import React, {useCallback, useEffect, useState} from 'react';
import {useParams} from 'react-router-dom';
import {JobExecutionProgress} from './Progress';
import {SecondaryActions} from './SecondaryActions';

type StepExecutionStatus =
  | 'COMPLETED'
  | 'STARTING'
  | 'STARTED'
  | 'STOPPING'
  | 'STOPPED'
  | 'FAILED'
  | 'ABANDONED'
  | 'UNKNOWN';
type StepExecutionTracking = {
  hasError: boolean;
  hasWarning: boolean;
  isTrackable: boolean;
  jobName: string;
  stepName: string;
  status: StepExecutionStatus;
  duration: number;
  processedItems: number;
  totalItems: number;
};
type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';
type JobExecutionTracking = {
  error: boolean;
  warning: boolean;
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
  steps: StepExecutionTracking[];
};

type JobExecution = {
  jobInstance: {
    label: string;
    code: string;
    type: string;
  };
  tracking: JobExecutionTracking;
  isStoppable: boolean;
};

const useJobExecution = (jobExecutionId: string): JobExecution | null => {
  const router = useRouter();
  const isMounted = useMountedRef();
  const [jobExecution, setJobExecution] = useState<JobExecution | null>(null);

  const fetchJobExecution = useCallback(async (identifier: string) => {
    const response = await fetch(router.generate('pim_enrich_job_execution_rest_get', {identifier}));

    return response.json();
  }, []);

  useEffect(() => {
    (async () => {
      const jobExecution = await fetchJobExecution(jobExecutionId);
      if (isMounted) setJobExecution(jobExecution);
    })();
  }, [jobExecutionId]);

  return jobExecution;
};

const ShowProfile = ({code, type}: {code: string; type: string}) => {
  const router = useRouter();
  var route = 'pim_importexport_%type%_profile_show'.replace('%type%', type);

  const href = `#/${router.generate(route, {code})}`;
  const translate = useTranslate();

  if (!['import', 'export'].includes(type)) return null;

  return <Link href={href}>{translate('pim_import_export.form.job_execution.button.show_profile.title')}</Link>;
};

const Report = () => {
  const {jobExecutionId} = useParams() as {jobExecutionId: string};
  const translate = useTranslate();
  const router = useRouter();
  const jobExecution = useJobExecution(jobExecutionId);

  if (null === jobExecution) return null;

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={router.generate('pim_dashboard_index')}>
              {translate('pim_menu.tab.activity')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.job_tracker')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
          <SecondaryActions title={translate('pim_common.other_actions')}>
            <ShowProfile code={jobExecution.jobInstance.code} type={jobExecution.jobInstance.type} />
          </SecondaryActions>
          <StopJobAction
            id={jobExecutionId}
            jobLabel={jobExecution.jobInstance.label}
            isStoppable={jobExecution.isStoppable}
            onStop={() => {}}
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{jobExecution.jobInstance.label}</PageHeader.Title>
        <PageHeader.Content>
          <Status tracking={jobExecution.tracking} />
          {jobExecution.tracking && <JobExecutionProgress steps={jobExecution.tracking.steps} />}
        </PageHeader.Content>
      </PageHeader>
      <PageContent></PageContent>
    </>
  );
};

export {Report};
export type {JobExecutionTracking, StepExecutionTracking};
