import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {ValidationError} from '@akeneo-pim-community/shared';

type SaverResult = {
  success: boolean;
  errors: ValidationError[];
};

type Saver = (data: MeasurementFamily) => Promise<SaverResult>;

const useCreateMeasurementFamilySaver = (): Saver => {
  const router = useRouter();

  return async (data: MeasurementFamily) => {
    const response = await fetch(router.generate('akeneo_measurements_measurement_family_create_rest'), {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(data),
    });

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

export {useCreateMeasurementFamilySaver};
