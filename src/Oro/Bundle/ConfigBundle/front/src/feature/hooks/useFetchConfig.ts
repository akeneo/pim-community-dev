import {useRoute} from '@akeneo-pim-community/shared';
import {
  configBackToFront,
  ConfigServicePayloadBackend,
  ConfigServicePayloadFrontend,
} from '../models/ConfigServicePayload';
import {useEffect} from 'react';
import {FetchResult, useFetchSimpler} from './useFetchSimpler';

export function useFetchConfig(): FetchResult<ConfigServicePayloadFrontend> {
  const configUrl = useRoute('oro_config_configuration_system_get');

  const [configFetchResult, doFetchConfig] = useFetchSimpler<ConfigServicePayloadBackend, ConfigServicePayloadFrontend>(
    configUrl,
    configBackToFront
  );

  useEffect(() => {
    doFetchConfig();
  }, [doFetchConfig]);

  return configFetchResult;
}
