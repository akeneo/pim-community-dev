import React from 'react';
import styled from 'styled-components';
import {SectionTitle, Field, BooleanInput, Helper} from 'akeneo-design-system';
import {Section, useTranslate, ValidationError, filterErrors, useFeatureFlags} from '@akeneo-pim-community/shared';
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
  validationErrors: ValidationError[];
  onScheduledChange: (scheduled: boolean) => void;
  onAutomationChange: (automation: Automation) => void;
};

const JobAutomationForm = ({
  scheduled,
  automation,
  validationErrors,
  onScheduledChange,
  onAutomationChange,
}: JobAutomationFormProps) => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();

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
        {filterErrors(validationErrors, '[scheduled]').map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      {scheduled && (
        <>
          <SectionTitle>
            <SectionTitle.Title level="secondary">
              {translate('akeneo.job_automation.scheduling.title')}
            </SectionTitle.Title>
          </SectionTitle>
          <CronExpressionForm
            cronExpression={automation.cron_expression}
            onCronExpressionChange={handleCronExpressionChange}
            validationErrors={filterErrors(validationErrors, '[cron_expression]')}
          />
          {isEnabled('permission') && (
            <UserGroupsForm
              userGroups={automation.running_user_groups}
              onUserGroupsChange={handleRunningUserGroupsChange}
              validationErrors={filterErrors(validationErrors, '[running_user_groups]')}
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
            validationErrors={filterErrors(validationErrors, '[notification_user_groups]')}
            label={translate('akeneo.job_automation.notification.user_groups.label')}
            disabledHelperMessage={translate('akeneo.job_automation.notification.user_groups.disabled_helper')}
          />
          <UsersForm
            users={automation.notification_users}
            onUsersChange={handleNotificationUsersChange}
            validationErrors={filterErrors(validationErrors, '[notification_users]')}
          />
        </>
      )}
    </SpacedSection>
  );
};

export type {JobAutomationFormProps};
export {JobAutomationForm};
