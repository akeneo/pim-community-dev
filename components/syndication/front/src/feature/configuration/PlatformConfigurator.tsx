import {filterErrors, PageHeader, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  Breadcrumb,
  Button,
  ExportXlsxIllustration,
  getColor,
  getFontSize,
  SubNavigationItem,
  Tag,
} from 'akeneo-design-system';
import React, {useCallback, useState} from 'react';
import styled from 'styled-components';
import {CatalogProjectionConfigurator} from './CatalogProjectionConfigurator';
import {ManualEditor} from './components/ManualEditor';
import {SubNavigation} from './components/shared/SubNavigation';
import {ValidationErrorsContext} from './contexts';
import {EntityTypeProvider} from './contexts/EntityTypeContext';
import {PlatformConfiguration, Platform, CatalogProjection, getDefaultCatalogProjection} from './models';
import {JobInstanceDetail} from '@akeneo-pim-community/process-tracker';
import {Connection} from './components/Connection';
import {FamilySelector} from './components/FamilySelector/FamilySelector';
import {useFamily} from './hooks/platform/useFamily';

const Header = styled(PageHeader)`
  padding: 0;
`;

const Illustration = styled(ExportXlsxIllustration)`
  border: 1px solid ${getColor('grey80')};
  margin-right: 20px;
`;

const MetaContainer = styled.div`
  display: flex;
  align-items: center;
  margin-top: 20px;
`;

const SubNavigationTitle = styled.div`
  color: ${getColor('grey', 100)};
  text-transform: uppercase;
  font-size: ${getFontSize('small')};
  white-space: nowrap;
`;

const Container = styled.div`
  display: flex;
  width: 100%;
  height: 100vh;
`;

const Page = styled.div`
  flex: 1;
  display: flex;
  flex-direction: column;
`;

const Content = styled.div`
  padding: 0 40px 40px 40px;
  flex: 1;
`;

type PlatformConfiguratorProps = {
  code: string;
  configuration: PlatformConfiguration;
  platform: Platform;
  entityType: 'product_model' | 'product';
  validationErrors: ValidationError[];
  onSave: () => void;
  onConfigurationChange: (configuration: PlatformConfiguration) => void;
};

