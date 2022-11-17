import React, {useCallback} from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {useSaveGenerator} from '../hooks/useSaveGenerator';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {useQueryClient} from 'react-query';
import {useIdentifierGeneratorContext} from '../context/useIdentifierGeneratorContext';

type EditGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const EditGeneratorPage: React.FC<EditGeneratorProps> = ({initialGenerator}) => {
  const queryClient = useQueryClient();
  const notify = useNotify();
  const translate = useTranslate();
  const {save, isLoading, error} = useSaveGenerator();
  const identifierGeneratorContext = useIdentifierGeneratorContext();

  const onSave = useCallback(
    (generator: IdentifierGenerator) => {
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
          queryClient.invalidateQueries({queryKey: ['getIdentifierGenerator']});
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
      validationErrors={error}
      isNew={false}
    />
  );
};

export {EditGeneratorPage};
