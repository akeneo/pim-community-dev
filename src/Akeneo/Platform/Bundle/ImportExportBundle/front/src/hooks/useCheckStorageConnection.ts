import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {AmazonS3Storage, SftpStorage} from '../components';

const useCheckStorageConnection = (storage: SftpStorage | AmazonS3Storage) => {
  const [isValid, setValid] = useState<boolean | undefined>(undefined);
  const [isChecking, setIsChecking] = useState<boolean>(false);
  const route = useRoute('pimee_job_automation_get_storage_connection_check');

  const canCheckConnection = !isChecking && !isValid;
  //  &&
  // '' !== storage.file_path &&
  // '' !== storage.host &&
  // !isNaN(storage.port) &&
  // '' !== storage.username &&
  // (('password' === storage.login_type && '' !== storage.password) || 'private_key' === storage.login_type);

  useEffect(() => {
    return () => {
      setValid(undefined);
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

    setValid(response.ok);
    setIsChecking(false);
  };

  return [isValid, canCheckConnection, checkReliability] as const;
};

export {useCheckStorageConnection};
