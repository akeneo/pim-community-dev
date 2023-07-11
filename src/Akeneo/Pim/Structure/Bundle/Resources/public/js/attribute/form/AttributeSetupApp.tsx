import React, {FC} from 'react';
import {getColor, Helper, IconButton, List, LockIcon} from 'akeneo-design-system';
import {Attribute} from '../models/Attribute';
import styled from 'styled-components';
import {useFeatureFlags, useTranslate} from '@akeneo-pim-community/shared';

const CustomList = styled(List)`
  & > * {
    padding-left: 20px;
  }
`

const TitleCell = styled(List.Cell)`
  font-size: 15px;
  display: block;
  
  em {
    font-style: initial;
    color: ${getColor('brand', 100)};
  }
`;

type AttributeSetupAppProps = {
  attribute: Attribute;
}

const AttributeSetupApp: FC<AttributeSetupAppProps> = ({attribute}) => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();

  const isReadOnlyFeatureEnabled = isEnabled('read_only_product_attribute');

  return (
    <>
      <Helper level="error">
        Actions taken here could greatly impact your products.
      </Helper>
      <CustomList>
        <List.Row>
          <TitleCell width="auto">
            The attribute is an <em>{translate(`pim_enrich.entity.attribute.property.type.${attribute.type}`)}</em>
          </TitleCell>
          <List.RowHelpers>
            An attribute's type cannot be modified as this would result in the deletion of all its product values
          </List.RowHelpers>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <TitleCell width="auto">
            Value par channel ? <em>{JSON.stringify(attribute.scopable)}</em>
          </TitleCell>
          <List.RowHelpers>
            Text value per channel
          </List.RowHelpers>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <TitleCell width="auto">
            Value par locale ? <em>{JSON.stringify(attribute.localizable)}</em>
          </TitleCell>
          <List.RowHelpers>
            Text value per locale
          </List.RowHelpers>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        {isReadOnlyFeatureEnabled &&
        <List.Row>
          <TitleCell width="auto">
            Readonly ? <em>{JSON.stringify(attribute.is_read_only)}</em>
          </TitleCell>
          <List.RowHelpers>
            Text readonly
          </List.RowHelpers>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>
        }
      </CustomList>
    </>
  );
};

export {AttributeSetupApp};
