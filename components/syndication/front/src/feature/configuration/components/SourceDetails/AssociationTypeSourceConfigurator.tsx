import React from 'react';
import {Helper} from 'akeneo-design-system';
import {useTranslate, getErrorsForPath, ValidationError} from '@akeneo-pim-community/shared';
import {AssociationTypeSource, Requirement} from '../../models';
import {useAssociationType} from '../../hooks';
import {SimpleAssociationTypeConfigurator} from './SimpleAssociationType/SimpleAssociationTypeConfigurator';
import {QuantifiedAssociationTypeConfigurator} from './QuantifiedAssociationType/QuantifiedAssociationTypeConfigurator';
import {DeletedAssociationTypeSourcePlaceholder, ErrorBoundary} from './error';

type AssociationTypeSourceConfiguratorProps = {
  source: AssociationTypeSource;
  requirement: Requirement;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: AssociationTypeSource) => void;
};

const AssociationTypeSourceConfigurator = ({
  source,
  requirement,
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
      <Configurator
        source={source}
        validationErrors={validationErrors}
        onSourceChange={onSourceChange}
        requirement={requirement}
      />
    </ErrorBoundary>
  );
};

export {AssociationTypeSourceConfigurator};
