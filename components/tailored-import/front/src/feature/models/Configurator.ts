import {ValidationError} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {AttributeTarget, Target} from "./Target";

type AttributeConfiguratorProps = {
  target: Target
  attribute: Attribute;
  validationErrors: ValidationError[];
  onTargetChange: (updatedTarget: AttributeTarget) => void;
};

export type {AttributeConfiguratorProps};
