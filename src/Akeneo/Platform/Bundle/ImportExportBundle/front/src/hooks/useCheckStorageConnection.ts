import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {
  isSftpStorage,
  isAmazonS3Storage,
  isMicrosoftAzureStorage,
  isGoogleCloudStorage,
  SftpStorage,
  AmazonS3Storage,
  GoogleCloudStorage,
  MicrosoftAzureStorage,
  isAmazonS3ConnectionFieldFulfilled,
  isGoogleCloudConnectionFieldFulfilled,
  isMicrosoftAzureConnectionFieldFulfilled,
  isSftpConnectionFieldFulfilled,
} from '../models';

const useCheckStorageConnection = (
  jobInstanceCode: string,
  storage: SftpStorage | AmazonS3Storage | MicrosoftAzureStorage | GoogleCloudStorage
) => {
  const [isValid, setValid] = useState<boolean | undefined>(undefined);
  const [isChecking, setIsChecking] = useState<boolean>(false);
  const route = useRoute('pimee_job_automation_get_storage_connection_check', {jobInstanceCode});

  const canCheckConnection =
    !isChecking &&
    !isValid &&
    ((isSftpStorage(storage) && isSftpConnectionFieldFulfilled(storage)) ||
      (isAmazonS3Storage(storage) && isAmazonS3ConnectionFieldFulfilled(storage)) ||
      (isMicrosoftAzureStorage(storage) && isMicrosoftAzureConnectionFieldFulfilled(storage)) ||
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
