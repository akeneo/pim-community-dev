import React, {FC} from 'react';
import {PageContent, PageHeader, PimView, Section, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb, KeyFigureGrid, SectionTitle} from 'akeneo-design-system';
import {useCatalogVolumes} from './hooks/useCatalogVolumes';
import {CatalogVolumeKeyFigure} from './CatalogVolumeKeyFigure';
import {IconsMappingContext} from './context/IconsMappingContext';
import {Axe, IconsMapping} from './model/catalog-volume';

type Props = {
  axes: Axe[];
  iconsMapping: IconsMapping;
};

const CatalogVolumeMonitoringApp: FC<Props> = ({axes, iconsMapping}) => {
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
        <IconsMappingContext.Provider value={iconsMapping}>
          {axes.map(axe => (
            <>
              {axe.volumes.filter(volumeName => catalogVolumes.hasOwnProperty(volumeName)).length > 0 && (
                <Section>
                  <SectionTitle>
                    <SectionTitle.Title>{axe.name}</SectionTitle.Title>
                  </SectionTitle>
                  <KeyFigureGrid>
                    {axe.volumes.map(volumeName => {
                      if (!catalogVolumes.hasOwnProperty(volumeName)) {
                        return;
                      }
                      return <CatalogVolumeKeyFigure name={volumeName} volume={catalogVolumes[volumeName]}/>;
                    })}
                  </KeyFigureGrid>
                </Section>
              )}
            </>
          ))}
          </IconsMappingContext.Provider>
      </PageContent>
    </>
  );
};

export {CatalogVolumeMonitoringApp};
