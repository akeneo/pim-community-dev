import {ValidationError} from '@akeneo-pim-community/shared';

type FrequencyConfiguratorProps = {
  cronExpression: string;
  validationErrors: ValidationError[];
  onCronExpressionChange: (cronExpression: string) => void;
};

export type {FrequencyConfiguratorProps};
