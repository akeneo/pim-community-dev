import React from 'react';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  Section,
  useRoute,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {Breadcrumb, KeyFigureGrid, SectionTitle} from 'akeneo-design-system';
import {GetCatalogVolumeInterface, useCatalogVolumeByAxis} from './hooks/useCatalogVolumeByAxis';
import {CatalogVolumeKeyFigure} from './CatalogVolumeKeyFigure';
import styled from 'styled-components';

interface Props {
  getCatalogVolumes: GetCatalogVolumeInterface;
}

const CatalogVolumeMonitoringApp = ({getCatalogVolumes}: Props) => {
  const translate = useTranslate();
  const systemHref = useRoute('pim_system_index');
  const [axes, status] = useCatalogVolumeByAxis(getCatalogVolumes);

  const StyledKeyFigureGrid = styled(KeyFigureGrid)`
    grid-template-columns: repeat(3, 33%);
  `;

  const displayContent = () => {
    if (status === 'error') {
      // TODO: create custom error view
      return <FullScreenError title={translate('error.exception')} message={translate('error.not_found')} code={404} />;
    }

    return axes.map(axis => (
      <Section key={axis.name}>
        <SectionTitle>
          <SectionTitle.Title>{translate(`pim_catalog_volume.axis.title.${axis.name}`)}</SectionTitle.Title>
        </SectionTitle>
        <StyledKeyFigureGrid>
          {axis.catalogVolumes.map(catalogVolume => {
            return <CatalogVolumeKeyFigure catalogVolume={catalogVolume} key={catalogVolume.name} />;
          })}
        </StyledKeyFigureGrid>
      </Section>
    ));
  };

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
      <PageContent>{displayContent()}</PageContent>
    </>
  );
};

export {CatalogVolumeMonitoringApp};
