import {useRouter} from '@akeneo-pim-community/shared';

const useFetchSampleData = (): ((
  file_key: string,
  column_index: number,
  sheet_name: string | null,
  product_line: number
) => Promise<string[]>) => {
  const router = useRouter();

  return (
    file_key: string,
    column_index: number,
    sheet_name: string | null,
    product_line: number
  ): Promise<Array<string>> => {
    const route = router.generate('pimee_tailored_import_get_sample_data_action', {
      file_key,
      column_index,
      sheet_name,
      product_line,
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
      }

      reject();
    });
  };
};

export {useFetchSampleData};
