import {MeasurementFamilyCode} from '../../../model/measurement-family';
import {Unit} from '../../../model/unit';
import {useRouter} from '@akeneo-pim-community/legacy';
import {ValidationError} from '@akeneo-pim-community/shared';

type ValidatorResult = {
  valid: boolean;
  errors: ValidationError[];
};

type Validator = (measurementFamilyCode: MeasurementFamilyCode, data: Unit) => Promise<ValidatorResult>;

const useCreateUnitValidator = (): Validator => {
  const router = useRouter();

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
        errors: await response.json(),
      };
    }

    return {
      valid: true,
      errors: [],
    };
  };
};

export {useCreateUnitValidator};
