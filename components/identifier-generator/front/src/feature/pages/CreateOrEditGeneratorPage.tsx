import React, {useCallback, useEffect, useState} from 'react';
import {Button, Pill, TabBar, useBooleanState} from 'akeneo-design-system';
import {PageContent, PageHeader, SecondaryActions, useTranslate} from '@akeneo-pim-community/shared';
import {GeneralPropertiesTab, SelectionTab, StructureTab} from '../tabs';
import {Conditions, Delimiter, GeneratorTab, IdentifierGenerator, IdentifierGeneratorCode, Structure} from '../models';
import {Violation} from '../validators';
import {Header} from '../components';
import {DeleteGeneratorModal} from './DeleteGeneratorModal';
import {useHistory} from 'react-router-dom';
import {useIdentifierGeneratorAclContext, useIdentifierGeneratorContext} from '../context';
import {useStructureTabs} from '../hooks';

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
  const getErrorByPathNames = useCallback(
    (pathNames: string[]) => validationErrors.filter(item => pathNames.some(v => item?.path?.includes(v))),
    [validationErrors]
  );

  const generalValidationErrors = getErrorByPathNames(['code', 'labels', 'target']);
  const selectionValidationErrors = getErrorByPathNames(['conditions']);
  const structureValidationErrors = getErrorByPathNames(['structure', 'delimiter']);

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
    if (structure.length === 0) {
      onChangeGenerator({...updatedGenerator, delimiter: null});
    } else {
      onChangeGenerator(updatedGenerator);
    }
  };

  const onConditionsChange = (conditions: Conditions) => {
    const updatedGenerator = {...generator, conditions};
    onChangeGenerator(updatedGenerator);
  };

  const onDelimiterChange = (delimiter: Delimiter | null) => {
    const updatedGenerator = {...generator, delimiter: delimiter};
    onChangeGenerator(updatedGenerator);
  };

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
            <Button onClick={onSave} disabled={isMainButtonDisabled}>
              {translate('pim_common.save')}
            </Button>
          </PageHeader.Actions>
        )}
      </Header>
      <PageContent>
        <TabBar moreButtonTitle={translate('pim_common.more')}>
          <TabBar.Tab isActive={currentTab === GeneratorTab.GENERAL} onClick={changeTab(GeneratorTab.GENERAL)}>
            {translate('pim_identifier_generator.tabs.general')}
            {generalValidationErrors.length > 0 && <Pill level="danger" />}
          </TabBar.Tab>
          <TabBar.Tab
            isActive={currentTab === GeneratorTab.PRODUCT_SELECTION}
            onClick={changeTab(GeneratorTab.PRODUCT_SELECTION)}
          >
            {translate('pim_identifier_generator.tabs.product_selection')}
            {selectionValidationErrors.length > 0 && <Pill level="danger" />}
          </TabBar.Tab>
          <TabBar.Tab isActive={currentTab === GeneratorTab.STRUCTURE} onClick={changeTab(GeneratorTab.STRUCTURE)}>
            {translate('pim_identifier_generator.tabs.identifier_structure')}
            {structureValidationErrors.length > 0 && <Pill level="danger" />}
          </TabBar.Tab>
        </TabBar>
        {currentTab === GeneratorTab.GENERAL && (
          <GeneralPropertiesTab
            generator={generator}
            onGeneratorChange={onChangeGenerator}
            validationErrors={generalValidationErrors}
          />
        )}
        {currentTab === GeneratorTab.PRODUCT_SELECTION && (
          <SelectionTab
            generator={generator}
            onChange={onConditionsChange}
            validationErrors={selectionValidationErrors}
          />
        )}
        {currentTab === GeneratorTab.STRUCTURE && (
          <StructureTab
            initialStructure={generator.structure}
            delimiter={generator.delimiter}
            onStructureChange={onStructureChange}
            onDelimiterChange={onDelimiterChange}
            validationErrors={structureValidationErrors}
            textTransformation={generator.text_transformation}
          />
        )}
      </PageContent>
      {isDeleteGeneratorModalOpen && generatorCodeToDelete && (
        <DeleteGeneratorModal generatorCode={generatorCodeToDelete} onClose={closeModal} onDelete={redirectToList} />
      )}
    </>
  );
};

export {CreateOrEditGeneratorPage};
