import React, {useCallback, useState} from 'react';
import {Button, Helper, TabBar, useBooleanState} from 'akeneo-design-system';
import {PageContent, PageHeader, SecondaryActions, useTranslate} from '@akeneo-pim-community/shared';
import {GeneralPropertiesTab, Structure} from '../tabs';
import {IdentifierGenerator, IdentifierGeneratorCode} from '../models';
import {Violation} from '../validators/Violation';
import {Header} from '../components';
import {DeleteGeneratorModal} from './DeleteGeneratorModal';
import {useHistory} from 'react-router-dom';

enum Tabs {
  GENERAL,
  PRODUCT_SELECTION,
  STRUCTURE,
}

type CreateOrEditGeneratorProps = {
  isMainButtonDisabled: boolean
  initialGenerator: IdentifierGenerator;
  mainButtonCallback: (identifierGenerator: IdentifierGenerator) => void;
  validationErrors: Violation[];
  isNew: boolean;
};

const CreateOrEditGeneratorPage: React.FC<CreateOrEditGeneratorProps> = ({
  initialGenerator,
  isMainButtonDisabled,
  mainButtonCallback,
  validationErrors,
  isNew,
}) => {
  const [currentTab, setCurrentTab] = useState(Tabs.GENERAL);
  const translate = useTranslate();
  const history = useHistory();
  const [generator, setGenerator] = useState<IdentifierGenerator>(initialGenerator);
  const changeTab = useCallback(tabName => () => setCurrentTab(tabName), []);
  const onSave = useCallback(() => mainButtonCallback(generator), [generator, mainButtonCallback]);

  const [generatorCodeToDelete, setGeneratorCodeToDelete] = useState<IdentifierGeneratorCode | undefined>();
  const [isDeleteGeneratorModalOpen, openDeleteGeneratorModal, closeDeleteGeneratorModal] = useBooleanState();

  const closeModal = (): void => {
    closeDeleteGeneratorModal();
  };
  const redirectToList = (): void => {
    closeModal();
    history.push('/');
  };
  const handleDeleteModal = (): void => {
    setGeneratorCodeToDelete(generator.code);
    openDeleteGeneratorModal();
  };

  return (
    <>
      <Header>
        <PageHeader.Actions>
          {!isNew && (
            <SecondaryActions>
              <SecondaryActions.Item onClick={handleDeleteModal}>
                {translate('pim_common.delete')}
              </SecondaryActions.Item>
            </SecondaryActions>
          )}
          <Button disabled={isMainButtonDisabled} onClick={onSave}>{translate('pim_common.save')}</Button>
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
      {isDeleteGeneratorModalOpen && generatorCodeToDelete && (
        <DeleteGeneratorModal generatorCode={generatorCodeToDelete} onClose={closeModal} onDelete={redirectToList} />
      )}
    </>
  );
};

export {CreateOrEditGeneratorPage};
