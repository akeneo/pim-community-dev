import React, {useCallback, useState} from 'react';
import {Button, Helper, TabBar} from 'akeneo-design-system';
import {PageContent, PageHeader, useTranslate} from '@akeneo-pim-community/shared';
import {GeneralPropertiesTab, StructureTab} from '../tabs';
import {IdentifierGenerator} from '../models';
import {Violation} from '../validators/Violation';
import {Header} from '../components/Header';

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
  const changeTab = useCallback(tabName => () => setCurrentTab(tabName), []);
  const onSave = useCallback(() => mainButtonCallback(generator), [generator, mainButtonCallback]);

  return (
    <>
      <Header>
        <PageHeader.Actions>
          <Button onClick={onSave}>{translate('pim_common.save')}</Button>
        </PageHeader.Actions>
      </Header>
      <PageContent>
        {validationErrors.length > 0 && (
          <Helper level="error">
            {validationErrors.map(({path, message}) => (
              <div key={`${path || ''}${message}`}>
                {path && `${path}: `}
                {message}
              </div>
            ))}
          </Helper>
        )}

        <TabBar moreButtonTitle={translate('pim_common.more')}>
          <TabBar.Tab isActive={currentTab === Tabs.GENERAL} onClick={changeTab(Tabs.GENERAL)}>
            {translate('pim_identifier_generator.tabs.general')}
          </TabBar.Tab>
          <TabBar.Tab isActive={currentTab === Tabs.PRODUCT_SELECTION} onClick={changeTab(Tabs.PRODUCT_SELECTION)}>
            {translate('pim_identifier_generator.tabs.product_selection')}
          </TabBar.Tab>
          <TabBar.Tab isActive={currentTab === Tabs.STRUCTURE} onClick={changeTab(Tabs.STRUCTURE)}>
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
        {currentTab === Tabs.STRUCTURE && <StructureTab />}
      </PageContent>
    </>
  );
};

export {CreateOrEditGeneratorPage};
