import React, {useState} from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {Violation} from '../validators/Violation';
import {useHistory} from 'react-router-dom';
import {useGetGenerators} from '../hooks';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();
  const history = useHistory();
  const {refetch} = useGetGenerators();
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
      notify(
        NotificationLevel.SUCCESS,
        translate('pim_identifier_generator.flash.create.success', {code: generator.code})
      );
      refetch();
      history.push(`/configuration/identifier-generator/${generator.code}`);
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
      isNew={true}
    />
  );
};

export {CreateGeneratorPage};
