type CounterValue = number;

type AverageMaxValue = {
  average: number;
  max: number;
};

type KeyFigure = {
  name: string;
  type: string;
  value: AverageMaxValue | CounterValue;
};

type Volume = {
  name: string;
  keyFigures: KeyFigure[];
}

export type {KeyFigure, Volume, AverageMaxValue, CounterValue};
