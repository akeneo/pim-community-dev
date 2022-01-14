import React from 'react';
import {filterErrors} from '@akeneo-pim-community/shared';
import {StaticConfiguratorProps} from '../../../../models';
import {isStaticStringSource} from './model';
import {InvalidAttributeSourceError} from '../../error';
import {StringValueGeneratorConfigurator} from './StringValueGenerator';
import styled from 'styled-components';

const Container = styled.div`
  padding-top: 10px;
`;

const StringConfigurator = ({source, requirement, validationErrors, onSourceChange}: StaticConfiguratorProps) => {
  if (!isStaticStringSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for string configurator`);
  }

  return (
    <Container>
      <StringValueGeneratorConfigurator
        value={source.value}
        validationErrors={filterErrors(validationErrors, '[value]')}
        onValueChange={newValue => onSourceChange({...source, value: newValue})}
      />
    </Container>
  );
};

export {StringConfigurator};
