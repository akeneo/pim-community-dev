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

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

const configurators = {
  enabled: EnabledConfigurator,
  parent: ParentConfigurator,
  groups: GroupsConfigurator,
  categories: CategoriesConfigurator,
  family: FamilyConfigurator,
  family_variant: FamilyVariantConfigurator,
} as const;

const getConfigurator = (sourceCode: string): FunctionComponent<PropertyConfiguratorProps> | null => {
  return configurators[sourceCode] ?? null;
};

type PropertySourceConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const PropertySourceConfigurator = ({source, validationErrors, onSourceChange}: PropertySourceConfiguratorProps) => {
  const Configurator = getConfigurator(source.code);

  if (null === Configurator) {
    console.error(`No configurator found for "${source.code}" source code`);

    return null;
  }

  return (
    <Container>
      <Configurator source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
    </Container>
  );
};

export {PropertySourceConfigurator};
