import {useEffect, useState} from 'react';
import {useIsMounted} from 'akeneo-design-system';
import {AssetFamily} from '../../models';
import {useFetchers} from '../../contexts';

const useAssetFamily = (assetFamilyIdentifier: string) => {
  const assetFamilyFetcher = useFetchers().assetFamily;
  const [assetFamily, setAssetFamily] = useState<AssetFamily | null>(null);
  const isMounted = useIsMounted();

  useEffect(() => {
    assetFamilyFetcher.fetchByIdentifier(assetFamilyIdentifier).then((assetFamily: AssetFamily | undefined) => {
      if (!isMounted()) return;

      setAssetFamily(assetFamily ?? null);
    });
  }, [assetFamilyIdentifier, assetFamilyFetcher, isMounted]);

  return assetFamily?.identifier === assetFamilyIdentifier ? assetFamily : null;
};

export {useAssetFamily};
