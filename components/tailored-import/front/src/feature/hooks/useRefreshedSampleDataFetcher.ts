import {useRouter} from '@akeneo-pim-community/shared';

const useRefreshedSampleDataFetcher = (): ((
  fileKey: string,
  indexToChange: number,
  currentSample: string[],
  columnIndex: number,
  sheetName: string | null,
  productLine: number
) => Promise<string[]>) => {
  const router = useRouter();

  return (
    fileKey: string,
    indexToChange: number,
    currentSample: string[],
    columnIndex: number,
    sheetName: string | null,
    productLine: number
  ): Promise<string[]> => {
    const route = router.generate('pimee_tailored_import_get_refreshed_sample_data_action', {
      index_to_change: indexToChange,
      current_sample: currentSample,
      file_key: fileKey,
      column_index: columnIndex,
      sheet_name: sheetName,
      product_line: productLine,
  });

    return new Promise<string[]>(async (resolve, reject) => {
      const response = await fetch(route, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (response.ok) {
        const sampleData = await response.json();
        resolve(sampleData);
      }

      reject();
    });
  };
};

export {useRefreshedSampleDataFetcher};
