import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {DataMapping, PreviewData} from '../models';

const usePreviewData = (dataMapping: DataMapping) => {
  const route = useRoute('pimee_tailored_import_generate_preview_data_action');
  const [isLoading, setIsLoading] = useState(false);
  const [previewData, setPreviewData] = useState<PreviewData[]>([]);
  const [hasError, setHasError] = useState<boolean>(false);

  useEffect(() => {
    const fetchPreviewData = async () => {
      const response = await fetch(route, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          sample_data: dataMapping.sample_data,
          operations: dataMapping.operations,
        }),
      });

      const data = await response.json();

      setIsLoading(false);
      setPreviewData(response.ok ? data.preview_data : []);
      setHasError(!response.ok);
    };

    setHasError(false);
    if (dataMapping.sample_data.length === 0 || dataMapping.operations.length === 0) {
      setPreviewData([]);

      return;
    }

    setIsLoading(true);
    void fetchPreviewData();
  }, [route, dataMapping.sample_data, dataMapping.operations]);

  return [isLoading, previewData, hasError] as const;
};

export {usePreviewData};
