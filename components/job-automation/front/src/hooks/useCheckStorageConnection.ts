import {useEffect, useState} from 'react';
import {SftpStorage} from '../components';
import {useRoute} from '@akeneo-pim-community/shared/lib/hooks/useRoute';

const useCheckStorageConnection = (storage: SftpStorage) => {
  const [check, setCheck] = useState<boolean>();
  const [isChecking, setIsChecking] = useState<boolean>(false);
  const route = useRoute('pimee_job_automation_get_storage_connection_check');

  useEffect(() => {
    return () => {
      setCheck(undefined);
    };
  }, [storage]);

  const checkReliability = async () => {
    setIsChecking(true);
    const response = await fetch(route, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(storage),
    });

    response.ok ? setCheck(true) : setCheck(false);
    setIsChecking(false);
  };

  return [check, isChecking, checkReliability] as const;
};

export {useCheckStorageConnection};
