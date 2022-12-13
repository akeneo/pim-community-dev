import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {AmazonS3Storage, GoogleCloudStorage, SftpStorage} from '../components';
import {isAmazonS3Storage, isGoogleCloudStorage, isSftpStorage} from '../components/StorageConfigurator';

const isSftpConnectionFieldFulfilled = (storage: SftpStorage): boolean => {
  return (
    '' !== storage.file_path &&
    '' !== storage.host &&
    !isNaN(storage.port) &&
    '' !== storage.username &&
    (('password' === storage.login_type && '' !== storage.password) || 'private_key' === storage.login_type)
  );
};

const isAmazonS3ConnectionFieldFulfilled = (storage: AmazonS3Storage): boolean => {
  return (
    '' !== storage.file_path &&
    '' !== storage.region &&
    '' !== storage.bucket &&
    '' !== storage.key &&
    '' !== storage.secret
  );
};

const isGoogleCloudConnectionFieldFulfilled = (storage: GoogleCloudStorage): boolean => {
  return (
    '' !== storage.file_path && '' !== storage.project_id && '' !== storage.service_account && '' !== storage.bucket
  );
};

const useCheckStorageConnection = (storage: SftpStorage | AmazonS3Storage | GoogleCloudStorage) => {
  const [isValid, setValid] = useState<boolean | undefined>(undefined);
  const [isChecking, setIsChecking] = useState<boolean>(false);
  const route = useRoute('pimee_job_automation_get_storage_connection_check');

  const canCheckConnection =
    !isChecking &&
    !isValid &&
    ((isSftpStorage(storage) && isSftpConnectionFieldFulfilled(storage)) ||
      (isAmazonS3Storage(storage) && isAmazonS3ConnectionFieldFulfilled(storage)) ||
      (isGoogleCloudStorage(storage) && isGoogleCloudConnectionFieldFulfilled(storage)));

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
