import React, {useCallback, useContext, useEffect, useState} from 'react';
import {PimView, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb, Button, getColor, IconButton, Link, MoreIcon} from 'akeneo-design-system';
import {Status} from './Status';
import {StopJobAction} from './StopJobAction';
import {JobExecutionProgress} from './Progress';
import {Dropdown} from './Dropdown';
import {ShowProfile} from './ShowProfile';
import styled, {ThemeContext} from 'styled-components';
import {JobExecution} from './model/job-execution';
import {useParams} from 'react-router-dom';
import {PageErrorBlock} from '@akeneo-pim-community/shared';
import {useIsMounted} from '@akeneo-pim-community/shared/src';

const SecondaryActionsButton = styled(IconButton)`
  opacity: 0.5;
  :hover {
    opacity: 1;
  }
`;

type Error = {
  statusMessage: any;
  statusCode: number;
};

const useJobExecution = (jobExecutionId: string) => {
  const router = useRouter();
  const isMounted = useIsMounted();
  const [jobExecution, setJobExecution] = useState<JobExecution | null>(null);
  const [error, setError] = useState<Error | null>();

  const fetchJobExecution = useCallback(async (identifier: string) => {
    const response = await fetch(router.generate('pim_enrich_job_execution_rest_get', {identifier}));
    if (!response.ok) {
      setError({
        statusMessage: response.statusText,
        statusCode: response.status,
      });

      return null;
    }

    return response.json();
  }, []);

  useEffect(() => {
    (async () => {
      const jobExecution = await fetchJobExecution(jobExecutionId);
      if (isMounted()) {
        setJobExecution(jobExecution);
      }
    })();
  }, [jobExecutionId]);

  return {jobExecution, error};
};

const canDownloadLog = (jobExecution: JobExecution | null) => {
  const {isGranted} = useSecurity();
  if (!jobExecution || !jobExecution.meta.logExists) {
    return false;
  }

  if (jobExecution.jobInstance.type === 'export') {
    return isGranted('pim_importexport_export_execution_download_log');
  } else if (jobExecution.jobInstance.type === 'import') {
    return isGranted('pim_importexport_import_execution_download_log');
  }

  return true;
};

const canDownloadArchive = (jobExecution: JobExecution | null) => {
  const {isGranted} = useSecurity();
  if (!jobExecution) {
    return false;
  }

  if (jobExecution.jobInstance.type === 'export') {
    return isGranted('pim_importexport_export_execution_download_files');
  } else if (jobExecution.jobInstance.type === 'import') {
    return isGranted('pim_importexport_import_execution_download_files');
  }

  return true;
};

type DownloadLink = {
  label: string;
  url: string;
};

const getDownloadLinks = (jobExecution: JobExecution | null): DownloadLink[] => {
  const translate = useTranslate();
  const router = useRouter();

  if (!jobExecution || !jobExecution.meta.archives) {
    return [];
  }

  let downloadLinks: DownloadLink[] = [];
  const archives = jobExecution.meta.archives;
  Object.keys(archives).forEach(archiver => {
    const archive = archives[archiver];
    let label: string | null = null;
    const fileNames = Object.keys(archive.files);
    if (fileNames.length === 1) {
      label = translate(archive.label);
    }

    fileNames.forEach(fileName => {
      downloadLinks.push({
        label: null === label ? fileName : label,
        url: router.generate('pim_enrich_job_tracker_download_file', {
          id: jobExecution.meta.id,
          archiver: archiver,
          key: fileName,
        }),
      });
    });
  });

  return downloadLinks;
};

const Report = () => {
  const translate = useTranslate();
  const router = useRouter();
  const akeneoTheme = useContext(ThemeContext);

  const jobTypeWithProfile = ['import', 'export'];
  const {jobExecutionId} = useParams() as {jobExecutionId: string};
  const {jobExecution, error} = useJobExecution(jobExecutionId);

  const downloadLogIsVisible = canDownloadLog(jobExecution);
  const downloadArchiveLinks = getDownloadLinks(jobExecution);
  const downloadArchiveLinkIsVisible = canDownloadArchive(jobExecution) && downloadArchiveLinks.length > 0;

  const downloadArchiveTitle = translate('pim_enrich.entity.job_execution.module.download.output');
  const showProfileIsVisible = jobTypeWithProfile.includes(jobExecution?.jobInstance.type || '');

  const downloadLogHref = router.generate('pim_importexport_export_execution_download_log', {
    id: jobExecution?.meta.id || '',
  });

  if (error) {
    return (
      <PageErrorBlock
        title={translate('error.exception', {status_code: error.statusCode.toString()})}
        message={error.statusMessage}
        code={error.statusCode}
      />
    );
  }

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${router.generate('pim_dashboard_index')}`}>
              {translate('pim_menu.tab.activity')}
            </Breadcrumb.Step>
            <Breadcrumb.Step href={`#${router.generate('pim_enrich_job_tracker_index')}`}>
              {translate('pim_menu.item.job_tracker')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_import_export.form.job_execution.title.details')}</Breadcrumb.Step>
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
            <Dropdown
              title={translate('pim_common.other_actions')}
              actionButton={
                <SecondaryActionsButton
                  title={translate('pim_common.other_actions')}
                  icon={<MoreIcon color={akeneoTheme.color.grey120} />}
                  ghost={'borderless'}
                />
              }
            >
              {showProfileIsVisible && jobExecution && <ShowProfile jobInstance={jobExecution.jobInstance} />}
              {downloadLogIsVisible && (
                <Link href={downloadLogHref}>
                  {translate('pim_import_export.form.job_execution.button.download_log.title')}
                </Link>
              )}
            </Dropdown>
          )}
          {jobExecution && (
            <StopJobAction
              id={jobExecution.meta.id}
              jobLabel={jobExecution.jobInstance.label}
              isStoppable={jobExecution.isStoppable}
              onStop={() => {}}
            />
          )}
          {downloadArchiveLinkIsVisible &&
            (downloadArchiveLinks.length === 1 ? (
              <Button level="secondary" href={downloadArchiveLinks[0].url}>
                {downloadArchiveTitle}
              </Button>
            ) : (
              <Dropdown
                title={downloadArchiveTitle}
                actionButton={<Button level="secondary">{downloadArchiveTitle}</Button>}
              >
                {downloadArchiveLinks.map((link, index) => (
                  <Link key={index} href={link.url}>
                    {link.label}
                  </Link>
                ))}
              </Dropdown>
            ))}
        </PageHeader.Actions>
        <PageHeader.Title>{jobExecution?.jobInstance.label}</PageHeader.Title>
        <PageHeader.Content>
          {jobExecution && <Status tracking={jobExecution.tracking} />}
          {jobExecution?.tracking && <JobExecutionProgress steps={jobExecution.tracking.steps} />}
        </PageHeader.Content>
      </PageHeader>
      <PageContent></PageContent>
    </>
  );
};

export {Report};
