type GroupCode = string;

type Group = {
  code: GroupCode;
  labels: {
    [locale: string]: string;
  };
};

export { Group, GroupCode };
