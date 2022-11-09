import React, {useCallback, useMemo, useState} from 'react';
import {PageContent, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {
  AttributesIllustration,
  Button,
  Helper,
  Placeholder,
  SkeletonPlaceholder,
  Table,
  useBooleanState,
} from 'akeneo-design-system';
import {useGetGenerators, useIdentifierAttributes} from '../hooks';
import {LabelCollection, Target} from '../models';
import {Styled} from './styles';
import {Header, ListSkeleton} from '../components';
import {useHistory} from 'react-router-dom';
import {DeleteGeneratorModal} from './';

type ListPageProps = {
  onCreate: () => void;
};

const ListPage: React.FC<ListPageProps> = ({onCreate}) => {
  const helpCenterUrl = 'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html';

  const history = useHistory();
  const translate = useTranslate();

  const {data: generators = [], isLoading, refetch} = useGetGenerators();

  const [isDeleteGeneratorModalOpen, openDeleteGeneratorModal, closeDeleteGeneratorModal] = useBooleanState();
  const [generatorToDelete, setGeneratorToDelete] = useState<string>('');

  const locale = useUserContext().get('catalogLocale');
  const isCreateDisabled = useMemo(() => generators?.length >= 1, [generators]);
  const isGeneratorListEmpty = useMemo(() => generators?.length === 0, [generators]);
  const getCurrentLabel = useCallback(
    (labels: LabelCollection, code: string) => labels[locale] || `[${code}]`,
    [locale]
  );
  const goToEditPage = (code: string) => () => history.push(`/${code}`);
  const closeModal = (): void => closeDeleteGeneratorModal();

  const {data: identifierAttributes} = useIdentifierAttributes();

  const refetchAndClose = (): void => {
    refetch();
    closeModal();
  };

  const onDelete = (code: string) => () => {
    setGeneratorToDelete(code);
    openDeleteGeneratorModal();
  };

  const getTargetLabel: (target: Target) => string | undefined = target => {
    return identifierAttributes?.find(attribute => attribute.code === target)?.label;
  };

  return (
    <>
      <Header>
        <Button onClick={onCreate} disabled={isCreateDisabled}>
          {translate('pim_common.create')}
        </Button>
      </Header>
      <PageContent>
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {isGeneratorListEmpty && !isLoading && (
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
            {isLoading && <ListSkeleton />}
            {!isGeneratorListEmpty && (
              <>
                <tr>
                  <td colSpan={3}>
                    <Helper level="info">
                      {translate('pim_identifier_generator.list.create_info')}{' '}
                      <a href={helpCenterUrl} target="_blank" rel="noreferrer">
                        {translate('pim_identifier_generator.list.check_help_center')}
                      </a>
                    </Helper>
                  </td>
                </tr>
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
              </>
            )}
          </Table.Body>
        </Table>
      </PageContent>
      {isDeleteGeneratorModalOpen && generatorToDelete !== null && (
        <DeleteGeneratorModal generatorCode={generatorToDelete} onClose={closeModal} onDelete={refetchAndClose} />
      )}
    </>
  );
};

export {ListPage};
