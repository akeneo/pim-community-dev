import React, {FC, Fragment} from 'react';
import {CurrentCompleteness, LocaleCurrentCompleteness, MissingAttribute, Product} from '../../models';
import {
  Badge,
  Dropdown,
  getColor,
  Level,
  Link,
  ProgressBar,
  SwitcherButton,
  useBooleanState,
} from 'akeneo-design-system';
import {useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {useScrollToAttribute} from '../../hooks';

type Props = {
  currentCompleteness: CurrentCompleteness | null;
  product: Product;
  changeLocale: (localeCode: string) => void;
  redirectToAttributeTab: () => void;
};

const ProductCurrentCompleteness: FC<Props> = ({
  currentCompleteness,
  product,
  redirectToAttributeTab,
  changeLocale,
}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const userContext = useUserContext();
  const {setAttributeToScrollTo, setIsAttributeDisplayed} = useScrollToAttribute(product);

  const router = useRouter();

  if (currentCompleteness === null) {
    return null;
  }

  const followAttribute = (localeCode: string, attributeCode: string) => {
    close();
    if (userContext.get('catalogLocale') !== localeCode) {
      changeLocale(localeCode);
    }

    const currentTab = sessionStorage.getItem('current_column_tab');
    const familyVariant = product.meta.family_variant;

    if (null !== familyVariant) {
      if (!product.meta.attributes_for_this_level.includes(attributeCode)) {
        redirectToModel(product, attributeCode);
        return;
      }
    }

    if (currentTab !== 'pim-product-edit-form-attributes') {
      redirectToAttributeTab();
    } else {
      setIsAttributeDisplayed(true);
    }
    setAttributeToScrollTo(attributeCode);
  };

  const redirectToModel = (product: Product, attributeCode: string) => {
    let modelId = product.meta.variant_navigation[0].selected.id;
    const comesFromParent = product.meta.parent_attributes.includes(attributeCode);
    const hasTwoLevelsOfVariation = 3 === product.meta.variant_navigation.length;
    if (comesFromParent && hasTwoLevelsOfVariation) {
      modelId = product.meta.variant_navigation[1].selected.id;
    }

    sessionStorage.setItem('current_column_tab', 'pim-product-model-edit-form-attributes');
    sessionStorage.setItem('attributeToScrollTo', attributeCode);

    sessionStorage.setItem('filter_missing_required_attributes', 'true');
    router.redirect(router.generate('pim_enrich_product_model_edit', {id: modelId}));
  };

  return (
    <StyledDropdown>
      <SwitcherButton inline onClick={open} label={translate('pim_enrich.entity.product.module.completeness.complete')}>
        <Badge level={getCompletenessVariationLevel(currentCompleteness.channelRatio)}>
          {`${currentCompleteness.channelRatio}%`}
        </Badge>
      </SwitcherButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <HeaderContainer>
              <Dropdown.Title>{translate('pim_enrich.entity.product.module.completeness.complete')}</Dropdown.Title>
              <Badge level={getCompletenessVariationLevel(currentCompleteness.channelRatio)}>
                {`${currentCompleteness.channelRatio}%`}
              </Badge>
            </HeaderContainer>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {Object.entries(currentCompleteness.localesCompleteness).map(
              ([localeCode, localeCurrentCompleteness]: [string, LocaleCurrentCompleteness]) => {
                return (
                  <LocaleCompletenessContainer key={localeCode}>
                    <ProgressBar
                      title={localeCurrentCompleteness.label}
                      size="small"
                      level={getCompletenessVariationLevel(localeCurrentCompleteness.ratio)}
                      percent={localeCurrentCompleteness.ratio}
                      progressLabel={`${localeCurrentCompleteness.ratio} %`}
                    />
                    {localeCurrentCompleteness.missingCount > 0 && (
                      <MissingAttributesContainer>
                        {translate(
                          'pim_enrich.entity.product.module.completeness.missing_values',
                          {count: localeCurrentCompleteness.missingCount},
                          localeCurrentCompleteness.missingCount
                        )}
                        :
                        {localeCurrentCompleteness.missingAttributes.map(
                          (missingAttribute: MissingAttribute, index: number) => {
                            return (
                              <Fragment key={missingAttribute.code}>
                                {index > 0 && <MissingAttributeSeparator>|</MissingAttributeSeparator>}
                                <MissingAttributeLink
                                  decorated={false}
                                  onClick={() => followAttribute(localeCode, missingAttribute.code)}
                                >
                                  {missingAttribute.label}
                                </MissingAttributeLink>
                              </Fragment>
                            );
                          }
                        )}
                      </MissingAttributesContainer>
                    )}
                  </LocaleCompletenessContainer>
                );
              }
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </StyledDropdown>
  );
};

const getCompletenessVariationLevel = (ratio: number): Level => {
  return ratio < 100 ? 'warning' : 'primary';
};

const StyledDropdown = styled(Dropdown)`
  margin-right: 10px;
`;

const LocaleCompletenessContainer = styled.div`
  padding: 10px 20px 10px 20px;
  min-width: 350px;
`;

const MissingAttributesContainer = styled.div`
  margin-top: 10px;
`;

const MissingAttributeLink = styled(Link)`
  padding-left: 4px;
  color: ${getColor('brand', 100)};
`;

const MissingAttributeSeparator = styled.span`
  padding-left: 4px;
`;

const HeaderContainer = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
`;

export {ProductCurrentCompleteness};
