import React, {FC} from 'react';
import {getColor, getFontSize, Helper, IconButton, Link, List, LockIcon} from 'akeneo-design-system';
import {Attribute} from '../models/Attribute';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

const ListCellInner = styled.div`
  padding-left: 10px;
  flex-direction: column;
  align-items: baseline;
  line-height: 22px;
  color: ${getColor('grey', 120)};
  
  header {
    font-size: ${getFontSize('big')};
    display: block;
    font-weight: 600;

    em {
      font-style: initial;
      color: ${getColor('brand', 100)};
    }
  }
`;

type AttributeSetupAppProps = {
  attribute: Attribute;
}

const AttributeSetupApp: FC<AttributeSetupAppProps> = ({attribute}) => {
  const translate = useTranslate();
  const urlScopable = 'http://todo';
  const urlLocalizable = 'http://todo';

  return (
    <>
      <Helper level="error">
        Actions taken here could greatly impact your products.
      </Helper>
      <List>
        <List.Row>
          <List.Cell width='auto'>
            <ListCellInner>
              <header>
                This attribute type is <em>{translate(`pim_enrich.entity.attribute.property.type.${attribute.type}`)}</em>
              </header>
              An attribute’s type cannot be modified as this would result in the deletion of all its product values.
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <List.Cell width='auto'>
            <ListCellInner>
              <header>
                {attribute.unique ? <>
                    This attribute has a <em>unique value</em>.
                  </> :
                  <>This attribute don't have <em>unique value</em>.</>
                }
              </header>
              The value of this attribute must be unique. This cannot be modified. // TODO Have opposite
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <List.Cell width='auto'>
            <ListCellInner>
              <header>
                {attribute.scopable ? <>
                    This attribute is <em>scopable</em>.
                  </> :
                  <>This attribute is not <em>scopable</em>.</>
                }
              </header>
              Value per channel determines if an attribute is scopable (different values for different channels) or not (one value for all channels).<br/>
              A scopable attribute cannot be changed to not scopable.
              {' '}
              <Link href={urlScopable} target='_blank'>Learn more</Link>
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <List.Cell width='auto'>
            <ListCellInner>
              <header>
                {attribute.localizable ? <>
                    This attribute is <em>localizable</em>.
                  </> :
                  <>This attribute is not <em>localizable</em>.</>
                }
              </header>
              Value per locale determines if an attribute is localizable (different values for different locales) or not (one value for all locales).<br/>
              A localizable attribute cannot be changed to not localizable.
              {' '}
              <Link href={urlLocalizable} target='_blank'>Learn more</Link>
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>
      </List>
    </>
  );
};

export {AttributeSetupApp};
