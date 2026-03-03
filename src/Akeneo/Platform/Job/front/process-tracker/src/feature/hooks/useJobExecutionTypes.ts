import {useEffect, useState} from 'react';
import {useRoute, useIsMounted} from '@akeneo-pim-community/shared';

const useJobExecutionTypes = (): string[] | null => {
  const [jobExecutionTypes, setJobExecutionTypes] = useState<string[] | null>(null);
  const route = useRoute('akeneo_job_get_job_type_action');
  const isMounted = useIsMounted();

  useEffect(() => {
    const fetchJobExecutionTypes = async () => {
      const response = await fetch(route, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (isMounted()) {
        setJobExecutionTypes(await response.json());
      }
    };

    fetchJobExecutionTypes();
  }, [route, isMounted]);

  return jobExecutionTypes;
};

export {useJobExecutionTypes};
