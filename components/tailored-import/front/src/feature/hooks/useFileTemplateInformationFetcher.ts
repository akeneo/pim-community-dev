import {useRouter} from '@akeneo-pim-community/shared';
import {FileTemplateInformation} from '../models/FileTemplateInformation';
import {FileInfo} from 'akeneo-design-system/lib/components/Input/MediaFileInput/FileInfo';
import {FileStructure} from '../models';

const useFileTemplateInformationFetcher = () => {
  const router = useRouter();

  return async (fileInfo: FileInfo, fileStructure: FileStructure | null): Promise<FileTemplateInformation> => {
    const params = fileStructure
      ? {file_key: fileInfo.filePath, sheet_name: fileStructure.sheet_name, header_line: fileStructure.header_line}
      : {file_key: fileInfo.filePath};

    const route = router.generate('pimee_tailored_import_get_file_template_information_action', params);
    const response = await fetch(route, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const decodedResponse = await response.json();

    return response.ok ? {...decodedResponse, file_info: fileInfo} : Promise.reject(decodedResponse);
  };
};

export {useFileTemplateInformationFetcher};
