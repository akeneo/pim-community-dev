import {CronExpression} from './Frequency';
import {UserGroup} from './UserGroup';

type Automation = {
  cron_expression: CronExpression;
  running_user_groups: string[];
  notification_user_groups: string[];
  notification_users: string[];
};

const removeDefaultUserGroup = (userGroups: UserGroup[]) =>
  userGroups.filter((userGroup: UserGroup) => userGroup.label !== 'All').map((userGroup: UserGroup) => userGroup.label);
export type {Automation};
export {removeDefaultUserGroup};
