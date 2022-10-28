import React, {useCallback, useState} from 'react';
import {Button, Helper, TabBar, useBooleanState} from 'akeneo-design-system';
import {PageContent, PageHeader, useTranslate, SecondaryActions, useRouter} from '@akeneo-pim-community/shared';
import {GeneralPropertiesTab, Structure} from '../tabs';
import {IdentifierGenerator} from '../models';
import {Violation} from '../validators/Violation';
import {Header} from '../components/Header';
import {DeleteIdentifierGeneratorModal} from './DeleteGeneratorModal';

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
  const router = useRouter();
  const [generator, setGenerator] = useState<IdentifierGenerator>(initialGenerator);
  const changeTab = useCallback(tabName => () => setCurrentTab(tabName), []);
  const onSave = useCallback(() => mainButtonCallback(generator), [generator, mainButtonCallback]);

  const [generatorToDelete, setGeneratorToDelete] = useState<string>('');
  const [isDeleteGeneratorModalOpen, openDeleteGeneratorModal, closeDeleteGeneratorModal] = useBooleanState();

  const closeModal = (): void => {
    closeDeleteGeneratorModal();
  };
  const redirectToList = (): void => {
    closeModal();
    router.redirect('/configuration/identifier-generator');
  };

  return (
    <>
      <Header>
        <PageHeader.Actions>
          <SecondaryActions>
            <SecondaryActions.Item
              onClick={() => {
                setGeneratorToDelete(generator.code);
                openDeleteGeneratorModal();
              }}
            >
              {translate('pim_common.delete')}
            </SecondaryActions.Item>
          </SecondaryActions>
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
        {currentTab === Tabs.STRUCTURE && <Structure />}
      </PageContent>
      {isDeleteGeneratorModalOpen && generatorToDelete !== null && (
        <DeleteIdentifierGeneratorModal
          generatorCode={generatorToDelete}
          closeModal={closeModal}
          deleteGenerator={redirectToList}
        />
      )}
    </>
  );
};

export {CreateOrEditGeneratorPage};