const PlatformConfigurator = ({
  code,
  configuration,
  platform,
  validationErrors,
  entityType,
  onSave,
  onConfigurationChange,
}: PlatformConfiguratorProps) => {
  const translate = useTranslate();
  const [currentFamilyCode, setCurrentFamilyCode] = useState<string | null>(
    configuration.catalogProjections.map(({code}) => code)[0] ?? null
  );
  const [currentTab, setCurrentTab] = useState<string | null>(null);
  const handleCatalogProjectionChange = useCallback(
    (catalogProjection: CatalogProjection) => {
      onConfigurationChange({
        ...configuration,
        catalogProjections: [
          ...configuration.catalogProjections.filter(
            currentCatalogProjection => currentCatalogProjection.code !== catalogProjection.code
          ),
          catalogProjection,
        ],
      });
    },
    [configuration, onConfigurationChange]
  );

  const handleFamilyChange = useCallback((familyCode: string) => {
    setCurrentFamilyCode(familyCode);
    setCurrentTab(null);
  }, []);
  const handleTabChange = useCallback((tabCode: string) => {
    setCurrentFamilyCode(null);
    setCurrentTab(tabCode);
  }, []);

  const handleFamilyDelete = useCallback(
    (familyCodeToDelete: string) => {
      const newCatalogProjections = configuration.catalogProjections.filter(({code}) => code !== familyCodeToDelete);

      onConfigurationChange({
        ...configuration,
        catalogProjections: newCatalogProjections,
      });

      if (newCatalogProjections.length === 0) {
        handleTabChange('manual');
      } else {
        handleFamilyChange(newCatalogProjections[0].code);
      }
    },
    [configuration, onConfigurationChange, handleTabChange, handleFamilyChange]
  );
  const handleFamilyAdd = useCallback(
    (familyCodeToAdd: string) => {
      const isNewFamily = !configuration.catalogProjections.some(({code}) => code === familyCodeToAdd);

      if (isNewFamily) {
        onConfigurationChange({
          ...configuration,
          catalogProjections: [...configuration.catalogProjections, getDefaultCatalogProjection(familyCodeToAdd)],
        });
        setCurrentFamilyCode(familyCodeToAdd);
      }
    },
    [configuration, onConfigurationChange]
  );

  const currentFamily = useFamily(platform.code, currentFamilyCode);

  const title =
    null !== currentFamilyCode && null !== currentFamily ? currentFamily.label ?? currentFamily.code : currentTab ?? '';

  return (
    <Container>
      <ValidationErrorsContext.Provider value={validationErrors}>
        <EntityTypeProvider entityType={entityType}>
          <SubNavigation title={platform.code}>
            <div>
              <SubNavigationItem onClick={() => handleTabChange('connection')}>
                {translate('akeneo.syndication.configuration.connection')}
              </SubNavigationItem>
              <SubNavigationItem onClick={() => handleTabChange('manual')}>
                {translate('akeneo.syndication.configuration.manual')}
              </SubNavigationItem>
            </div>
            <SubNavigationTitle>{translate('akeneo.syndication.configuration.families')}</SubNavigationTitle>
            <FamilySelector
              activeFamilyCodes={configuration.catalogProjections.map(({code}) => code)}
              families={platform.families}
              currentFamily={currentFamilyCode}
              onCurrentFamilyChange={handleFamilyChange}
              onFamilyDelete={handleFamilyDelete}
              onFamilyAdd={handleFamilyAdd}
            />
          </SubNavigation>
          <Page>
            <Header>
              <PageHeader.Illustration>
                <Illustration size={138} />
              </PageHeader.Illustration>
              <PageHeader.Breadcrumb>
                <Breadcrumb>
                  <Breadcrumb.Step>Exports</Breadcrumb.Step>
                </Breadcrumb>
              </PageHeader.Breadcrumb>
              <PageHeader.UserActions>{/* <UserNavigation /> */}</PageHeader.UserActions>
              <PageHeader.Actions>
                <Button onClick={onSave}>Save</Button>
              </PageHeader.Actions>
              {/* <PageHeader.State>{form.isDirty && <UnsavedChanges />}</PageHeader.State> */}
              <PageHeader.Title>{title}</PageHeader.Title>
              <PageHeader.Content>
                <MetaContainer>
                  {translate('syndication.completeness')}:&nbsp; <Tag>12/25</Tag>
                </MetaContainer>
              </PageHeader.Content>
            </Header>
            <Content>
              {null !== currentFamily && (
                <ValidationErrorsContext.Provider
                  value={filterErrors(
                    validationErrors,
                    `[catalog_projections][${configuration.catalogProjections.findIndex(
                      ({code}) => code === currentFamilyCode
                    )}]`
                  )}
                >
                  <CatalogProjectionConfigurator
                    key={currentFamily.code}
                    onSave={onSave}
                    requirements={currentFamily.requirements}
                    catalogProjection={
                      configuration.catalogProjections.find(({code}) => code === currentFamily.code) ??
                      getDefaultCatalogProjection(currentFamily.code)
                    }
                    onCatalogProjectionChange={handleCatalogProjectionChange}
                  />
                </ValidationErrorsContext.Provider>
              )}
              {'manual' === currentTab && (
                <ManualEditor configuration={configuration} onConfigurationChange={onConfigurationChange} />
              )}
              {'connection' === currentTab && (
                <Connection configuration={configuration} onConfigurationChange={onConfigurationChange} />
              )}
              {'tracking' === currentTab && <JobInstanceDetail code={code} type="export" />}
            </Content>
          </Page>
        </EntityTypeProvider>
      </ValidationErrorsContext.Provider>
    </Container>
  );
};

export {PlatformConfigurator};
