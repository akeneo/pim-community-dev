import React, {useCallback, useMemo, useState} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributesIllustration, Button, Helper, Placeholder, Table, useBooleanState} from 'akeneo-design-system';
import {useGetGenerators} from '../hooks';
import {LabelCollection} from '../models';
import {Styled} from './styles/ListPageStyled';
import {ListSkeleton} from '../components/ListSkeleton';
import {Header} from '../components/Header';
import {DeleteIdentifierGeneratorModal} from './DeleteGeneratorModal';

type ListPageProps = {
  onCreate: () => void;
};

const ListPage: React.FC<ListPageProps> = ({onCreate}) => {
  const translate = useTranslate();
  const {data: generators, isLoading, refetch} = useGetGenerators();
  const locale = useUserContext().get('catalogLocale');
  const isCreateDisabled = useMemo(() => generators?.length >= 1, [generators]);
  const isGeneratorListEmpty = useMemo(() => generators?.length === 0, [generators]);
  const getCurrentLabel = useCallback(
    (labels: LabelCollection, code: string) => labels[locale] || `[${code}]`,
    [locale]
  );
  // TODO: CPM-795 : Add real url
  const helpCenterUrl = '#';

  // data state
  // String
  const [generatorToDelete, setGeneratorToDelete] = useState<string>('');

  // ui state
  const [isDeleteGeneratorModalOpen, openDeleteGeneratorModal, closeDeleteGeneratorModal] = useBooleanState();

  const closeModal = (): void => {
    closeDeleteGeneratorModal();
  };

  const refetchAndClose = (): void => {
    refetch();
    closeModal();
  };

  const onDelete = (code: string) => () => {
    setGeneratorToDelete(code);
    openDeleteGeneratorModal();
  };

  return (
    <>
      <Header>
        <Button onClick={onCreate} disabled={isCreateDisabled}>
          {translate('pim_common.create')}
        </Button>
      </Header>
      <Styled.Container>
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
            <Table.HeaderCell></Table.HeaderCell>
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
                      {translate('pim_identifier_generator.list.create_info')}
                      <a href={helpCenterUrl} target="_blank" rel="noreferrer">
                        {translate('pim_identifier_generator.list.check_help_center')}
                      </a>
                    </Helper>
                  </td>
                </tr>
                {generators?.map(({labels, code, target}) => (
                  <Table.Row key={code}>
                    <Table.Cell>
                      <Styled.Label>{getCurrentLabel(labels, code)}</Styled.Label>
                    </Table.Cell>
                    <Table.Cell>{target}</Table.Cell>
                    <Table.ActionCell>
                      <Button
                          onClick={onDelete(code)}
                          ghost
                          level="danger"
                      >
                        {translate('pim_common.delete')}
                      </Button>
                    </Table.ActionCell>
                  </Table.Row>
                ))}
              </>
            )}
          </Table.Body>
        </Table>
      </Styled.Container>
      {isDeleteGeneratorModalOpen && generatorToDelete !== null && (
        <DeleteIdentifierGeneratorModal
            generatorCode={generatorToDelete}
            closeModal={closeModal}
            deleteGenerator={refetchAndClose}
        />
      )}
    </>
  );
};

export {ListPage};
