import {useRouter} from '@akeneo-pim-community/shared';
import {Column, FileStructure} from '../models';

const useReadColumns = () => {
  const router = useRouter();

  const readColumns = async (fileKey: string, fileStructure: FileStructure): Promise<Column[]> => {
    const response = await fetch(router.generate('pimee_tailored_import_read_columns_action'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        file_key: fileKey,
        file_structure: fileStructure,
      }),
    });

    const decodedResponse = await response.json();

    return response.ok ? decodedResponse : Promise.reject(decodedResponse);
  };

  return readColumns;
};

export {useReadColumns};
