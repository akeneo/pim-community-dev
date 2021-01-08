import React from 'react';
import {PimView, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {
  Breadcrumb,
  Button,
  Dropdown,
  getColor,
  IconButton,
  Link, LoaderIcon,
  MoreIcon,
  useBooleanState,
} from 'akeneo-design-system';
import {Status} from './Status';
import {StopJobAction} from './StopJobAction';
import {JobExecutionProgress} from './Progress';
import {ShowProfile} from './ShowProfile';
import styled from 'styled-components';
import {getDownloadLinks, JobExecution} from './models/job-execution';
import {useParams} from 'react-router-dom';
import {FullScreenError} from '@akeneo-pim-community/shared';
import {useJobExecution} from './hooks/use-job-execution';
import {useRoute, Security} from '@akeneo-pim-community/legacy-bridge';
import {SummaryTable} from './summary/SummaryTable';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  height: 100vh;
`;

const SecondaryActionsButton = styled(IconButton)`
  opacity: 0.5;
  color: ${getColor('grey', 120)};
  :hover {
    opacity: 1;
  }
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

const ExecutionDetail = () => {
  const translate = useTranslate();
  const security = useSecurity();
  const router = useRouter();

  const jobTypeWithProfile = ['import', 'export'];
  const {jobExecutionId} = useParams() as {jobExecutionId: string};
  const {jobExecution, error, reloadJobExecution, isFinished} = useJobExecution(jobExecutionId);

  const handleStop = async () => {
    await reloadJobExecution();
  };
  const [secondaryActionIsOpen, openSecondaryAction, closeSecondaryAction] = useBooleanState(false);
  const [downloadDropdownIsOpen, openDownloadDropdown, closeDownloadDropdown] = useBooleanState(false);

  const downloadLogIsVisible = canDownloadLog(security, jobExecution);
  const downloadArchiveLinks = getDownloadLinks(jobExecution?.meta.archives ?? null);
  const downloadArchiveLinkIsVisible = canDownloadArchive(security, jobExecution) && downloadArchiveLinks.length > 0;

  const downloadArchiveTitle = translate('pim_enrich.entity.job_execution.module.download.output');
  const showProfileIsVisible = jobTypeWithProfile.includes(jobExecution?.jobInstance.type || '');

  const dashboardHref = useRoute('pim_dashboard_index');
  const jobTrackerHref = useRoute('pim_enrich_job_tracker_index');
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
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${dashboardHref}`}>{translate('pim_menu.tab.activity')}</Breadcrumb.Step>
            <Breadcrumb.Step href={`#${jobTrackerHref}`}>{translate('pim_menu.item.job_tracker')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_import_export.form.job_execution.title.details')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          {!isFinished && (
            <>
              {translate('pim_import_export.form.job_execution.refreshing')}
              <LoaderIcon />
            </>
          )}
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          {(showProfileIsVisible || downloadLogIsVisible) && (
            <Dropdown>
              <SecondaryActionsButton
                title={translate('pim_common.other_actions')}
                icon={<MoreIcon />}
                ghost={'borderless'}
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
            (downloadArchiveLinks.length === 1 ? (
              <Button
                level="secondary"
                href={router.generate('pim_enrich_job_tracker_download_file', {
                  id: jobExecutionId,
                  archiver: downloadArchiveLinks[0].archiver,
                  key: downloadArchiveLinks[0].key,
                })}
              >
                {downloadArchiveTitle}
              </Button>
            ) : (
              <Dropdown>
                <Button level="secondary" onClick={openDownloadDropdown}>
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
                            {translate(link.label)}
                          </Link>
                        </Dropdown.Item>
                      ))}
                    </Dropdown.ItemCollection>
                  </Dropdown.Overlay>
                )}
              </Dropdown>
            ))}
        </PageHeader.Actions>
        <PageHeader.Title>{jobExecution?.jobInstance.label}</PageHeader.Title>
        <PageHeader.Content>
          {jobExecution && <Status tracking={jobExecution.tracking} />}
          {jobExecution?.tracking && <JobExecutionProgress steps={jobExecution.tracking.steps} />}
        </PageHeader.Content>
      </PageHeader>
      <PageContent>{jobExecution && <SummaryTable jobExecution={jobExecution} />}</PageContent>
    </Container>
  );
};

export {ExecutionDetail};
