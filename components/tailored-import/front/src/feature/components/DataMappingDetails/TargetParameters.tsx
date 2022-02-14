import React from 'react';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {isAttributeTarget, Target} from '../../models';
import {AttributeTargetParameters} from './AttributeTargetParameters';
import {PropertyTargetParameters} from './PropertyTargetParameters';

type TargetParametersProps = {
  target: Target;
  validationErrors: ValidationError[];
  onTargetChange: (target: Target) => void;
};

const TargetParameters = ({target, validationErrors, onTargetChange}: TargetParametersProps) => {
  const translate = useTranslate();
  const codeErrors = filterErrors(validationErrors, '[code]');

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.target.title')}
        </SectionTitle.Title>
      </SectionTitle>
      {codeErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      {isAttributeTarget(target) ? (
        <AttributeTargetParameters
          target={target}
          validationErrors={validationErrors}
          onTargetChange={onTargetChange}
        />
      ) : (
        <PropertyTargetParameters target={target} onTargetChange={onTargetChange} />
      )}
    </>
  );
};

export {TargetParameters};
