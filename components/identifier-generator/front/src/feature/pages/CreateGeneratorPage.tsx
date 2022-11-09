import React from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {useHistory} from 'react-router-dom';
import {useCreateIdentifierGenerator} from '../hooks';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const notify = useNotify();
  const translate = useTranslate();
  const history = useHistory();
  const {mutate, error, isLoading} = useCreateIdentifierGenerator();

  const onSave = (generator: IdentifierGenerator) => {
    mutate(generator, {
      onError: error => {
        // @ts-ignore
        if (error.violations) {
          notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.create.error'));
        } else {
          notify(NotificationLevel.ERROR, translate('pim_error.unexpected'));
        }
      },
      onSuccess: ({code}: IdentifierGenerator) => {
        notify(
          NotificationLevel.SUCCESS,
          translate('pim_identifier_generator.flash.create.success', {code})
        );
        history.push(`/${code}`);
      },
    });
  };

  return (
    <CreateOrEditGeneratorPage
      isMainButtonDisabled={isLoading}
      initialGenerator={initialGenerator}
      mainButtonCallback={onSave}
      validationErrors={error?.violations || []}
      isNew={true}
    />
  );
};

export {CreateGeneratorPage};
