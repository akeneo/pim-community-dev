type AvailableTarget = {
  code: string;
  label: string;
  type: string;
};

type AvailableTargetGroup = {
  code: string;
  label: string;
  children: AvailableTarget[];
};

type TargetOffset = {
  system: number;
  attribute: number;
};

type AvailableTargetsResult = {
  results: AvailableTargetGroup[];
  offset: TargetOffset;
};

const defaultTargetOffset = {
  system: 0,
  attribute: 0,
};

export type {AvailableTarget, AvailableTargetGroup, AvailableTargetsResult, TargetOffset};
export {defaultTargetOffset};
