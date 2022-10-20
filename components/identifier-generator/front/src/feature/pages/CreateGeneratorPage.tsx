import React, {useState} from 'react';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {useHistory} from 'react-router-dom';
import {Violation} from '../validators/Violation';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const translate = useTranslate();
  const router = useRouter();
  const history = useHistory();
  const [validationErrors, setValidationErrors] = useState<Violation[]>([]);

  const onSave = (generator: IdentifierGenerator) => {
    fetch(router.generate('akeneo_identifier_generator_rest_create'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(generator),
    }).then(response => {
      if (response.status === 400) {
        response.json().then(json => {
          setValidationErrors(json);
        });
      } else {
        response.json().then(json => {
          // TODO Add flash message
          const code = json.code;
          history.push(`/${code}`);
        });
      }
    });
  };

  return <CreateOrEditGeneratorPage
    initialGenerator={initialGenerator}
    mainButtonLabel={translate('pim_common.save')}
    mainButtonCallback={onSave}
    validationErrors={validationErrors}
  />;
};

export {CreateGeneratorPage};
