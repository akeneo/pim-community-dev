import {useState, useCallback, useEffect} from 'react';
import {CampaignFetcher} from './../fetcher/campaign.type';

const useCampaign = (
  campaignFetcher: CampaignFetcher
): {campaign: string | null; fetchCampaign: () => Promise<void>} => {
  const [campaign, setCampaign] = useState<string | null>(null);

  const fetchCampaign = useCallback(async () => {
    setCampaign(await campaignFetcher.fetch());
  }, [setCampaign]);

  useEffect(() => {
    fetchCampaign();
  }, []);

  return {campaign, fetchCampaign};
};

export {useCampaign};
