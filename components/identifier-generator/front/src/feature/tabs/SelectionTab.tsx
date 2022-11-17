import React from 'react';
import {IdentifierGenerator} from '../models';
import {Helper, Link, SectionTitle, Table, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useIdentifierAttributes} from '../hooks';
import {Styled} from '../components/Styled';
import {ListSkeleton} from '../components';

type SelectionTabProps = {
  generator: IdentifierGenerator;
};

const SelectionTab: React.FC<SelectionTabProps> = ({generator}) => {
  const helpCenterUrl = 'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html';
  const translate = useTranslate();
  const {data: identifiers, isLoading} = useIdentifierAttributes();

  return <>
    <SectionTitle>
      <SectionTitle.Title>{translate('pim_identifier_generator.tabs.product_selection')}</SectionTitle.Title>
    </SectionTitle>
    <Helper level="info">
      {translate('pim_identifier_generator.selection.helper')}{' '}
      <Link target={'_blank'} href={helpCenterUrl} rel="noreferrer">
        {translate('pim_identifier_generator.list.check_help_center')}
      </Link>
    </Helper>
    <Table>
      {isLoading && <ListSkeleton/>}
      {!isLoading &&
      <Table.Row>
        <Styled.TitleCell>
          {identifiers ? identifiers[0].label : generator.target}
        </Styled.TitleCell>
        <Table.Cell>
          <Styled.InputContainer>
            <TextInput value={'Is empty'} readOnly={true} />
          </Styled.InputContainer>
        </Table.Cell>
      </Table.Row>}
    </Table>
  </>;
};

export {SelectionTab};
