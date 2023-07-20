import React from 'react';
import styled from 'styled-components';
import {
  PageContent,
  PageHeader,
  FullScreenError,
  useRouter,
  useSecurity,
  useTranslate,
  useRoute,
  Security,
  PimView,
} from '@akeneo-pim-community/shared';
import {
  Breadcrumb,
  Button,
  Dropdown,
  getColor,
  IconButton,
  Link,
  LoaderIcon,
  MoreIcon,
  useBooleanState,
} from 'akeneo-design-system';
import {Progress, SummaryTable, ShowProfile, StopJobAction, JobExecutionStatus} from '../components';
import {getDownloadLinks, JobExecution} from '../models';
import {useJobExecution} from '../hooks/useJobExecution';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  height: 100vh;
`;

const Refreshing = styled.span`
  display: flex;
  gap: 10px;
  align-items: center;
  font-style: italic;
  color: ${getColor('grey', 100)};
  text-align: right;
`;

const StatusContainer = styled.div`
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 5px;
`;

const canDownloadLog = ({isGranted}: Security, jobExecution: JobExecution | null) => {
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

const canDownloadArchive = ({isGranted}: Security, jobExecution: JobExecution | null) => {
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

type JobExecutionDetailProps = {
  jobExecutionId: string;
};

const JobExecutionDetail = ({jobExecutionId}: JobExecutionDetailProps) => {
  const translate = useTranslate();
  const security = useSecurity();
  const router = useRouter();

  const jobTypeWithProfile = ['import', 'export'];
  const [jobExecution, error, reloadJobExecution, isAutoRefreshing] = useJobExecution(jobExecutionId);

  const handleStop = async () => {
    await reloadJobExecution();
  };
  const [secondaryActionIsOpen, openSecondaryAction, closeSecondaryAction] = useBooleanState(false);
  const [downloadDropdownIsOpen, openDownloadDropdown, closeDownloadDropdown] = useBooleanState(false);

  const downloadLogIsVisible = canDownloadLog(security, jobExecution);
  const downloadArchiveLinks = getDownloadLinks(jobExecution?.meta.archives ?? null);
  const downloadArchiveLinkIsVisible = canDownloadArchive(security, jobExecution) && 0 < downloadArchiveLinks.length;
  const downloadZipArchive = jobExecution?.meta?.generateZipArchive ?? false;

  const downloadArchiveTitle = translate(
    'pim_enrich.entity.job_execution.module.download.output',
    {},
    downloadArchiveLinks.length
  );
  const showProfileIsVisible = jobTypeWithProfile.includes(jobExecution?.jobInstance.type || '');

  const dashboardHref = useRoute('pim_dashboard_index');
  const jobTrackerHref = useRoute('akeneo_job_process_tracker_index');
  const downloadLogHref = useRoute('pim_importexport_export_execution_download_log', {
    id: jobExecutionId,
  });

  if (error) {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: error.statusCode.toString()})}
        message={error.statusMessage}
        code={error.statusCode}
      />
    );
  }

  return (
    <Container>
      <PageHeader showPlaceholder={null === jobExecution}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${dashboardHref}`}>{translate('pim_menu.tab.activity')}</Breadcrumb.Step>
            <Breadcrumb.Step href={`#${jobTrackerHref}`}>{translate('pim_menu.item.job_tracker')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_import_export.form.job_execution.title.details')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          {null !== jobExecution && isAutoRefreshing && (
            <Refreshing>
              {translate('pim_import_export.form.job_execution.refreshing')}
              <LoaderIcon />
            </Refreshing>
          )}
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          {(showProfileIsVisible || downloadLogIsVisible) && (
            <Dropdown>
              <IconButton
                level="tertiary"
                title={translate('pim_common.other_actions')}
                icon={<MoreIcon />}
                ghost="borderless"
                onClick={openSecondaryAction}
              />
              {secondaryActionIsOpen && (
                <Dropdown.Overlay onClose={closeSecondaryAction}>
                  <Dropdown.Header>
                    <Dropdown.Title>{translate('pim_common.other_actions')}</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    {showProfileIsVisible && jobExecution && (
                      <Dropdown.Item>
                        <ShowProfile jobInstance={jobExecution.jobInstance} />
                      </Dropdown.Item>
                    )}
                    {downloadLogIsVisible && (
                      <Dropdown.Item>
                        <Link href={downloadLogHref}>
                          {translate('pim_import_export.form.job_execution.button.download_log.title')}
                        </Link>
                      </Dropdown.Item>
                    )}
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
          )}
          {jobExecution && (
            <StopJobAction
              id={jobExecutionId}
              jobLabel={jobExecution.jobInstance.label}
              isStoppable={jobExecution.isStoppable}
              onStop={handleStop}
            />
          )}
          {jobExecution &&
            downloadArchiveLinkIsVisible &&
            (downloadArchiveLinks.length === 1 && !downloadZipArchive ? (
              <Button
                level="secondary"
                href={router.generate('pim_enrich_job_tracker_download_file', {
                  id: jobExecutionId,
                  archiver: downloadArchiveLinks[0].archiver,
                  key: downloadArchiveLinks[0].key,
                })}
              >
                {translate(downloadArchiveLinks[0].label, {}, downloadArchiveLinks.length)}
              </Button>
            ) : (
              <Dropdown>
                <Button level="secondary" data-toggle="dropdown" onClick={openDownloadDropdown}>
                  {downloadArchiveTitle}
                </Button>
                {downloadDropdownIsOpen && (
                  <Dropdown.Overlay onClose={closeDownloadDropdown}>
                    <Dropdown.Header>
                      <Dropdown.Title>{downloadArchiveTitle}</Dropdown.Title>
                    </Dropdown.Header>
                    <Dropdown.ItemCollection>
                      {downloadArchiveLinks.map((link, index) => (
                        <Dropdown.Item key={index}>
                          <Link
                            href={router.generate('pim_enrich_job_tracker_download_file', {
                              id: jobExecutionId,
                              archiver: link.archiver,
                              key: link.key,
                            })}
                          >
                            {translate(link.label, {}, downloadArchiveLinks.length)}
                          </Link>
                        </Dropdown.Item>
                      ))}
                      {downloadZipArchive && (
                        <Dropdown.Item>
                          <Link
                            href={router.generate('pim_enrich_job_tracker_download_zip_archive', {
                              jobExecutionId: jobExecutionId,
                            })}
                          >
                            {translate('pim_import_export.form.job_execution.button.download_archive.title')}
                          </Link>
                        </Dropdown.Item>
                      )}
                    </Dropdown.ItemCollection>
                  </Dropdown.Overlay>
                )}
              </Dropdown>
            ))}
        </PageHeader.Actions>
        <PageHeader.Title>{jobExecution?.jobInstance.label ?? jobExecutionId}</PageHeader.Title>
        <PageHeader.Content>
          {jobExecution && (
            <StatusContainer>
              {translate('pim_common.status')}
              <JobExecutionStatus
                data-testid="job-status"
                status={jobExecution.tracking.status}
                currentStep={jobExecution.tracking.currentStep}
                totalSteps={jobExecution.tracking.totalSteps}
                hasWarning={jobExecution.tracking.warning}
                hasError={jobExecution.tracking.error}
              />
            </StatusContainer>
          )}
          {jobExecution?.tracking && <Progress jobStatus={jobExecution?.status} steps={jobExecution.tracking.steps} />}
        </PageHeader.Content>
      </PageHeader>
      <PageContent>{jobExecution && <SummaryTable jobExecution={jobExecution} />}</PageContent>
    </Container>
  );
};

export {JobExecutionDetail};
