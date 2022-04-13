import {useRouter} from '@akeneo-pim-community/shared';

const useSampleDataFetcher = (): ((
  fileKey: string,
  columnIndices: number[],
  sheetName: string | null,
  productLine: number
) => Promise<string[]>) => {
  const router = useRouter();

  return (
    fileKey: string,
    columnIndices: number[],
    sheetName: string | null,
    productLine: number
  ): Promise<Array<string>> => {
    const route = router.generate('pimee_tailored_import_get_sample_data_action', {
      file_key: fileKey,
      column_indices: columnIndices,
      sheet_name: sheetName,
      product_line: productLine,
    });

    return new Promise<Array<string>>(async (resolve, reject) => {
      const response = await fetch(route, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (response.ok) {
        const sampleData = await response.json();
        resolve(sampleData);
        return;
      }

      reject();
    });
  };
};

export {useSampleDataFetcher};
