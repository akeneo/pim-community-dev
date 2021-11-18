import React, {FC} from 'react';
import {PageContent, PageHeader, PimView, Section, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb, KeyFigureGrid, SectionTitle} from 'akeneo-design-system';
import {useCatalogVolumes} from './hooks/useCatalogVolumes';
import {CatalogVolumeKeyFigure} from './CatalogVolumeKeyFigure';

const CatalogVolumeMonitoringApp: FC = () => {
  const translate = useTranslate();
  const systemHref = useRoute('pim_system_index');
  const [catalogVolumes] = useCatalogVolumes();

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
          {catalogVolumes.map(volume => (
              <Section key={volume.name}>
                <SectionTitle>
                  <SectionTitle.Title>{volume.name}</SectionTitle.Title>
                </SectionTitle>
                <KeyFigureGrid>
                  {volume.keyFigures.map(keyFigure => {
                    return <CatalogVolumeKeyFigure keyFigure={keyFigure} key={keyFigure.name} />;
                  })}
                </KeyFigureGrid>
              </Section>
          ))}
      </PageContent>
    </>
  );
};

export {CatalogVolumeMonitoringApp};
