import {useRouter} from '@akeneo-pim-community/shared';
import {SampleData} from '../models';

const useRefreshedSampleDataFetcher = (): ((
  fileKey: string,
  currentSample: SampleData[],
  columnIndex: number,
  sheetName: string | null,
  productLine: number
) => Promise<string>) => {
  const router = useRouter();

  return (
    fileKey: string,
    currentSample: SampleData[],
    columnIndex: number,
    sheetName: string | null,
    productLine: number
  ): Promise<string> => {
    const route = router.generate('pimee_tailored_import_get_refreshed_sample_data_action', {
      current_sample: currentSample,
      file_key: fileKey,
      column_index: columnIndex,
      sheet_name: sheetName,
      product_line: productLine,
    });

    return new Promise<string>(async (resolve, reject) => {
      const response = await fetch(route, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (response.ok) {
        const sampleData = await response.json();
        resolve(sampleData.refreshed_data);

        return;
      }

      reject();
    });
  };
};

export {useRefreshedSampleDataFetcher};
