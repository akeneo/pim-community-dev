import React from 'react';
import styled from 'styled-components';
import {Helper} from 'akeneo-design-system';
import {useTranslate, getErrorsForPath, ValidationError} from '@akeneo-pim-community/shared';
import {Source} from '../../models';
import {useAssociationType} from '../../hooks';
import {SimpleAssociationTypeConfigurator} from './SimpleAssociationType/SimpleAssociationTypeConfigurator';
import {QuantifiedAssociationTypeConfigurator} from './QuantifiedAssociationType/QuantifiedAssociationTypeConfigurator';
import {DeletedSourcePlaceholder} from './DeletedSourcePlaceholder';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

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
  const translate = useTranslate();
  const [isFetching, associationType] = useAssociationType(source.code);
  const associationTypeErrors = getErrorsForPath(validationErrors, '');

  if (isFetching) return null;
  if (null === associationType) {
    return (
      <>
        {associationTypeErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
        <DeletedSourcePlaceholder
          message={translate('akeneo.tailored_export.column_details.sources.deleted_association_type.title')}
        />
      </>
    );
  }

  const Configurator = associationType.is_quantified
    ? QuantifiedAssociationTypeConfigurator
    : SimpleAssociationTypeConfigurator;

  return (
    <>
      {associationTypeErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      <Container>
        <Configurator source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
      </Container>
    </>
  );
};

export {AssociationTypeSourceConfigurator};
