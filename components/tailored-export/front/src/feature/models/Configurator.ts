import {ValidationError} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {AttributeSource, AssociationTypeSource, PropertySource, Source} from './Source';

type PropertyConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: PropertySource) => void;
};

type AttributeConfiguratorProps = {
  source: Source;
  attribute: Attribute;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: AttributeSource) => void;
};

type AssociationTypeConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: AssociationTypeSource) => void;
};

export type {PropertyConfiguratorProps, AssociationTypeConfiguratorProps, AttributeConfiguratorProps};
