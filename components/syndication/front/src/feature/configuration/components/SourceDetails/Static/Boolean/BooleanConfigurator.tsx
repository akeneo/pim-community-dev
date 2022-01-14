import React from 'react';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {StaticConfiguratorProps} from '../../../../models';
import {isStaticBooleanSource} from './model';
import {InvalidAttributeSourceError} from '../../error';
import {BooleanReplacement, Operations} from '../../common';
import {BooleanValueGeneratorConfigurator} from './BooleanValueGenerator';
import styled from 'styled-components';

const Container = styled.div`
  padding-top: 10px;
`;

const BooleanConfigurator = ({source, requirement, validationErrors, onSourceChange}: StaticConfiguratorProps) => {
  const translate = useTranslate();
  if (!isStaticBooleanSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for boolean configurator`);
  }

  return (
    <Container>
      <BooleanValueGeneratorConfigurator
        value={source.value}
        validationErrors={filterErrors(validationErrors, '[value]')}
        onValueChange={newValue => onSourceChange({...source, value: newValue})}
      />
      {0 !== Object.keys(source.operations).length && (
        <Operations>
          {'string' === requirement.type && (
            <BooleanReplacement
              trueLabel={translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.yes')}
              falseLabel={translate('akeneo.syndication.data_mapping_details.sources.operation.replacement.no')}
              operation={source.operations.replacement}
              validationErrors={filterErrors(validationErrors, '[operations][replacement]')}
              onOperationChange={updatedOperation =>
                onSourceChange({...source, operations: {...source.operations, replacement: updatedOperation}})
              }
            />
          )}
        </Operations>
      )}
    </Container>
  );
};

export {BooleanConfigurator};
