import {CronExpression} from './Frequency';

type Automation = {
  cron_expression: CronExpression;
  running_user_groups: string[];
  notification_user_groups: string[];
  notification_users: string[];
};

const filterDefaultUserGroup = (userGroups: string[]) => userGroups.filter((userGroup: string) => userGroup !== 'All');

export type {Automation};
export {filterDefaultUserGroup};
