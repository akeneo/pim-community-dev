import {CronExpression} from './Frequency';

type AutomationConfiguration = {
  scheduled: boolean;
  automation: {
    cron_expression: CronExpression;
    running_user_groups: string[];
  }
};

const filterDefaultUserGroup = (userGroups: string[]) => userGroups.filter((group: string) => group !== 'All');

export type {AutomationConfiguration};
export {filterDefaultUserGroup};
