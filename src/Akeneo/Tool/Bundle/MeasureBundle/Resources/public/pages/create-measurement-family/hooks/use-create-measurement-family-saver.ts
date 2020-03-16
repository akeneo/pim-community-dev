import {useContext} from 'react';
import {RouterContext} from 'akeneomeasure/context/router-context';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {ValidationError} from 'akeneomeasure/model/validation-error';

type SaverResult = {
  success: boolean;
  errors: ValidationError[];
};

type Saver = (data: MeasurementFamily) => Promise<SaverResult>;

const useCreateMeasurementFamilySaver = (): Saver => {
  const router = useContext(RouterContext);

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
        errors: (await response.json()).errors,
      };
    }

    return {
      success: true,
      errors: [],
    };
  };
};

export {useCreateMeasurementFamilySaver};
