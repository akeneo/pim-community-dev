type Automation = {
  running_user_groups: string[];
};

const filterDefaultUserGroup = (userGroups: string[]) => userGroups.filter((group: string) => group !== 'All');

export {filterDefaultUserGroup};
export type {Automation};
