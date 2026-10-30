import {useRouter} from '@akeneo-pim-community/shared';
import {useEffect, useState} from 'react';

type PimVersion = {
  version: string;
  analytics_url: string;
  is_analytics_wanted: boolean;
};

const usePimVersion = () => {
  const [version, setVersion] = useState<string>('');
  const [isAnalyticsWanted, setIsAnalyticsWanted] = useState<boolean>(false);
  const [analyticsUrl, setAnalyticsUrl] = useState<string>('');
  const router = useRouter();

  useEffect(() => {
    (async () => {
      const result = await fetch(router.generate('pim_dashboard_version_data'), {
        method: 'GET',
      });
      const pimVersion: PimVersion = await result.json();
      setVersion(pimVersion.version);
      setIsAnalyticsWanted(pimVersion.is_analytics_wanted);
      setAnalyticsUrl(pimVersion.analytics_url);
    })();
  }, []);

  useEffect(() => {
    if (isAnalyticsWanted && analyticsUrl && !sessionStorage.getItem('analytics-called')) {
      (async () => {
        const collectedDataResponse = await fetch(router.generate('pim_analytics_data_collect'));
        const collectedData = await collectedDataResponse.json();
        await fetch(`${analyticsUrl}?${$.param(collectedData)}`);
        sessionStorage.setItem('analytics-called', '1');
      })();
    }
  }, [isAnalyticsWanted, analyticsUrl]);

  return {
    version,
  };
};

export {usePimVersion};
