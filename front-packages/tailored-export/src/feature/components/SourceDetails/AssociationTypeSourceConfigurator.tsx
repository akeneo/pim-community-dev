import React from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Source} from '../../models';
import styled from 'styled-components';
import {SimpleAssociationTypeConfigurator} from './SimpleAssociationType/SimpleAssociationTypeConfigurator';
import {useAssociationType} from '../../hooks';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

type AssociationTypeConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const AssociationTypeSourceConfigurator = ({
  source,
  validationErrors,
  onSourceChange,
}: AssociationTypeConfiguratorProps) => {
  const associationType = useAssociationType(source.code);
  if (null === associationType) {
    return null;
  }

  return (
    <Container>
      <SimpleAssociationTypeConfigurator
        source={source}
        validationErrors={validationErrors}
        onSourceChange={onSourceChange}
      />
    </Container>
  );
};

export {AssociationTypeSourceConfigurator};
