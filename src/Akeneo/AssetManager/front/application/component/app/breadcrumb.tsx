import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useRoute, useRouter, useTranslate} from '@akeneo-pim-community/shared';

type AssetFamilyBreadcrumbProps = {
  assetFamilyLabel: string;
};

const AssetFamilyBreadcrumb = ({assetFamilyLabel}: AssetFamilyBreadcrumbProps) => {
  const translate = useTranslate();
  const indexHref = `#${useRoute('akeneo_asset_manager_asset_family_index')}`;

  return (
    <Breadcrumb>
      <Breadcrumb.Step href={indexHref}>{translate('pim_asset_manager.asset_family.breadcrumb')}</Breadcrumb.Step>
      <Breadcrumb.Step>{assetFamilyLabel}</Breadcrumb.Step>
    </Breadcrumb>
  );
};

type AssetBreadcrumbProps = {
  assetFamilyIdentifier: string;
  assetCode: string;
};

const AssetBreadcrumb = ({assetFamilyIdentifier, assetCode}: AssetBreadcrumbProps) => {
  const translate = useTranslate();
  const indexHref = `#${useRoute('akeneo_asset_manager_asset_family_index')}`;
  const assetFamilyHref = `#${useRoute('akeneo_asset_manager_asset_family_edit', {
    identifier: assetFamilyIdentifier,
    tab: 'attribute',
  })}`;

  const router = useRouter();

  const handleBreadcrumbClick = (event: React.MouseEvent<HTMLAnchorElement>, href: string) => {
    event.preventDefault();
    router.redirect(href);
  };

  return (
    <Breadcrumb>
      <Breadcrumb.Step
        onClick={(event: React.MouseEvent<HTMLAnchorElement>) => handleBreadcrumbClick(event, indexHref)}
        href={indexHref}
      >
        {translate('pim_asset_manager.asset_family.breadcrumb')}
      </Breadcrumb.Step>
      <Breadcrumb.Step
        onClick={(event: React.MouseEvent<HTMLAnchorElement>) => handleBreadcrumbClick(event, assetFamilyHref)}
        href={assetFamilyHref}
      >
        {assetFamilyIdentifier}
      </Breadcrumb.Step>
      <Breadcrumb.Step>{assetCode}</Breadcrumb.Step>
    </Breadcrumb>
  );
};

export {AssetFamilyBreadcrumb, AssetBreadcrumb};
