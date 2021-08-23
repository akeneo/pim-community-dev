import React from 'react';
import {Helper} from 'akeneo-design-system';
import {useTranslate, getErrorsForPath, ValidationError} from '@akeneo-pim-community/shared';
import {Source} from '../../models';
import {useAssociationType} from '../../hooks';
import {SimpleAssociationTypeConfigurator} from './SimpleAssociationType/SimpleAssociationTypeConfigurator';
import {QuantifiedAssociationTypeConfigurator} from './QuantifiedAssociationType/QuantifiedAssociationTypeConfigurator';
import {DeletedAssociationTypeSourcePlaceholder, ErrorBoundary} from './error';

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
        <DeletedAssociationTypeSourcePlaceholder />
      </>
    );
  }

  const Configurator = associationType.is_quantified
    ? QuantifiedAssociationTypeConfigurator
    : SimpleAssociationTypeConfigurator;

  return (
    <ErrorBoundary>
      {associationTypeErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      <Configurator source={source} validationErrors={validationErrors} onSourceChange={onSourceChange} />
    </ErrorBoundary>
  );
};

export {AssociationTypeSourceConfigurator};
