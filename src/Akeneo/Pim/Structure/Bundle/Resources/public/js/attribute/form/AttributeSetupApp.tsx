import React, {FC} from 'react';
import {getColor, getFontSize, Helper, IconButton, Link, List, LockIcon, SectionTitle} from 'akeneo-design-system';
import {Attribute} from '../models/Attribute';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

const ListCellInner = styled.div`
  padding-left: 10px;
  flex-direction: column;
  align-items: baseline;
  line-height: 22px;
  color: ${getColor('grey', 140)};

  header {
    font-size: ${getFontSize('big')};
    display: block;

    em {
      font-style: initial;
      color: ${getColor('brand', 100)};
    }
  }
`;

type AttributeSetupAppProps = {
  attribute: Attribute;
};

const AttributeSetupApp: FC<AttributeSetupAppProps> = ({attribute}) => {
  const translate = useTranslate();
  const urlScopable =
    'https://help.akeneo.com/en_US/serenity-your-first-steps-with-akeneo/serenity-what-is-an-attribute#the-value-per-channel-property';
  const urlLocalizable =
    'https://help.akeneo.com/serenity-your-first-steps-with-akeneo/serenity-what-is-an-attribute#the-value-per-locale-property';

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>
          {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.section_title')}
        </SectionTitle.Title>
      </SectionTitle>
      <Helper level="error">{translate('pim_enrich.entity.attribute.module.edit.attribute_setup.warning')}</Helper>
      <List>
        <List.Row>
          <List.Cell width="auto">
            <ListCellInner>
              <header>
                {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.type')}{' '}
                <em>{translate(`pim_enrich.entity.attribute.property.type.${attribute.type}`)}</em>
              </header>
              {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.type_helper')}
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <List.Cell width="auto">
            <ListCellInner>
              <header>
                {attribute.unique ? (
                  <>
                    {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.unique_attribute_title')}{' '}
                    <em>
                      {translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.unique_attribute_title_highlight'
                      )}
                    </em>
                  </>
                ) : (
                  <>
                    {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.non_unique_attribute_title')}{' '}
                    <em>
                      {translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.non_unique_attribute_title_highlight'
                      )}
                    </em>
                  </>
                )}
              </header>
              {attribute.unique ?
                translate('pim_enrich.entity.attribute.module.edit.attribute_setup.unique_helper') :
                translate('pim_enrich.entity.attribute.module.edit.attribute_setup.non_unique_helper')
              }
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <List.Cell width="auto">
            <ListCellInner>
              <header>
                {attribute.scopable ? (
                  <>
                    {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.scopable_attribute_title')}{' '}
                    <em>
                      {translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.scopable_attribute_title_highlight'
                      )}
                    </em>
                  </>
                ) : (
                  <>
                    {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.non_scopable_attribute_title')}{' '}
                    <em>
                      {translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.non_scopable_attribute_title_highlight'
                      )}
                    </em>
                  </>
                )}
              </header>
              {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.scopable_helper')}{' '}
              <Link href={urlScopable} target="_blank">
                {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.learn_more')}
              </Link>
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        <List.Row>
          <List.Cell width="auto">
            <ListCellInner>
              <header>
                {attribute.localizable ? (
                  <>
                    {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.localizable_attribute_title')}{' '}
                    <em>
                      {translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.localizable_attribute_title_highlight'
                      )}
                    </em>
                  </>
                ) : (
                  <>
                    {translate(
                      'pim_enrich.entity.attribute.module.edit.attribute_setup.non_localizable_attribute_title'
                    )}{' '}
                    <em>
                      {translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.non_localizable_attribute_title_highlight'
                      )}
                    </em>
                  </>
                )}
              </header>
              {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.localizable_helper')}{' '}
              <Link href={urlLocalizable} target="_blank">
                {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.learn_more')}
              </Link>
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
