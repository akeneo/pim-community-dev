import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {NoDataSection, NoDataText, NoDataTitle} from '@akeneo-pim-community/shared';
import {AssetsIllustration, Information, Link} from 'akeneo-design-system';
import React from 'react';

const NoAssetFamily = ({onCreateAssetFamily}: {onCreateAssetFamily: () => void}) => {
  const translate = useTranslate();

  return (
    <>
      <Information
        illustration={<AssetsIllustration />}
        title={`👋 ${translate('pim_asset_manager.asset_family.helper.title')}`}
      >
        <p>{translate('pim_asset_manager.asset_family.helper.no_asset_family.text')}</p>
        <Link href="https://help.akeneo.com/pim/serenity/articles/what-about-assets.html" target="_blank">
          {translate('pim_asset_manager.asset_family.helper.no_asset_family.link')}
        </Link>
      </Information>
      <NoDataSection>
        <AssetsIllustration size={256} />
        <NoDataTitle>{translate('pim_asset_manager.asset_family.no_data.no_asset_family.title')}</NoDataTitle>
        <NoDataText>
          <Link onClick={onCreateAssetFamily}>
            {translate('pim_asset_manager.asset_family.no_data.no_asset_family.link')}
          </Link>
        </NoDataText>
      </NoDataSection>
    </>
  );
};

export {NoAssetFamily};
