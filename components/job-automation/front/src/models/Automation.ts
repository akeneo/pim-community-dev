import {CronExpression} from './Frequency';

type Automation = {
  cron_expression: CronExpression;
  running_user_groups: string[];
};

const filterDefaultUserGroup = (userGroups: string[]) => userGroups.filter((group: string) => group !== 'All');

export type {Automation};
export {filterDefaultUserGroup};
