import React, {useCallback, useMemo} from 'react';
import {PageContent, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributesIllustration, Button, Helper, Placeholder, Table} from 'akeneo-design-system';
import {useGetGenerators} from '../hooks';
import {LabelCollection} from '../models';
import {Styled} from './styles/ListPageStyled';
import {ListSkeleton} from '../components/ListSkeleton';
import {Header} from '../components/Header';
import {useHistory} from 'react-router-dom';
import {upperCase} from 'lodash';

type ListPageProps = {
  onCreate: () => void;
};

const ListPage: React.FC<ListPageProps> = ({onCreate}) => {
  const history = useHistory();
  const translate = useTranslate();
  const {data: generators = [], isLoading} = useGetGenerators();
  const locale = useUserContext().get('catalogLocale');
  const isCreateDisabled = useMemo(() => generators?.length >= 1, [generators]);
  const isGeneratorListEmpty = useMemo(() => generators?.length === 0, [generators]);
  const getCurrentLabel = useCallback(
    (labels: LabelCollection, code: string) => labels[locale] || `[${code}]`,
    [locale]
  );
  const goToEditPage = (code: string) => () => history.push(`/${code}`);
  const helpCenterUrl = 'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html';

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
                  <Table.Row key={code} onClick={goToEditPage(code)}>
                    <Table.Cell>
                      <Styled.Label>{getCurrentLabel(labels, code)}</Styled.Label>
                    </Table.Cell>
                    <Table.Cell colSpan={2}>{upperCase(target)}</Table.Cell>
                  </Table.Row>
                ))}
              </>
            )}
          </Table.Body>
        </Table>
      </PageContent>
    </>
  );
};

export {ListPage};
