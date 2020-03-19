import {useContext} from 'react';
import {RouterContext} from 'akeneomeasure/context/router-context';
import {Unit} from 'akeneomeasure/model/measurement-family';
import {ValidationError} from 'akeneomeasure/model/validation-error';

type ValidatorResult = {
  valid: boolean;
  errors: ValidationError[];
};

type Validator = (data: Unit) => Promise<ValidatorResult>;

const useCreateUnitValidator = (): Validator => {
  const router = useContext(RouterContext);

  return async (data: Unit) => {
    const response = await fetch(router.generate('akeneo_measurements_unit_validate_rest'), {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      return {
        valid: false,
        errors: (await response.json()).errors,
      };
    }

    return {
      valid: true,
      errors: [],
    };
  };
};

export {
  useCreateUnitValidator,
};
