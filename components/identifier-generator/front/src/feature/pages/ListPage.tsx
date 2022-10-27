import React, {useCallback} from 'react';
import {Common} from '../components';
import {PageHeader, PimView, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {
  AttributesIllustration,
  Breadcrumb,
  Button,
  getColor,
  Helper,
  SkeletonPlaceholder,
  Table,
} from 'akeneo-design-system';
import {useGetGenerators} from '../hooks/useGetGenerators';
import styled from 'styled-components';
import {LabelCollection} from '../models';

type ListPageProps = {
  onCreate: () => void;
  isCreateEnabled: boolean;
};

const NoIdentifierMessage = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
`;

const Title = styled.div`
  font-size: 28px;
  font-weight: 400;
  margin-top: 30px;
`;

const HelpCenterLink = styled.a`
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.purple100};
  cursor: pointer;
  margin-top: 5px;
  text-decoration: underline;
`;

const Container = styled.div`
  margin: 40px 20px;
`;

const Skeleton = styled(SkeletonPlaceholder)`
  width: 100%;
  height: 50px;
`;

const SkeletonContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
`;

const Label = styled.label`
  font-style: italic;
  color: ${getColor('brand', 100)};
`;

const ListPage: React.FC<ListPageProps> = ({onCreate, isCreateEnabled}) => {
  const translate = useTranslate();
  const {data: generators, isLoading} = useGetGenerators();
  const locale = useUserContext().get('catalogLocale');

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
          <Button onClick={onCreate} disabled={!isCreateEnabled}>
            {translate('pim_common.create')}
          </Button>
        </PageHeader.Actions>
        <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
      </PageHeader>
      <Container>
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_identifier_generator.list.identifier')}</Table.HeaderCell>
            <Table.HeaderCell></Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {generators.length === 0 && !isLoading && (
              <NoIdentifierMessage>
                <AttributesIllustration />
                <Title>{translate('pim_identifier_generator.list.first_generator')}</Title>
                <HelpCenterLink
                  href="https://help.akeneo.com/pim/serenity/articles/understand-data-quality.html"
                  target="_blank"
                >
                  {translate('pim_identifier_generator.list.check_help_center')}
                </HelpCenterLink>
              </NoIdentifierMessage>
            )}
            {isLoading && (
              <>
                <SkeletonContainer>
                  <Skeleton />
                  <Skeleton />
                  <Skeleton />
                </SkeletonContainer>
              </>
            )}
            {
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
                {generators.map(({labels, code, target}) => (
                  <Table.Row key={code}>
                    <Table.Cell>
                      <Label>{getCurrentLabel(labels, code)}</Label>
                    </Table.Cell>
                    <Table.Cell>{target}</Table.Cell>
                    <Table.ActionCell>
                      <Button ghost level="primary">
                        Edit
                      </Button>
                    </Table.ActionCell>
                  </Table.Row>
                ))}
              </>
            }
          </Table.Body>
        </Table>
      </Container>
    </>
  );
};

export {ListPage};
