import {ValidationError} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {Source} from './Source';

type PropertyConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

type AttributeConfiguratorProps = {
  source: Source;
  attribute: Attribute;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

export type {PropertyConfiguratorProps, AttributeConfiguratorProps};
