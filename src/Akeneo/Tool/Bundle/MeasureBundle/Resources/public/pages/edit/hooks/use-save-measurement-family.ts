import {useContext} from 'react';
import {RouterContext} from 'akeneomeasure/context/router-context';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {ValidationError} from 'akeneomeasure/model/validation-error';

type SaverResult = {
  success: boolean;
  errors: ValidationError[];
};

type Saver = (measurementFamily: MeasurementFamily) => Promise<SaverResult>;

const useMeasurementFamilySaver = (): Saver => {
  const router = useContext(RouterContext);

  return async (measurementFamily: MeasurementFamily) => {
    const response = await fetch(
      router.generate('akeneo_measurements_measurement_family_edit_rest', {
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
        errors: (await response.json()).errors,
      };
    }

    return {
      success: true,
      errors: [],
    };
  };
};

export {useMeasurementFamilySaver};
