import {useState} from 'react';
import {IdentifierGenerator} from '../models';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {Violation} from '../validators/Violation';
import {useHistory} from 'react-router-dom';

type OnSaveIdentifierProps = () => {
  onSave: (generator: IdentifierGenerator) => void;
  validationErrors: Violation[];
};

const useSaveIdentifierGenerator: OnSaveIdentifierProps = () => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();
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
      if (response.status >= 400 && response.status < 500) {
        response.json().then(json => {
          notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.create.error'));
          setValidationErrors(json);
        });
      } else if (response.status === 201) {
        notify(NotificationLevel.SUCCESS, translate('pim_identifier_generator.flash.create.success'));
        setValidationErrors([]);
        history.push(`/${generator.code}`);
      } else {
        notify(NotificationLevel.ERROR, translate('pim_error.unexpected'));
      }
    });
  };

  return {onSave, validationErrors};
};

export {useSaveIdentifierGenerator};
