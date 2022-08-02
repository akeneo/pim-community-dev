import {ValidationError} from '@akeneo-pim-community/shared';
import {FrequencyOption} from '../../models';

type FrequencyConfiguratorProps = {
  frequencyOption: FrequencyOption;
  cronExpression: string;
  validationErrors: ValidationError[];
  onCronExpressionChange: (cronExpression: string) => void;
};

export type {FrequencyConfiguratorProps};
