type AvailableSource = {
  code: string;
  label: string;
  type: string;
};

type AvailableSourceGroup = {
  code: string;
  label: string;
  children: AvailableSource[];
};

type SourceOffset = {
  static: number;
  system: number;
  association_type: number;
  attribute: number;
};

type AvailableSourcesResult = {
  results: AvailableSourceGroup[];
  offset: SourceOffset;
};

const defaultSourceOffset = {
  static: 0,
  system: 0,
  attribute: 0,
  association_type: 0,
};

export type {AvailableSource, AvailableSourceGroup, AvailableSourcesResult, SourceOffset};
export {defaultSourceOffset};
