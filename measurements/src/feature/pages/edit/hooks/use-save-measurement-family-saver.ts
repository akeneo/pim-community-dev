import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {ValidationError} from '@akeneo-pim-community/shared';

type SaverResult = {
  success: boolean;
  errors: ValidationError[];
};

type Saver = (measurementFamily: MeasurementFamily) => Promise<SaverResult>;

const useSaveMeasurementFamilySaver = (): Saver => {
  const router = useRouter();

  return async (measurementFamily: MeasurementFamily) => {
    const response = await fetch(
      router.generate('akeneo_measurements_measurement_family_create_save', {
        measurement_family_code: measurementFamily.code,
      }),
      {
        method: 'POST',
        headers: [
          ['Content-type', 'application/json'],
          ['X-Requested-With', 'XMLHttpRequest'],
        ],
        body: JSON.stringify(measurementFamily),
      }
    );

    if (!response.ok) {
      return {
        success: false,
        errors: await response.json(),
      };
    }

    return {
      success: true,
      errors: [],
    };
  };
};

export {useSaveMeasurementFamilySaver};
