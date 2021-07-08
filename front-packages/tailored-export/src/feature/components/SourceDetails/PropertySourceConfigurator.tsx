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

const getConfigurator = (sourceCode: string): FunctionComponent<PropertyConfiguratorProps> | null => {
  switch (sourceCode) {
    case 'enabled':
      return EnabledConfigurator;
    case 'parent':
      return ParentConfigurator;
    case 'groups':
      return GroupsConfigurator;
    case 'categories':
      return CategoriesConfigurator;
    case 'family':
      return FamilyConfigurator;
    case 'family_variant':
      return FamilyVariantConfigurator;
    default:
      return null;
  }
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
