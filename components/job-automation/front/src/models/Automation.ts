import {CronExpression} from './Frequency';
import {UserGroup} from './UserGroup';

type Automation = {
  cron_expression: CronExpression;
  running_user_groups: number[];
  notification_user_groups: number[];
  notification_users: number[];
};

const removeDefaultUserGroup = (userGroups: UserGroup[]) =>
  userGroups.filter((userGroup: UserGroup) => userGroup.label !== 'All');
export type {Automation};
export {removeDefaultUserGroup};
