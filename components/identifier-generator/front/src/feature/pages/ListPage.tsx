import React, {useCallback, useMemo} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributesIllustration, Button, Helper, Table} from 'akeneo-design-system';
import {useGetGenerators} from '../hooks/useGetGenerators';
import {LabelCollection} from '../models';
import {Styled} from './styles/ListPageStyled';
import {ListSkeleton} from '../components/ListSkeleton';
import {Header} from '../components/Header';

type ListPageProps = {
  onCreate: () => void;
};

const ListPage: React.FC<ListPageProps> = ({onCreate}) => {
  const translate = useTranslate();
  const {data: generators, isLoading} = useGetGenerators();
  const locale = useUserContext().get('catalogLocale');
  const isGeneratorListEmpty = useMemo(() => generators?.length === 0, [generators]);
  const getCurrentLabel = useCallback(
    (labels: LabelCollection, code: string) => labels[locale] || `[${code}]`,
    [locale]
  );

  return (
    <>
      <Header>
        <Button onClick={onCreate} disabled={!isGeneratorListEmpty}>
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
              <Styled.NoIdentifierMessage>
                <AttributesIllustration />
                <Styled.Title>{translate('pim_identifier_generator.list.first_generator')}</Styled.Title>
                <Styled.HelpCenterLink
                  href="https://help.akeneo.com/pim/serenity/articles/understand-data-quality.html"
                  target="_blank"
                >
                  {translate('pim_identifier_generator.list.check_help_center')}
                </Styled.HelpCenterLink>
              </Styled.NoIdentifierMessage>
            )}
            {isLoading && <ListSkeleton />}
            {!isGeneratorListEmpty && (
              <>
                <tr>
                  <td colSpan={3}>
                    <Helper level="info">
                      {translate('pim_identifier_generator.list.create_info')}
                      <a
                        href="https://help.akeneo.com/pim/serenity/articles/understand-data-quality.html"
                        target="_blank"
                        rel="noreferrer"
                      >
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
                  </Table.Row>
                ))}
              </>
            )}
          </Table.Body>
        </Table>
      </Styled.Container>
    </>
  );
};

export {ListPage};
