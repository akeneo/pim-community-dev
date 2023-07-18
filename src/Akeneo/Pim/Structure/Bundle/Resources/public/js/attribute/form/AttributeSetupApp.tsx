import React, {FC, useState} from 'react';
import {
  AttributesIllustration,
  Button,
  getColor,
  getFontSize,
  Helper,
  IconButton,
  Link,
  List,
  LockIcon,
  SectionTitle,
  useBooleanState,
} from 'akeneo-design-system';
import {Attribute} from '../models/Attribute';
import styled from 'styled-components';
import {
  DoubleCheckDeleteModal,
  getLabel,
  NotificationLevel,
  useFeatureFlags,
  useNotify,
  useRouter,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {useTranslateWithComponents} from './useTranslateWithComponents';

const ListWithBottomMargin = styled(List)`
  margin-bottom: 20px;
`;

const ListCellInner = styled.div`
  padding-left: 10px;
  flex-direction: column;
  align-items: baseline;
  line-height: 22px;
  color: ${getColor('grey', 120)};

  header {
    font-size: ${getFontSize('big')};
    display: block;
    color: ${getColor('grey', 140)};

    em {
      font-style: initial;
      color: ${getColor('brand', 100)};
    }
  }
`;

type AttributeSetupAppProps = {
  attribute: Attribute;
  originalMainIdentifierAttribute: Attribute;
  onMainIdentifierChange: () => void;
};

type ErrorMessage = {
  exception: 'published_product' | string;
};

const AttributeSetupApp: FC<AttributeSetupAppProps> = ({
  attribute,
  originalMainIdentifierAttribute,
  onMainIdentifierChange,
}) => {
  const translate = useTranslate();
  const translateWithComponents = useTranslateWithComponents();
  const userContext = useUserContext();
  const router = useRouter();
  const notify = useNotify();
  const featureFlags = useFeatureFlags();
  const catalogLocale = userContext.get('catalogLocale');
  const urlScopable =
    'https://help.akeneo.com/en_US/serenity-your-first-steps-with-akeneo/serenity-what-is-an-attribute#the-value-per-channel-property';
  const urlLocalizable =
    'https://help.akeneo.com/serenity-your-first-steps-with-akeneo/serenity-what-is-an-attribute#the-value-per-locale-property';
  const urlMainIdentifier =
    'https://help.akeneo.com/serenity-build-your-catalog/33-serenity-manage-your-product-identifiers';
  const [mainIdentifierAttribute, setMainIdentifierAttribute] = useState<Attribute>(originalMainIdentifierAttribute);
  const isIdentifier = attribute.type === 'pim_catalog_identifier';
  const isMainIdentifier = isIdentifier && mainIdentifierAttribute.code === attribute.code;
  const mainIdentifierCode = mainIdentifierAttribute.code;
  const mainIdentifierLabel = getLabel(mainIdentifierAttribute.labels, catalogLocale, mainIdentifierCode);
  const attributeLabel = getLabel(attribute.labels, catalogLocale, attribute.code);
  const [isOpen, open, close] = useBooleanState();
  const isOnboarderEnabled = featureFlags.isEnabled('onboarder');
  const isNotSaved = !('meta' in attribute); // A non saved attribute don't have meta from backend

  const emPlaceholder = {
    em: (innerText: string) => <em>{innerText}</em>,
  };

  const setAsMainIdentifierUrl = router.generate('pim_enrich_attribute_rest_switch_main_identifier', {
    attributeCode: attribute.code,
  });

  const redirectToPublishedProducts = () => {
    router.redirectToRoute('pimee_workflow_published_product_index');
  };

  const setAsMainIdentifier = async () => {
    const response = await fetch(setAsMainIdentifierUrl, {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
    });
    if (response.ok) {
      notify(
        NotificationLevel.SUCCESS,
        translate('pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.flash.success')
      );
      setMainIdentifierAttribute(attribute);
      onMainIdentifierChange();
    } else {
      response.json().then((errorMessage: ErrorMessage) => {
        if (errorMessage.exception === 'published_product') {
          notify(
            NotificationLevel.ERROR,
            translate(
              'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.flash.fail_published_product'
            ),
            <Link onClick={redirectToPublishedProducts}>
              {translate(
                'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.flash.fail_published_product_link'
              )}
            </Link>
          );
        } else {
          notify(
            NotificationLevel.ERROR,
            `${translate('pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.flash.fail')}
            ${errorMessage.exception}`
          );
        }
      });
    }
    close();
  };

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>
          {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.section_title')}
        </SectionTitle.Title>
      </SectionTitle>
      <Helper level="error">{translate('pim_enrich.entity.attribute.module.edit.attribute_setup.warning')}</Helper>
      <ListWithBottomMargin>
        <List.Row>
          <List.Cell width="auto">
            <ListCellInner>
              <header>
                {translateWithComponents('pim_enrich.entity.attribute.module.edit.attribute_setup.type', {
                  ...emPlaceholder,
                  attributeType: translate(`pim_enrich.entity.attribute.property.type.${attribute.type}`),
                })}
              </header>
              {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.type_helper')}
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>

        {isIdentifier && (
          <List.Row>
            <List.Cell width="auto">
              <ListCellInner>
                <header>
                  {isMainIdentifier
                    ? translateWithComponents(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.main_identifier_title',
                        emPlaceholder
                      )
                    : translateWithComponents(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.non_main_identifier_title',
                        emPlaceholder
                      )}
                </header>
                {translateWithComponents(
                  'pim_enrich.entity.attribute.module.edit.attribute_setup.main_identifier_helper',
                  {
                    maxIdentifiersCount: 10,
                    mainIdentifierLabel: mainIdentifierLabel,
                    link: innerText => (
                      <Link href={urlMainIdentifier} target="_blank">
                        {innerText}
                      </Link>
                    ),
                  }
                )}
              </ListCellInner>
            </List.Cell>
            {!isMainIdentifier && isOnboarderEnabled && (
              <List.RowHelpers>
                <Helper level="error">
                  {translate(
                    'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.onboarder_warning'
                  )}
                </Helper>
              </List.RowHelpers>
            )}
            <List.RemoveCell>
              {isMainIdentifier || isOnboarderEnabled || isNotSaved ? (
                <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
              ) : (
                <>
                  <Button level="danger" ghost onClick={open}>
                    {translate('pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.button')}
                  </Button>
                  {isOpen && (
                    <DoubleCheckDeleteModal
                      title={translate('pim_enrich.entity.attribute.plural_label')}
                      doubleCheckInputLabel={translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.confirm',
                        {
                          attributeCode: attribute.code,
                        }
                      )}
                      textToCheck={attribute.code}
                      onCancel={close}
                      onConfirm={setAsMainIdentifier}
                      confirmDeletionTitle={translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.confirm_title'
                      )}
                      confirmButtonLabel={translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.button'
                      )}
                      illustration={<AttributesIllustration />}
                    >
                      <Helper level="error">
                        {translateWithComponents(
                          'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.are_you_sure',
                          {
                            attributeLabel: attributeLabel,
                            link: innerText => (
                              <Link href={urlMainIdentifier} target="_blank">
                                {innerText}
                              </Link>
                            ),
                          }
                        )}
                      </Helper>
                      {translate(
                        'pim_enrich.entity.attribute.module.edit.attribute_setup.set_as_main_identifier.warning',
                        {
                          attributeLabel: mainIdentifierLabel,
                        }
                      )}
                    </DoubleCheckDeleteModal>
                  )}
                </>
              )}
            </List.RemoveCell>
          </List.Row>
        )}

        <List.Row>
          <List.Cell width="auto">
            <ListCellInner>
              <header>
                {attribute.unique
                  ? translateWithComponents(
                      'pim_enrich.entity.attribute.module.edit.attribute_setup.unique_attribute_title',
                      emPlaceholder
                    )
                  : translateWithComponents(
                      'pim_enrich.entity.attribute.module.edit.attribute_setup.non_unique_attribute_title',
                      emPlaceholder
                    )}
              </header>
              {attribute.unique
                ? translate('pim_enrich.entity.attribute.module.edit.attribute_setup.unique_helper')
                : translate('pim_enrich.entity.attribute.module.edit.attribute_setup.non_unique_helper')}
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
                {attribute.scopable
                  ? translateWithComponents(
                      'pim_enrich.entity.attribute.module.edit.attribute_setup.scopable_attribute_title',
                      emPlaceholder
                    )
                  : translateWithComponents(
                      'pim_enrich.entity.attribute.module.edit.attribute_setup.non_scopable_attribute_title',
                      emPlaceholder
                    )}
              </header>
              {translateWithComponents('pim_enrich.entity.attribute.module.edit.attribute_setup.scopable_helper', {
                link: innerText => (
                  <Link href={urlScopable} target="_blank">
                    {innerText}
                  </Link>
                ),
              })}
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
                {attribute.localizable
                  ? translateWithComponents(
                      'pim_enrich.entity.attribute.module.edit.attribute_setup.localizable_attribute_title',
                      emPlaceholder
                    )
                  : translateWithComponents(
                      'pim_enrich.entity.attribute.module.edit.attribute_setup.non_localizable_attribute_title',
                      emPlaceholder
                    )}
              </header>
              {translateWithComponents('pim_enrich.entity.attribute.module.edit.attribute_setup.localizable_helper', {
                link: innerText => (
                  <Link href={urlLocalizable} target="_blank">
                    {innerText}
                  </Link>
                ),
              })}
            </ListCellInner>
          </List.Cell>
          <List.RemoveCell>
            <IconButton ghost="borderless" level="tertiary" icon={<LockIcon />} title="" />
          </List.RemoveCell>
        </List.Row>
      </ListWithBottomMargin>
    </>
  );
};

export {AttributeSetupApp};
