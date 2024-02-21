type CounterValue = number;

type AverageMaxValue = {
  average: number;
  max: number;
};

type CatalogVolume = {
  name: string;
  type: string;
  value: AverageMaxValue | CounterValue;
};

type Axis = {
  name: string;
  catalogVolumes: CatalogVolume[];
};

export type {CatalogVolume, Axis, AverageMaxValue, CounterValue};
