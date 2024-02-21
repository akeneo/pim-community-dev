import React, {useCallback, useMemo, useState} from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {useSaveGenerator} from '../hooks/useSaveGenerator';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {useQueryClient} from 'react-query';
import {useIdentifierGeneratorContext} from '../context';
import {validateIdentifierGenerator, Violation} from '../validators';

type EditGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const EditGeneratorPage: React.FC<EditGeneratorProps> = ({initialGenerator}) => {
  const queryClient = useQueryClient();
  const notify = useNotify();
  const translate = useTranslate();
  const [validationErrors, setValidationErrors] = useState<Violation[]>([]);
  const {save, isLoading, error} = useSaveGenerator();
  const identifierGeneratorContext = useIdentifierGeneratorContext();
  const errors = useMemo(
    () => (validationErrors.length > 0 ? validationErrors : error || []),
    [error, validationErrors]
  );

  const onSave = useCallback(
    (generator: IdentifierGenerator) => {
      const validationErrors = validateIdentifierGenerator(generator);
      if (validationErrors.length > 0) {
        setValidationErrors(validationErrors);
        return;
      }
      setValidationErrors([]);
      save(generator, {
        onError: () => {
          notify(
            NotificationLevel.ERROR,
            translate('pim_identifier_generator.flash.create.error', {code: generator.code})
          );
        },
        onSuccess: () => {
          notify(
            NotificationLevel.SUCCESS,
            translate('pim_identifier_generator.flash.update.success', {code: generator.code})
          );
          queryClient.invalidateQueries('getIdentifierGenerator');
          queryClient.invalidateQueries('getGeneratorList');
          identifierGeneratorContext.unsavedChanges.setHasUnsavedChanges(false);
        },
      });
    },
    [notify, queryClient, save, translate, identifierGeneratorContext.unsavedChanges]
  );

  return (
    <CreateOrEditGeneratorPage
      initialGenerator={initialGenerator}
      mainButtonCallback={onSave}
      isMainButtonDisabled={isLoading}
      validationErrors={errors}
      isNew={false}
    />
  );
};

export {EditGeneratorPage};
