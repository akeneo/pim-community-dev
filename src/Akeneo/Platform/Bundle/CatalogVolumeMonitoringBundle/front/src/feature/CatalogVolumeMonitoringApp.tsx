import React from 'react';
import {PageContent, PageHeader, PimView, Section, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb, KeyFigureGrid, SectionTitle} from 'akeneo-design-system';
import {GetCatalogVolumeInterface, useCatalogVolumeByAxis} from './hooks/useCatalogVolumeByAxis';
import {CatalogVolumeKeyFigure} from './CatalogVolumeKeyFigure';

interface Props {
  getCatalogVolume: GetCatalogVolumeInterface;
}

const CatalogVolumeMonitoringApp = ({getCatalogVolume}: Props) => {
  const translate = useTranslate();
  const systemHref = useRoute('pim_system_index');
  const [axes] = useCatalogVolumeByAxis(getCatalogVolume);

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${systemHref}`}>{translate('pim_menu.tab.system')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.catalog_volume')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{translate('pim_menu.item.catalog_volume')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        {axes.map(axis => (
          <Section key={axis.name}>
            <SectionTitle>
              <SectionTitle.Title>{axis.name}</SectionTitle.Title>
            </SectionTitle>
            <KeyFigureGrid>
              {axis.catalogVolumes.map(catalogVolume => {
                return <CatalogVolumeKeyFigure catalogVolume={catalogVolume} key={catalogVolume.name} />;
              })}
            </KeyFigureGrid>
          </Section>
        ))}
      </PageContent>
    </>
  );
};

export {CatalogVolumeMonitoringApp};
