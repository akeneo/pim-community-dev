import {useRouter} from '@akeneo-pim-community/legacy-bridge';

enum MeasurementFamilyRemoverResult {
  Success = 'Success',
  Unprocessable = 'Unprocessable',
  NotFound = 'NotFound',
}

type Remover = (measurementFamilyCode: string) => Promise<MeasurementFamilyRemoverResult>;

const useMeasurementFamilyRemover = (): Remover => {
  const router = useRouter();

  return async (measurementFamilyCode: string) => {
    const response = await fetch(
      router.generate('akeneo_measurements_measurement_family_delete_rest', {
        code: measurementFamilyCode,
      }),
      {
        method: 'DELETE',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      }
    );

    switch (response.status) {
      case 204:
        return MeasurementFamilyRemoverResult.Success;
      case 404:
        return MeasurementFamilyRemoverResult.NotFound;
      case 422:
        return MeasurementFamilyRemoverResult.Unprocessable;
      default:
        throw Error('The DELETE endpoint returned an unexpected response');
    }
  };
};

export {useMeasurementFamilyRemover, MeasurementFamilyRemoverResult};
