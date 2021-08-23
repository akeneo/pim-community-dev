import React, {FunctionComponent} from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps, Source} from '../../models';
import {EnabledConfigurator} from './Enabled/EnabledConfigurator';
import {GroupsConfigurator} from './Groups/GroupsConfigurator';
import {ParentConfigurator} from './Parent/ParentConfigurator';
import {FamilyVariantConfigurator} from './FamilyVariant/FamilyVariantConfigurator';
import {FamilyConfigurator} from './Family/FamilyConfigurator';
import {CategoriesConfigurator} from './Categories/CategoriesConfigurator';
import {ErrorBoundary} from './error';

const configurators: {[propertyName: string]: FunctionComponent<PropertyConfiguratorProps>} = {
  enabled: EnabledConfigurator,
  parent: ParentConfigurator,
  groups: GroupsConfigurator,
  categories: CategoriesConfigurator,
  family: FamilyConfigurator,
  family_variant: FamilyVariantConfigurator,
};

type PropertySourceConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const PropertySourceConfigurator = ({source, validationErrors, onSourceChange}: PropertySourceConfiguratorProps) => {
  const Configurator = configurators[source.code] ?? null;

  if (null === Configurator) {
    console.error(`No configurator found for "${source.code}" source code`);

    return null;
  }

  return (
    <ErrorBoundary>
      <Configurator source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
    </ErrorBoundary>
  );
};

export {PropertySourceConfigurator};
