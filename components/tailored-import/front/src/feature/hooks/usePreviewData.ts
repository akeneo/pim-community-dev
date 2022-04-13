import {useEffect, useState} from "react";
import {useRoute, ValidationError} from "@akeneo-pim-community/shared";
import {DataMapping, PreviewData} from "../models";
import {formatParameters} from "@akeneo-pim-community/shared/lib/models/validation-error";

const usePreviewData = (dataMapping: DataMapping) => {
  const route = useRoute('pimee_tailored_import_generate_preview_data_action');
  const [isLoading, setIsLoading] = useState(false);
  const [previewData, setPreviewData] = useState<PreviewData[]>([]);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);

  useEffect(() => {
    const fetchPreviewData = async () => {
      setIsLoading(true);
      const response = await fetch(route, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          sample_data: dataMapping.sample_data,
          operations: dataMapping.operations
        })
      });

      const data = await response.json();

      setIsLoading(false);
      setPreviewData(response.ok ? data.preview_data : []);
      setValidationErrors(response.ok ? [] : formatParameters(data));
    };

    setValidationErrors([]);
    if (dataMapping.sample_data.length === 0 || dataMapping.operations.length === 0) {
      setPreviewData([]);

      return;
    }

    void fetchPreviewData();
  }, [route, dataMapping.sample_data, dataMapping.operations]);

  return [isLoading, previewData, validationErrors] as const;
}

export { usePreviewData };
