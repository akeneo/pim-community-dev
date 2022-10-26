import React, {useState} from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {Violation} from '../validators/Violation';
import {useHistory} from 'react-router-dom';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();
  const history = useHistory();
  const [validationErrors, setValidationErrors] = useState<Violation[]>([]);

  const onSave = async (generator: IdentifierGenerator) => {
    const response = await fetch(router.generate('akeneo_identifier_generator_rest_create'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(generator),
    });

    if (response.status >= 400 && response.status < 500) {
      const json = await response.json();
      notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.create.error'));
      setValidationErrors(json);
    } else if (response.status === 201) {
      notify(NotificationLevel.SUCCESS, translate('pim_identifier_generator.flash.create.success'));
      history.push(`/${generator.code}`);
    } else {
      /* istanbul ignore next */
      notify(NotificationLevel.ERROR, translate('pim_error.unexpected'));
    }
  };

  return (
    <CreateOrEditGeneratorPage
      initialGenerator={initialGenerator}
      mainButtonCallback={onSave}
      validationErrors={validationErrors}
    />
  );
};

export {CreateGeneratorPage};
