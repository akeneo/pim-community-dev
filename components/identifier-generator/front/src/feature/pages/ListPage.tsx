import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {PageContent, useRouter, useSecurity, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {
  AttributesIllustration,
  Button,
  Helper,
  Information,
  Link,
  Placeholder,
  Search,
  SkeletonPlaceholder,
  Table,
  useBooleanState,
} from 'akeneo-design-system';
import {useGetIdentifierGenerators, useIdentifierAttributes, useStructureTabs} from '../hooks';
import {FlattenAttribute, GeneratorTab, IdentifierGenerator, LabelCollection, Target} from '../models';
import {Styled} from './styles';
import {Header, ListSkeleton} from '../components';
import {useHistory} from 'react-router-dom';
import {DeleteGeneratorModal} from './';
import {useQueryClient} from 'react-query';

type ListTableProps = {
  isErrored: boolean;
  isLoading: boolean;
  isManageIdentifierGeneratorAclGranted: boolean;
  generators: IdentifierGenerator[];
  onDelete: (code: string) => () => void;
  identifierAttributes: FlattenAttribute[];
  filtered: boolean;
};
const ListTable: React.FC<ListTableProps> = ({
  isErrored,
  isLoading,
  isManageIdentifierGeneratorAclGranted,
  generators,
  onDelete,
  identifierAttributes,
  filtered,
}) => {
  const translate = useTranslate();
  const router = useRouter();
  const history = useHistory();
  const queryClient = useQueryClient();
  const locale = useUserContext().get('catalogLocale');
  const isGeneratorListEmpty = useMemo(() => generators.length === 0, [generators]);
  const helpCenterUrl = 'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html';
  const emptyListMessage = isManageIdentifierGeneratorAclGranted
    ? 'pim_identifier_generator.list.first_generator'
    : 'pim_identifier_generator.list.read_only_list';

  const handleReorder = (indices: number[]) => {
    const codes = indices.map(i => generators[i]?.code).filter(code => code);
    fetch(router.generate('akeneo_identifier_generator_reorder'), {
      method: 'PATCH',
      headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
      body: JSON.stringify({codes}),
    }).then(() => queryClient.invalidateQueries('getGeneratorList'));
  };

  const goToEditPage = (code: string) => () => history.push(`/${code}`);
  const getCurrentLabel = useCallback(
    (labels: LabelCollection, code: string) => labels[locale] || `[${code}]`,
    [locale]
  );
  const getTargetLabel: (target: Target) => string | undefined = target => {
    return identifierAttributes.find(attribute => attribute.code === target)?.label;
  };

  if (isErrored) {
    return (
      <Table>
        <Table.Header>
          <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
          <Table.HeaderCell />
        </Table.Header>
        <Table.Body>
          <tr>
            <td colSpan={3}>
              <Helper level="error">{translate('pim_error.general')}</Helper>
            </td>
          </tr>
        </Table.Body>
      </Table>
    );
  }

  if (isLoading) {
    return (
      <Table>
        <Table.Header>
          <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
          <Table.HeaderCell />
        </Table.Header>
        <Table.Body>
          <ListSkeleton />
        </Table.Body>
      </Table>
    );
  }

  if (isGeneratorListEmpty) {
    return (
      <Table>
        <Table.Header>
          <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
          <Table.HeaderCell />
        </Table.Header>
        <Table.Body>
          <tr>
            <td colSpan={3}>
              <Placeholder illustration={<AttributesIllustration />} size="large" title={translate(emptyListMessage)}>
                <Styled.HelpCenterLink href={helpCenterUrl} target="_blank">
                  {translate('pim_identifier_generator.list.check_help_center')}
                </Styled.HelpCenterLink>
              </Placeholder>
            </td>
          </tr>
        </Table.Body>
      </Table>
    );
  }

  return (
    <Table isDragAndDroppable={isManageIdentifierGeneratorAclGranted && !filtered} onReorder={handleReorder}>
      <Table.Header>
        <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
        <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
        <Table.HeaderCell />
      </Table.Header>
      <Table.Body>
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
                {translate(isManageIdentifierGeneratorAclGranted ? 'pim_common.edit' : 'pim_common.view')}
              </Button>
              {isManageIdentifierGeneratorAclGranted && (
                <Button onClick={onDelete(code)} ghost level="danger">
                  {translate('pim_common.delete')}
                </Button>
              )}
            </Table.ActionCell>
          </Table.Row>
        ))}
        {!isManageIdentifierGeneratorAclGranted && (
          <tr>
            <td colSpan={3}>
              <Placeholder
                illustration={<AttributesIllustration />}
                size="large"
                title={translate('pim_identifier_generator.list.read_only_list')}
              >
                <Styled.HelpCenterLink href={helpCenterUrl} target="_blank">
                  {translate('pim_identifier_generator.list.check_help_center')}
                </Styled.HelpCenterLink>
              </Placeholder>
            </td>
          </tr>
        )}
      </Table.Body>
    </Table>
  );
};

