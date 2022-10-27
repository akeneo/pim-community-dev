import React, {useCallback, useMemo} from 'react';
import {Common} from '../components';
import {PageHeader, PimView, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributesIllustration, Breadcrumb, Button, Helper, Table, } from 'akeneo-design-system';
import {useGetGenerators} from '../hooks/useGetGenerators';
import {LabelCollection} from '../models';
import {Styled} from './styles/ListPageStyled';

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
      <Common.Helper />
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href="#">{translate('pim_title.pim_settings_index')}</Breadcrumb.Step>
            <Breadcrumb.Step href="#">{translate('pim_title.akeneo_identifier_generator_index')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-identifier-generator-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <Button onClick={onCreate} disabled={!isGeneratorListEmpty}>
            {translate('pim_common.create')}
          </Button>
        </PageHeader.Actions>
        <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
      </PageHeader>
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
            {isLoading && (
              <>
                <Styled.SkeletonContainer>
                  <Styled.Skeleton />
                  <Styled.Skeleton />
                  <Styled.Skeleton />
                </Styled.SkeletonContainer>
              </>
            )}
            {!isGeneratorListEmpty && (
              <>
                <tr>
                  <td colSpan={3}>
                    <Helper level="info">
                      It is only possible to create one Identifier Generator for the moment. You will soon be able to
                      configure multiple generators matching all your usecases.{' '}
                      <a
                        href="https://help.akeneo.com/pim/serenity/articles/understand-data-quality.html"
                        target="_blank"
                        rel="noreferrer"
                      >
                        Check out our Help Center for more information.
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
                      <Button ghost level="primary">
                        Edit
                      </Button>
                    </Table.ActionCell>
                  </Table.Row>
                ))}
              </>)
            }
          </Table.Body>
        </Table>
      </Styled.Container>
    </>
  );
};

export {ListPage};
