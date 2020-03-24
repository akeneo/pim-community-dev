import {useContext} from 'react';
import {RouterContext} from 'akeneomeasure/context/router-context';
import {MeasurementFamilyCode} from 'akeneomeasure/model/measurement-family';
import {Unit} from 'akeneomeasure/model/unit';
import {ValidationError} from 'akeneomeasure/model/validation-error';

type ValidatorResult = {
  valid: boolean;
  errors: ValidationError[];
};

type Validator = (measurementFamilyCode: MeasurementFamilyCode, data: Unit) => Promise<ValidatorResult>;

const useCreateUnitValidator = (): Validator => {
  const router = useContext(RouterContext);

  return async (measurementFamilyCode: MeasurementFamilyCode, data: Unit) => {
    const response = await fetch(
      router.generate('akeneo_measurements_validate_unit_rest', {
        measurement_family_code: measurementFamilyCode,
      }),
      {
        method: 'POST',
        headers: [
          ['Content-type', 'application/json'],
          ['X-Requested-With', 'XMLHttpRequest'],
        ],
        body: JSON.stringify(data),
      }
    );

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

export {useCreateUnitValidator};
