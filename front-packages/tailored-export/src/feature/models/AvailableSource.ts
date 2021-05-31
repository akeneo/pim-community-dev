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

export type {AvailableSource, AvailableSourceGroup};
