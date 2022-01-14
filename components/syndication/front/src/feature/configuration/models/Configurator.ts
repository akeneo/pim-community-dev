import {ValidationError} from '@akeneo-pim-community/shared';
import {Requirement} from '.';
import {Attribute} from './pim/Attribute';
import {AttributeSource, AssociationTypeSource, PropertySource, StaticSource, Source} from './Source';

type PropertyConfiguratorProps = {
  requirement: Requirement;
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: PropertySource) => void;
};

type StaticConfiguratorProps = {
  requirement: Requirement;
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: StaticSource) => void;
};
type AttributeConfiguratorProps = {
  requirement: Requirement;
  source: Source;
  attribute: Attribute;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: AttributeSource) => void;
};

type AssociationTypeConfiguratorProps = {
  requirement: Requirement;
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: AssociationTypeSource) => void;
};

export type {
  PropertyConfiguratorProps,
  AssociationTypeConfiguratorProps,
  AttributeConfiguratorProps,
  StaticConfiguratorProps,
};
