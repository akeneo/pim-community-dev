import {useState} from 'react';
import {IdentifierGenerator} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {Violation} from '../validators/Violation';
import {useHistory} from 'react-router-dom';

type OnSaveIdentifierProps = () => {
  onSave: (generator: IdentifierGenerator) => void;
  validationErrors: Violation[];
};

const useSaveIdentifierGenerator: OnSaveIdentifierProps = () => {
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

  return {onSave, validationErrors};
};

export {useSaveIdentifierGenerator};
