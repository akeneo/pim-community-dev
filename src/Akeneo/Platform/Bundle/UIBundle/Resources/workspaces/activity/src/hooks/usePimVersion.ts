import {useRouter} from '@akeneo-pim-community/shared';
import {useCallback, useEffect, useState} from 'react';

type PimVersion = {
  version: string;
  is_last_patch_displayed: boolean;
  analytics_url: string;
  is_analytics_wanted: boolean;
};

const usePimVersion = () => {
  const [version, setVersion] = useState<string>('');
  const [isLastPatchDisplayed, setIsLastPatchDisplayed] = useState<boolean>(false);
  const [isAnalyticsWanted, setIsAnalyticsWanted] = useState<boolean>(false);
  const [analyticsUrl, setAnalyticsUrl] = useState<string>('');
  const [lastPatch, setLastPatch] = useState<string>('');
  const router = useRouter();

  const isVersionOutdated = useCallback(
    (lastPatch: string) => {
      const regexCurrentVersion = /\s(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)\s/;
      const regexLastVersion = /v(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)/;
      const matchCurrentVersion = version.match(regexCurrentVersion);
      const matchLastVersion = lastPatch.match(regexLastVersion);

      if (matchCurrentVersion === null || matchLastVersion === null) {
        return false;
      }
      const [, majorCurrentVersion, minorCurrentVersion, patchCurrentVersion] = matchCurrentVersion;
      const [, majorLastVersion, minorLastVersion, patchLastVersion] = matchLastVersion;

      if (
        parseInt(`${majorCurrentVersion}${minorCurrentVersion}${patchCurrentVersion}`) >=
        parseInt(`${majorLastVersion}${minorLastVersion}${patchLastVersion}`)
      ) {
        return false;
      }
      return true;
    },
    [version]
  );

  useEffect(() => {
    (async () => {
      const result = await fetch(router.generate('pim_dashboard_version_data'), {
        method: 'GET',
      });
      const pimVersion: PimVersion = await result.json();
      setVersion(pimVersion.version);
      setIsLastPatchDisplayed(pimVersion.is_last_patch_displayed);
      setIsAnalyticsWanted(pimVersion.is_analytics_wanted);
      setAnalyticsUrl(pimVersion.analytics_url);
    })();
  }, []);

  useEffect(() => {
    if (isAnalyticsWanted && analyticsUrl && !sessionStorage.getItem('analytics-called')) {
      (async () => {
        const collectedDataResponse = await fetch(router.generate('pim_analytics_data_collect'));
        const collectedData = await collectedDataResponse.json();
        const response = await fetch(`${analyticsUrl}?${$.param(collectedData)}`);
        sessionStorage.setItem('analytics-called', '1');
        if (isLastPatchDisplayed && response.status === 200) {
          const lastPatchInfo: {last_patch: {name: string}} = await response.json();
          if (lastPatchInfo.hasOwnProperty('last_patch') && isVersionOutdated(lastPatchInfo.last_patch.name)) {
            setLastPatch(lastPatchInfo.last_patch.name);
            sessionStorage.setItem('last-patch-available', lastPatchInfo.last_patch.name);
          }
        }
      })();
    } else if (isLastPatchDisplayed) {
      const storedLastPatch = sessionStorage.getItem('last-patch-available');
      storedLastPatch !== null && isVersionOutdated(storedLastPatch) && setLastPatch(storedLastPatch);
    }
  }, [isAnalyticsWanted, analyticsUrl, isVersionOutdated]);

  return {
    version,
    lastPatch,
  };
};

export {usePimVersion};
