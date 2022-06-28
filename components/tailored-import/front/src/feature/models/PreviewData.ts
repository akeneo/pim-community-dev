type ArrayValue = {
  type: 'array';
  value: string[];
};

type InvalidValue = {
  type: 'invalid';
  error_key: string;
};

type ScalarValue = {
  type: 'number' | 'string' | 'date';
  value: string;
};

type BooleanValue = {
  type: 'boolean';
  value: boolean;
};

type MeasurementValue = {
  type: 'measurement';
  value: string;
  unit: string;
};

type NullValue = {
  type: 'null';
  value: null;
};

type PreviewData = ArrayValue | BooleanValue | InvalidValue | ScalarValue | MeasurementValue | NullValue;

type OperationPreviewData = {
  [uuid: string]: PreviewData[];
};

export type {PreviewData, OperationPreviewData};
