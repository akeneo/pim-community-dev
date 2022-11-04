import React, {useState} from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {Violation} from '../validators/Violation';
import {useHistory} from 'react-router-dom';
import {InvalidIdentifierGenerator} from '../errors';
import {useCreateIdentifierGenerator} from '../hooks';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const notify = useNotify();
  const translate = useTranslate();
  const history = useHistory();
  const [validationErrors, setValidationErrors] = useState<Violation[]>([]);
  const createIdentifierGenerator = useCreateIdentifierGenerator();

  const onSave = (generator: IdentifierGenerator) => {
    createIdentifierGenerator.mutate(generator, {
      onError: error => {
        if (error instanceof InvalidIdentifierGenerator) {
          notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.create.error'));
          setValidationErrors(error.violations);
        } else {
          notify(NotificationLevel.ERROR, translate('pim_error.unexpected'));
        }
      },
      onSuccess: (_data, variables) => {
        notify(
          NotificationLevel.SUCCESS,
          translate('pim_identifier_generator.flash.create.success', {code: variables.code})
        );
        history.push(`/${variables.code}`);
      },
    });
  };

  return (
    <CreateOrEditGeneratorPage
      isMainButtonDisabled={false}
      initialGenerator={initialGenerator}
      mainButtonCallback={onSave}
      validationErrors={validationErrors}
      isNew={true}
    />
  );
};

export {CreateGeneratorPage};
