import {PimView, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useMountedRef} from '@akeneo-pim-community/settings-ui';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb, Button, IconButton, Link, MoreIcon} from 'akeneo-design-system';
import {Status} from './Status';
import {StopJobAction} from './StopJobAction';
import React, {useCallback, useEffect, useState} from 'react';
import {useParams} from 'react-router-dom';
import {JobExecutionProgress} from './Progress';
import {Dropdown} from './Dropdown';
import {ShowProfile} from "./ShowProfile";

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

type JobInstance = {
  label: string;
  code: string;
  type: string;
};

type JobExecution = {
  jobInstance: JobInstance;
  tracking: JobExecutionTracking;
  isStoppable: boolean;
  meta: {
    id: string;
    logExists: boolean;
    archives: Record<string, {
      label: string;
      files: Record<string, string>;
    }>
  }
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

const canDownloadLog = (jobExecution: JobExecution) => {
  if (!jobExecution.meta.logExists) {
    return false;
  }

  const {isGranted} = useSecurity();
  if (jobExecution.jobInstance.type === 'export') {
    return isGranted('pim_importexport_export_execution_download_log');
  } else if (jobExecution.jobInstance.type === 'import') {
    return isGranted('pim_importexport_import_execution_download_log');
  }

  return true;
}

const canDownloadArchive = (jobExecution: JobExecution) => {
  const {isGranted} = useSecurity();
  if (jobExecution.jobInstance.type === 'export') {
    return isGranted('pim_importexport_export_execution_download_files');
  } else if (jobExecution.jobInstance.type === 'import') {
    return isGranted('pim_importexport_import_execution_download_files');
  }

  return true;
}

type DownloadLink = {
  label: string;
  url: string;
}

const getDownloadLinks = (jobExecution: JobExecution):DownloadLink[] => {
  if (!jobExecution.meta.archives) {
    return [];
  }

  let downloadLinks: DownloadLink[] = [];
  const translate = useTranslate();
  const router = useRouter();
  const archives = jobExecution.meta.archives;
  Object.keys(archives).forEach(archiver => {
    const archive = archives[archiver];
    let label: string | null = null;
    if (Object.keys(archive.files).length === 1) {
      label = translate(archive.label);
    }

    Object.keys(archive.files).forEach(fileName => {
      downloadLinks.push({
        label: null === label ? fileName : label,
        url: router.generate('pim_enrich_job_tracker_download_file', {
          id: jobExecution.meta.id,
          archiver: archiver,
          key: fileName
        })
      });
    });
  });

  return downloadLinks;
}

const Report = () => {
  const jobTypeWithProfile = ['import', 'export'];
  const {jobExecutionId} = useParams() as {jobExecutionId: string};
  const translate = useTranslate();
  const router = useRouter();
  const jobExecution = useJobExecution(jobExecutionId);

  if (null === jobExecution) return null;

  const downloadLogIsVisible = canDownloadLog(jobExecution);
  const downloadArchiveLinks = getDownloadLinks(jobExecution);
  const downloadArchiveLinkIsVisible = downloadArchiveLinks.length > 0 && canDownloadArchive(jobExecution);
  const downloadArchiveTitle = translate('pim_enrich.entity.job_execution.module.download.output');

  const showProfileIsVisible = jobTypeWithProfile.includes(jobExecution.jobInstance.type);
  const downloadLogHref = router.generate('pim_importexport_export_execution_download_log', {id: jobExecution.meta.id});

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
        </PageHeader.UserActions>
        <PageHeader.Actions>
          {(showProfileIsVisible || downloadLogIsVisible) && (
            <Dropdown title={translate('pim_common.other_actions')} actionButton={<IconButton title={translate('pim_common.other_actions')} icon={<MoreIcon />} onClick={open} ghost={'borderless'} />}>
              {showProfileIsVisible && (
                <ShowProfile jobInstance={jobExecution.jobInstance} />
              )}
              {downloadLogIsVisible && (
                <Link href={downloadLogHref}>
                  {translate('pim_import_export.form.job_execution.button.download_log.title')}
                </Link>
              )}
            </Dropdown>
          )}
          <StopJobAction
            id={jobExecutionId}
            jobLabel={jobExecution.jobInstance.label}
            isStoppable={jobExecution.isStoppable}
            onStop={() => {}}
          />
          {downloadArchiveLinkIsVisible && downloadArchiveLinks.length === 1 &&
            (<Button level="secondary" href={downloadArchiveLinks[0].url}>{downloadArchiveTitle}</Button>)
          }
          {downloadArchiveLinkIsVisible && downloadArchiveLinks.length > 1 &&
          (<Dropdown
            title={downloadArchiveTitle}
            actionButton={<Button level="secondary">{downloadArchiveTitle}</Button>}
          >
            {downloadArchiveLinks.map((link, index) => (
              <Link key={index} href={link.url}>
                {link.label}
              </Link>
            ))}
          </Dropdown>)
          }
        </PageHeader.Actions>
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
export type {JobExecutionTracking, JobInstance, StepExecutionTracking};
