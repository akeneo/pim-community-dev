import React, {FunctionComponent} from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {AssociationType, AssociationTypeConfiguratorProps, Source} from '../../models';
import styled from 'styled-components';
import {useAssociationType} from '../../hooks';
import {SimpleAssociationTypeConfigurator} from './SimpleAssociationType/SimpleAssociationTypeConfigurator';
import {QuantifiedAssociationTypeConfigurator} from "./QuantifiedAssociationType/QuantifiedAssociationTypeConfigurator";

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

const getConfigurator = (associationType: AssociationType): FunctionComponent<AssociationTypeConfiguratorProps> => {
  console.log(associationType);
  if (associationType.is_quantified) {
    return QuantifiedAssociationTypeConfigurator;
  }

  return SimpleAssociationTypeConfigurator;
};

type AssociationTypeSourceConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const AssociationTypeSourceConfigurator = ({
  source,
  validationErrors,
  onSourceChange,
}: AssociationTypeSourceConfiguratorProps) => {
  const associationType = useAssociationType(source.code);
  if (null === associationType) {
    return null;
  }

  const Configurator = getConfigurator(associationType);

  return (
    <Container>
      <Configurator
        source={source}
        validationErrors={validationErrors}
        onSourceChange={onSourceChange}
      />
    </Container>
  );
};

export {AssociationTypeSourceConfigurator};
