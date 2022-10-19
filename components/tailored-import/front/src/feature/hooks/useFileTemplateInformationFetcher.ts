import {useRouter} from '@akeneo-pim-community/shared';
import {FileTemplateInformation} from '../models';

const useFileTemplateInformationFetcher = () => {
  const router = useRouter();

  return async (fileKey: string, sheetName: string | null): Promise<FileTemplateInformation> => {
    const params = sheetName ? {file_key: fileKey, sheet_name: sheetName} : {file_key: fileKey};

    const route = router.generate('pimee_tailored_import_get_file_template_information_action', params);
    const response = await fetch(route, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const decodedResponse = await response.json();

    return response.ok ? decodedResponse : Promise.reject(decodedResponse);
  };
};

export {useFileTemplateInformationFetcher};