type ListPageProps = {
  onCreate: () => void;
};

const ListPage: React.FC<ListPageProps> = ({onCreate}) => {
  const translate = useTranslate();
  const security = useSecurity();
  const {setCurrentTab} = useStructureTabs();
  const LIMIT_IDENTIFIER_GENERATOR = 20;

  const [isDeleteGeneratorModalOpen, openDeleteGeneratorModal, closeDeleteGeneratorModal] = useBooleanState();
  const [generatorToDelete, setGeneratorToDelete] = useState<string>('');
  const [search, setSearch] = useState<string>('');

  const locale = useUserContext().get('catalogLocale');
  const isManageIdentifierGeneratorAclGranted = security.isGranted('pim_identifier_generator_manage');
  const {data: generators = [], isLoading, error: errorOnGenerators} = useGetIdentifierGenerators();
  const isCreateDisabled = useMemo(
    () => !isManageIdentifierGeneratorAclGranted || generators.length >= LIMIT_IDENTIFIER_GENERATOR,
    [generators, isManageIdentifierGeneratorAclGranted]
  );

  useEffect(() => {
    setCurrentTab(GeneratorTab.GENERAL);
  }, [setCurrentTab]);

  const closeModal = (): void => closeDeleteGeneratorModal();

  const {data: identifierAttributes = [], error: errorOnIdentifierAttributes} = useIdentifierAttributes();

  const handleDelete = (): void => {
    closeModal();
  };

  const onDelete = (code: string) => () => {
    setGeneratorToDelete(code);
    openDeleteGeneratorModal();
  };

  const filteredGenerators = useMemo(
    () =>
      generators.filter(
        ({code, labels}) =>
          code.toLowerCase().includes(search.toLowerCase()) ||
          labels[locale]?.toLowerCase()?.includes(search.toLowerCase())
      ),
    [generators, locale, search]
  );

  const isFiltered = useMemo(() => generators.length !== filteredGenerators.length, [generators, filteredGenerators]);

  return (
    <>
      <Header>
        <Button onClick={onCreate} disabled={isCreateDisabled}>
          {translate('pim_common.create')}
        </Button>
      </Header>
      <PageContent>
        <Information
          illustration={<AttributesIllustration />}
          title={translate('pim_identifier_generator.list.helper.title')}
        >
          {translate('pim_identifier_generator.list.helper.info')}
          <br />
          <Link href="https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html" target="_blank">
            {translate('pim_identifier_generator.list.check_help_center')}
          </Link>
        </Information>

        <Search onSearchChange={setSearch} searchValue={search} placeholder={translate('pim_common.search')}>
          <Search.ResultCount>
            {translate('pim_common.result_count', {itemsCount: filteredGenerators.length}, filteredGenerators.length)}
          </Search.ResultCount>
        </Search>

        {isManageIdentifierGeneratorAclGranted && generators.length >= LIMIT_IDENTIFIER_GENERATOR && (
          <Helper level="info">
            {translate('pim_identifier_generator.list.max_generator.title', {
              count: LIMIT_IDENTIFIER_GENERATOR,
            })}
          </Helper>
        )}

        <ListTable
          isErrored={null !== errorOnGenerators || null !== errorOnIdentifierAttributes}
          isLoading={isLoading}
          isManageIdentifierGeneratorAclGranted={isManageIdentifierGeneratorAclGranted}
          generators={filteredGenerators}
          identifierAttributes={identifierAttributes}
          onDelete={onDelete}
          filtered={isFiltered}
        />
      </PageContent>
      {isDeleteGeneratorModalOpen && generatorToDelete !== null && (
        <DeleteGeneratorModal generatorCode={generatorToDelete} onClose={closeModal} onDelete={handleDelete} />
      )}
    </>
  );
};

export {ListPage};
