import React, {FunctionComponent} from 'react';
import styled from 'styled-components';
import {ValidationError} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../models';
import {Source} from '../../models/Source';
import {EnabledConfigurator} from './Enabled/EnabledConfigurator';
import {GroupsConfigurator} from './Groups/GroupsConfigurator';
import {ParentConfigurator} from './Parent/ParentConfigurator';
import {FamilyVariantConfigurator} from './FamilyVariant/FamilyVariantConfigurator';
import {FamilyConfigurator} from './Family/FamilyConfigurator';
import {CategoriesConfigurator} from './Categories/CategoriesConfigurator';
import {ErrorBoundary} from './error';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

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
      <Container>
        <Configurator source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
      </Container>
    </ErrorBoundary>
  );
};

export {PropertySourceConfigurator};
