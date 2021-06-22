import React from 'react';
import styled from 'styled-components';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Source} from '../../models';
import {PropertyOperations} from './PropertySourceConfigurator/PropertyOperations';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

type PropertySourceConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const PropertySourceConfigurator = ({source, validationErrors, onSourceChange}: PropertySourceConfiguratorProps) => {
  return (
    <Container>
      <PropertyOperations source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
    </Container>
  );
};

export {PropertySourceConfigurator};
