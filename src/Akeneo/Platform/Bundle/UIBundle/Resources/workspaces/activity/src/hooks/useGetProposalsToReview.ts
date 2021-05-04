import {useEffect, useState} from 'react';
import {Proposal} from '../domain';
import {useRouter} from '@akeneo-pim-community/shared';

const useGetProposalsToReview = (): Proposal[] | null => {
  const [proposalsToReview, setProposalsToReview] = useState<Proposal[] | null>(null);
  const router = useRouter();
  const url = router.generate('pim_dashboard_widget_data', {alias: 'proposals'});

  useEffect(() => {
    (async () => {
      const result = await fetch(url, {
        method: 'GET',
      });
      if (result.ok) {
        setProposalsToReview(await result.json());
      }
    })();
  }, []);

  return proposalsToReview;
};

export {useGetProposalsToReview};
