import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {useHistory} from 'react-router-dom';
import {useCreateIdentifierGenerator} from '../hooks';
import {useIdentifierGeneratorContext} from '../context';
import {useQueryClient} from 'react-query';
import {validateIdentifierGenerator, Violation} from '../validators';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const notify = useNotify();
  const translate = useTranslate();
  const history = useHistory();
  const queryClient = useQueryClient();
  const [validationErrors, setValidationErrors] = useState<Violation[]>([]);
  const {mutate, error, isLoading} = useCreateIdentifierGenerator();
  const identifierGeneratorContext = useIdentifierGeneratorContext();
  const errors = useMemo(
    () => (validationErrors.length > 0 ? validationErrors : error?.violations || []),
    [error.violations, validationErrors]
  );

  useEffect(() => {
    identifierGeneratorContext.unsavedChanges.setHasUnsavedChanges(true);
  }, [identifierGeneratorContext.unsavedChanges]);

  const onSave = useCallback(
    (generator: IdentifierGenerator) => {
      const validationErrors = validateIdentifierGenerator(generator);
      if (validationErrors.length > 0) {
        setValidationErrors(validationErrors);
        return;
      }
      setValidationErrors([]);

      mutate(generator, {
        onError: error => {
          if (error.violations) {
            notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.create.error'));
          } else {
            notify(NotificationLevel.ERROR, translate('pim_error.unexpected'));
          }
        },
        onSuccess: ({code}: IdentifierGenerator) => {
          queryClient.invalidateQueries('getIdentifierGenerator');
          queryClient.invalidateQueries('getGeneratorList');
          notify(NotificationLevel.SUCCESS, translate('pim_identifier_generator.flash.create.success', {code}));
          identifierGeneratorContext.unsavedChanges.setHasUnsavedChanges(false);
          history.push(`/${code}`);
        },
      });
    },
    [history, identifierGeneratorContext.unsavedChanges, mutate, notify, queryClient, translate]
  );

  return (
    <CreateOrEditGeneratorPage
      isMainButtonDisabled={isLoading}
      initialGenerator={initialGenerator}
      mainButtonCallback={onSave}
      validationErrors={errors}
      isNew={true}
    />
  );
};

export {CreateGeneratorPage};
