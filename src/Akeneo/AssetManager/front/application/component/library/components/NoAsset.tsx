import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {NoDataSection, NoDataTitle, NoDataText} from '@akeneo-pim-community/shared';
import {Information, AssetsIllustration, Link} from 'akeneo-design-system';
import React from 'react';

const NoAsset = ({assetFamilyLabel, onCreateAsset}: {assetFamilyLabel: string; onCreateAsset: () => void}) => {
  const translate = useTranslate();

  return (
    <>
      <Information
        illustration={<AssetsIllustration />}
        title={`👋 ${translate('pim_asset_manager.asset_family.helper.title')}`}
      >
        {translate('pim_asset_manager.asset_family.helper.no_asset.text', {family: assetFamilyLabel})}
      </Information>
      <NoDataSection>
        <AssetsIllustration size={256} />
        <NoDataTitle>{translate('pim_asset_manager.asset_family.no_data.no_asset.title')}</NoDataTitle>
        <NoDataText>
          <Link onClick={onCreateAsset}>{translate('pim_asset_manager.asset_family.no_data.no_asset.link')}</Link>
        </NoDataText>
      </NoDataSection>
    </>
  );
};

export {NoAsset};
