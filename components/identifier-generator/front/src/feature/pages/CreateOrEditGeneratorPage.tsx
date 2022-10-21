import React, {useState} from 'react';
import {Breadcrumb, Button, Helper, TabBar} from 'akeneo-design-system';
import {PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {GeneralPropertiesTab} from '../tabs';
import {IdentifierGenerator} from '../models';
import {Common, Styled} from '../components';
import {Violation} from '../validators/Violation';

enum Tabs {
  GENERAL,
  PRODUCT_SELECTION,
  STRUCTURE,
}

type CreateOrEditGeneratorProps = {
  initialGenerator: IdentifierGenerator;
  mainButtonCallback: (identifierGenerator: IdentifierGenerator) => void;
  validationErrors: Violation[];
};

const CreateOrEditGeneratorPage: React.FC<CreateOrEditGeneratorProps> = ({
  initialGenerator,
  mainButtonCallback,
  validationErrors,
}) => {
  const [currentTab, setCurrentTab] = useState(Tabs.GENERAL);
  const translate = useTranslate();
  const [generator, setGenerator] = useState<IdentifierGenerator>(initialGenerator);

  return (
    <>
      <Common.Helper />
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href="#">{translate('pim_title.pim_settings_index')}</Breadcrumb.Step>
            {/*TODO Add alert when going out this page if not saved*/}
            <Breadcrumb.Step href="#">{translate('pim_title.akeneo_identifier_generator_index')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-identifier-generator-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <Button onClick={() => mainButtonCallback(generator)}>{translate('pim_common.save')}</Button>
        </PageHeader.Actions>
        <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
      </PageHeader>
      <Styled.TabContainer>
        {validationErrors.length > 0 && (
          <Helper level="error">
            {validationErrors.map(({path, message}) => (
              <div key={`${path || ''}${message}`}>{path && `${path}: `}{message}</div>
            ))}
          </Helper>
        )}

        <TabBar moreButtonTitle={translate('pim_common.more')}>
          <TabBar.Tab isActive={currentTab === Tabs.GENERAL} onClick={() => setCurrentTab(Tabs.GENERAL)}>
            {translate('pim_identifier_generator.tabs.general')}
          </TabBar.Tab>
          <TabBar.Tab
            isActive={currentTab === Tabs.PRODUCT_SELECTION}
            onClick={() => setCurrentTab(Tabs.PRODUCT_SELECTION)}
          >
            {translate('pim_identifier_generator.tabs.product_selection')}
          </TabBar.Tab>
          <TabBar.Tab isActive={currentTab === Tabs.STRUCTURE} onClick={() => setCurrentTab(Tabs.STRUCTURE)}>
            {translate('pim_identifier_generator.tabs.identifier_structure')}
          </TabBar.Tab>
        </TabBar>
        {currentTab === Tabs.GENERAL && <GeneralPropertiesTab generator={generator} onGeneratorChange={setGenerator} />}
        {currentTab === Tabs.PRODUCT_SELECTION && (
          <>
            <div>Not implemented YET</div>
            <div>{JSON.stringify(generator.conditions)}</div>
          </>
        )}
        {currentTab === Tabs.STRUCTURE && (
          <>
            <div>Not implemented YET</div>
            <div>{JSON.stringify(generator.structure)}</div>
          </>
        )}
      </Styled.TabContainer>
    </>
  );
};

export {CreateOrEditGeneratorPage};
