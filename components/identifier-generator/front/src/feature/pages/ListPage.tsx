import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {PageContent, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {
  AttributesIllustration,
  Button,
  CodingIllustration,
  Helper,
  Information,
  Link,
  Placeholder,
  SkeletonPlaceholder,
  Table,
  useBooleanState,
} from 'akeneo-design-system';
import {useGetIdentifierGenerators, useIdentifierAttributes} from '../hooks';
import {LabelCollection, Target} from '../models';
import {Styled} from './styles';
import {FamiliesSelector, Header, ListSkeleton} from '../components';
import {useHistory} from 'react-router-dom';
import {DeleteGeneratorModal} from './';
import {GeneratorTab} from '../models/generatorTab';
import {useStructureTabs} from '../hooks/useStructureTabs';

type ListPageProps = {
  onCreate: () => void;
};

const ListPage: React.FC<ListPageProps> = ({onCreate}) => {
  const helpCenterUrl = 'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html';

  const history = useHistory();
  const translate = useTranslate();
  const {setCurrentTab} = useStructureTabs();

  const [isDeleteGeneratorModalOpen, openDeleteGeneratorModal, closeDeleteGeneratorModal] = useBooleanState();
  const [generatorToDelete, setGeneratorToDelete] = useState<string>('');

  const locale = useUserContext().get('catalogLocale');
  const {data: generators = [], isLoading, error: errorOnGenerators} = useGetIdentifierGenerators();
  const isCreateDisabled = useMemo(() => generators.length >= 1, [generators]);
  const isGeneratorListEmpty = useMemo(() => generators.length === 0, [generators]);

  useEffect(() => {
    setCurrentTab(GeneratorTab.GENERAL);
  }, [setCurrentTab]);

  const getCurrentLabel = useCallback(
    (labels: LabelCollection, code: string) => labels[locale] || `[${code}]`,
    [locale]
  );
  const goToEditPage = (code: string) => () => history.push(`/${code}`);
  const closeModal = (): void => closeDeleteGeneratorModal();

  const {data: identifierAttributes = [], error: errorOnIdentifierAttributes} = useIdentifierAttributes();

  const handleDelete = (): void => {
    closeModal();
  };

  const onDelete = (code: string) => () => {
    setGeneratorToDelete(code);
    openDeleteGeneratorModal();
  };

  const getTargetLabel: (target: Target) => string | undefined = target => {
    return identifierAttributes.find(attribute => attribute.code === target)?.label;
  };

  const [familyCodes, setFamilyCodes] = React.useState<string[]>(['family1043', 'accessories', 'non_existing', 'family1143']);

  return (
    <>
      <FamiliesSelector familyCodes={familyCodes} onChange={setFamilyCodes}/>
      <Header>
        <Button onClick={onCreate} disabled={isCreateDisabled}>
          {translate('pim_common.create')}
        </Button>
      </Header>
      <PageContent>
        <Information illustration={<AttributesIllustration />} title={'Welcome to your identifier generator!'}>
          {translate('pim_identifier_generator.list.helper')}
          <br />
          <Link href="https://www.akeneo.com/" target="_blank">
            {translate('pim_identifier_generator.list.check_help_center')}
          </Link>
        </Information>
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {isGeneratorListEmpty && !isLoading && !errorOnGenerators && !errorOnIdentifierAttributes && (
              <tr>
                <td colSpan={3}>
                  <Placeholder
                    illustration={<AttributesIllustration />}
                    size="large"
                    title={translate('pim_identifier_generator.list.first_generator')}
                  >
                    <Styled.HelpCenterLink href={helpCenterUrl} target="_blank">
                      {translate('pim_identifier_generator.list.check_help_center')}
                    </Styled.HelpCenterLink>
                  </Placeholder>
                </td>
              </tr>
            )}
            {(null !== errorOnGenerators || null !== errorOnIdentifierAttributes) && (
              <tr>
                <td colSpan={3}>
                  <Helper level="error">{translate('pim_error.general')}</Helper>
                </td>
              </tr>
            )}
            {isLoading && <ListSkeleton />}
            {!isGeneratorListEmpty && (
              <>
                {generators?.map(({labels, code, target}) => (
                  <Table.Row key={code} onClick={goToEditPage(code)}>
                    <Table.Cell>
                      <Styled.Label>{getCurrentLabel(labels, code)}</Styled.Label>
                    </Table.Cell>
                    <Table.Cell>
                      {typeof getTargetLabel(target) === 'undefined' && (
                        <SkeletonPlaceholder>Loading identifier</SkeletonPlaceholder>
                      )}
                      {getTargetLabel(target)}
                    </Table.Cell>
                    <Table.ActionCell>
                      <Button onClick={goToEditPage(code)} ghost>
                        {translate('pim_common.edit')}
                      </Button>
                      <Button onClick={onDelete(code)} ghost level="danger">
                        {translate('pim_common.delete')}
                      </Button>
                    </Table.ActionCell>
                  </Table.Row>
                ))}
                <tr>
                  <td colSpan={3}>
                    <Placeholder
                      illustration={<CodingIllustration />}
                      size="large"
                      title={translate('pim_identifier_generator.list.max_generator.title')}
                    >
                      {translate('pim_identifier_generator.list.max_generator.info')}
                      <Styled.HelpCenterLink href={helpCenterUrl} target="_blank">
                        {translate('pim_identifier_generator.list.check_help_center')}
                      </Styled.HelpCenterLink>
                    </Placeholder>
                  </td>
                </tr>
              </>
            )}
          </Table.Body>
        </Table>
      </PageContent>
      {isDeleteGeneratorModalOpen && generatorToDelete !== null && (
        <DeleteGeneratorModal generatorCode={generatorToDelete} onClose={closeModal} onDelete={handleDelete} />
      )}
    </>
  );
};

export {ListPage};
