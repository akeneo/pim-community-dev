import React, {useCallback, useEffect, useState} from 'react';
import {Button, Helper, TabBar, useBooleanState} from 'akeneo-design-system';
import {PageHeader, SecondaryActions, useTranslate} from '@akeneo-pim-community/shared';
import {GeneralPropertiesTab, SelectionTab, StructureTab} from '../tabs';
import {Conditions, Delimiter, IdentifierGenerator, IdentifierGeneratorCode, Structure} from '../models';
import {validateIdentifierGenerator, Violation} from '../validators/';
import {Header} from '../components';
import {DeleteGeneratorModal} from './DeleteGeneratorModal';
import {useHistory} from 'react-router-dom';
import {useIdentifierGeneratorAclContext, useIdentifierGeneratorContext} from '../context';
import styled from 'styled-components';
import {GeneratorTab} from '../models';
import {useStructureTabs} from '../hooks';

// TODO: replace this component by PageContent when there we delete the warning message (DO NOT USE...)
const Container = styled.div`
  padding: 0 40px;
  overflow: auto;
  height: calc(100vh - 190px);
`;

type CreateOrEditGeneratorProps = {
  isMainButtonDisabled: boolean;
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
  const {currentTab, setCurrentTab} = useStructureTabs();
  const translate = useTranslate();
  const history = useHistory();
  const [generator, setGenerator] = useState<IdentifierGenerator>(initialGenerator);
  const changeTab = useCallback(tabName => () => setCurrentTab(tabName), [setCurrentTab]);
  const onSave = useCallback(() => mainButtonCallback(generator), [generator, mainButtonCallback]);
  const identifierGeneratorContext = useIdentifierGeneratorContext();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  const [generatorCodeToDelete, setGeneratorCodeToDelete] = useState<IdentifierGeneratorCode | undefined>();
  const [isDeleteGeneratorModalOpen, openDeleteGeneratorModal, closeDeleteGeneratorModal] = useBooleanState();

  useEffect(() => {
    setGenerator(initialGenerator);
  }, [initialGenerator]);

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

  const onChangeGenerator = useCallback(
    (generator: IdentifierGenerator) => {
      if (JSON.stringify(generator) !== JSON.stringify(initialGenerator) || isNew) {
        identifierGeneratorContext.unsavedChanges.setHasUnsavedChanges(true);
      } else {
        identifierGeneratorContext.unsavedChanges.setHasUnsavedChanges(false);
      }
      setGenerator(generator);
    },
    [identifierGeneratorContext.unsavedChanges, initialGenerator, isNew]
  );

  const onStructureChange = (structure: Structure) => {
    const updatedGenerator = {...generator, structure};
    onChangeGenerator(updatedGenerator);
  };

  const onConditionsChange = (conditions: Conditions) => {
    const updatedGenerator = {...generator, conditions};
    onChangeGenerator(updatedGenerator);
  };

  const onDelimiterChange = (delimiter: Delimiter | null) => {
    const updatedGenerator = {...generator, delimiter: delimiter};
    onChangeGenerator(updatedGenerator);
  };

  const isGeneratorValid = validateIdentifierGenerator(generator, '').length === 0;

  return (
    <>
      <Header>
        {identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted && (
          <PageHeader.Actions>
            {!isNew && (
              <SecondaryActions>
                <SecondaryActions.Item onClick={handleDeleteModal}>
                  {translate('pim_common.delete')}
                </SecondaryActions.Item>
              </SecondaryActions>
            )}
            <Button disabled={isMainButtonDisabled || !isGeneratorValid} onClick={onSave}>
              {translate('pim_common.save')}
            </Button>
          </PageHeader.Actions>
        )}
      </Header>
      <Container>
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
          <TabBar.Tab isActive={currentTab === GeneratorTab.GENERAL} onClick={changeTab(GeneratorTab.GENERAL)}>
            {translate('pim_identifier_generator.tabs.general')}
          </TabBar.Tab>
          <TabBar.Tab
            isActive={currentTab === GeneratorTab.PRODUCT_SELECTION}
            onClick={changeTab(GeneratorTab.PRODUCT_SELECTION)}
          >
            {translate('pim_identifier_generator.tabs.product_selection')}
          </TabBar.Tab>
          <TabBar.Tab isActive={currentTab === GeneratorTab.STRUCTURE} onClick={changeTab(GeneratorTab.STRUCTURE)}>
            {translate('pim_identifier_generator.tabs.identifier_structure')}
          </TabBar.Tab>
        </TabBar>
        {currentTab === GeneratorTab.GENERAL && (
          <GeneralPropertiesTab generator={generator} onGeneratorChange={onChangeGenerator} />
        )}
        {currentTab === GeneratorTab.PRODUCT_SELECTION && (
          <SelectionTab target={generator.target} conditions={generator.conditions} onChange={onConditionsChange} />
        )}
        {currentTab === GeneratorTab.STRUCTURE && (
          <StructureTab
            initialStructure={generator.structure}
            delimiter={generator.delimiter}
            onStructureChange={onStructureChange}
            onDelimiterChange={onDelimiterChange}
          />
        )}
      </Container>
      {isDeleteGeneratorModalOpen && generatorCodeToDelete && (
        <DeleteGeneratorModal generatorCode={generatorCodeToDelete} onClose={closeModal} onDelete={redirectToList} />
      )}
    </>
  );
};

export {CreateOrEditGeneratorPage};
