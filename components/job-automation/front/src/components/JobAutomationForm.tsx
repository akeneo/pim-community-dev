import React from 'react';
import styled from 'styled-components';
import {SectionTitle, Field, BooleanInput, Helper} from 'akeneo-design-system';
import {
  Section,
  useTranslate,
  ValidationError,
  filterErrors,
  useFeatureFlags,
  useSecurity,
} from '@akeneo-pim-community/shared';
import {Automation, CronExpression} from '../models';
import {UserGroupsForm} from './UserGroupsForm';
import {UsersForm} from './UsersForm';
import {CronExpressionForm} from './CronExpressionForm';

const SpacedSection = styled(Section)`
  margin-top: 20px;
`;

type JobAutomationFormProps = {
  scheduled: boolean;
  automation: Automation;
  automationValidationErrors: ValidationError[];
  scheduledValidationErrors: ValidationError[];
  onScheduledChange: (scheduled: boolean) => void;
  onAutomationChange: (automation: Automation) => void;
};

const JobAutomationForm = ({
  scheduled,
  automation,
  automationValidationErrors,
  scheduledValidationErrors,
  onScheduledChange,
  onAutomationChange,
}: JobAutomationFormProps) => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();
  const {isGranted} = useSecurity();

  const handleRunningUserGroupsChange = (userGroupIds: number[]) =>
    onAutomationChange({...automation, running_user_groups: userGroupIds});
  const handleNotificationUserGroupsChange = (userGroupIds: number[]) =>
    onAutomationChange({...automation, notification_user_groups: userGroupIds});
  const handleNotificationUsersChange = (userIds: number[]) =>
    onAutomationChange({...automation, notification_users: userIds});
  const handleScheduledChange = (isEnabled: boolean) => {
    onScheduledChange(isEnabled);
    if (isEnabled) onAutomationChange({...automation});
  };

  const handleCronExpressionChange = (cronExpression: CronExpression) =>
    onAutomationChange({
      ...automation,
      cron_expression: cronExpression,
    });

  const canViewAllJobs = isGranted('pim_enrich_job_tracker_view_all_jobs');

  return (
    <SpacedSection>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.job_automation.title')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('akeneo.job_automation.scheduling.enable')}>
        <BooleanInput
          noLabel={translate('pim_common.no')}
          value={scheduled}
          yesLabel={translate('pim_common.yes')}
          readOnly={false}
          onChange={handleScheduledChange}
        />
        {scheduledValidationErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      {scheduled && (
        <>
          <div>
            <SectionTitle>
              <SectionTitle.Title level="secondary">
                {translate('akeneo.job_automation.scheduling.title')}
              </SectionTitle.Title>
            </SectionTitle>
            {!canViewAllJobs && <Helper>{translate('akeneo.job_automation.scheduling.cannot_view_all_jobs')}</Helper>}
          </div>
          <CronExpressionForm
            cronExpression={automation.cron_expression}
            onCronExpressionChange={handleCronExpressionChange}
            validationErrors={filterErrors(automationValidationErrors, '[cron_expression]')}
          />
          {isEnabled('permission') && (
            <UserGroupsForm
              userGroups={automation.running_user_groups}
              onUserGroupsChange={handleRunningUserGroupsChange}
              validationErrors={filterErrors(automationValidationErrors, '[running_user_groups]')}
              label={translate('akeneo.job_automation.scheduling.running_user_groups.label')}
              disabledHelperMessage={translate('akeneo.job_automation.scheduling.running_user_groups.disabled_helper')}
            />
          )}
          <SectionTitle>
            <SectionTitle.Title level="secondary">
              {translate('akeneo.job_automation.notification.title')}
            </SectionTitle.Title>
          </SectionTitle>
          <UserGroupsForm
            userGroups={automation.notification_user_groups}
            onUserGroupsChange={handleNotificationUserGroupsChange}
            validationErrors={filterErrors(automationValidationErrors, '[notification_user_groups]')}
            label={translate('akeneo.job_automation.notification.user_groups.label')}
            disabledHelperMessage={translate('akeneo.job_automation.notification.user_groups.disabled_helper')}
          />
          <UsersForm
            users={automation.notification_users}
            onUsersChange={handleNotificationUsersChange}
            validationErrors={filterErrors(automationValidationErrors, '[notification_users]')}
          />
        </>
      )}
    </SpacedSection>
  );
};

export type {JobAutomationFormProps};
export {JobAutomationForm};
