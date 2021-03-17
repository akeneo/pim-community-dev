import {useEffect, useState} from 'react';

const Routing = require('routing');
const DataCollector = require('pim/data-collector');

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

  useEffect(() => {
    (async () => {
      const result = await fetch(Routing.generate('pim_dashboard_version_data'), {
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
    if (isAnalyticsWanted && analyticsUrl) {
      (async () => {
        const collectedData = await DataCollector.collect('pim_analytics_data_collect');
        const response = await fetch(`${analyticsUrl}?${$.param(collectedData)}`);
        if (isLastPatchDisplayed) {
          const lastPatchInfo: {last_patch: {name: string}} = await response.json();
          setLastPatch(lastPatchInfo.last_patch.name);
        }
      })();
    }
  }, [isAnalyticsWanted, analyticsUrl]);

  return {
    version,
    lastPatch,
  };
};

export {usePimVersion};
