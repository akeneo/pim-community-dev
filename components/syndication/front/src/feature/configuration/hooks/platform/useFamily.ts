import {useRouter} from '@akeneo-pim-community/shared';
import {useEffect, useState} from 'react';
import {Family} from '../../models';

const useFamily = (platformCode: string, familyCode: string | null): Family | null => {
  const router = useRouter();
  const [family, setFamily] = useState<Family | null>(null);
  const [familyCache, setFamilyCache] = useState<Record<string, Family>>({});

  useEffect(() => {
    const getFamilyRoute = router.generate('pimee_syndication_get_platform_family_action', {platformCode, familyCode});

    setFamily(family => (null === familyCode ? null : family));
    if (null === familyCode) return;

    if (undefined !== familyCache[familyCode]) {
      setFamily(familyCache[familyCode]);
      return;
    }

    (async () => {
      const response = await fetch(getFamilyRoute, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const fetchedFamily = await response.json();

      setFamily(fetchedFamily);
      setFamilyCache(familyCache => ({...familyCache, [familyCode]: fetchedFamily}));
    })();
  }, [familyCache, router, setFamily, platformCode, familyCode]);

  return family;
};

export {useFamily};
