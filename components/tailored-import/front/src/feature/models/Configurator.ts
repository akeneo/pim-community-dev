import {ValidationError} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {AttributeTarget, Target} from './Target';

type AttributeTargetParameterConfiguratorProps = {
  target: Target;
  attribute: Attribute;
  validationErrors: ValidationError[];
  onTargetAttributeChange: (updatedTarget: AttributeTarget) => void;
};

export type {AttributeTargetParameterConfiguratorProps};
