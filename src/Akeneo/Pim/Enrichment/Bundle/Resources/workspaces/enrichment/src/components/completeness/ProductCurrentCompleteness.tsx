import React, {FC, Fragment} from 'react';
import {CurrentCompleteness, LocaleCurrentCompleteness, MissingAttribute} from "../../models";
import {useBooleanState, Dropdown, Badge, ProgressBar, Link, SwitcherButton, Level} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import styled from "styled-components";

type Props = {
  currentCompleteness: CurrentCompleteness | null;
};

const getCompletenessVariationLevel = (ratio: number): Level => {
  return ratio < 100 ? 'warning' : 'primary';
};

const LocaleCompletenessContainer = styled.div`
  padding: 10px 20px 10px 20px;
  min-width: 350px;
`;

const MissingAttributesContainer = styled.div`
  margin-top: 10px;
`;

const MissingAttributeLink = styled(Link)`
  padding-left: 4px;
`;

const MissingAttributeSeparator = styled.span`
  padding-left: 4px;
`;

const HeaderContainer = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
`;

const ProductCurrentCompleteness: FC<Props> = ({currentCompleteness}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);

  if (currentCompleteness === null) {
    return null;
  }

  return (
    <Dropdown>
      <SwitcherButton
        inline
        onClick={open}
        label={translate('pim_enrich.entity.product.module.completeness.complete')}
      >
        <Badge level={getCompletenessVariationLevel(currentCompleteness.channelRatio)}>
          {`${currentCompleteness.channelRatio}%`}
        </Badge>
      </SwitcherButton>
      {isOpen && <Dropdown.Overlay verticalPosition="down" onClose={close}>
        <Dropdown.Header>
          <HeaderContainer>
            <Dropdown.Title>
            {translate('pim_enrich.entity.product.module.completeness.complete')}
            </Dropdown.Title>
            <Badge level={getCompletenessVariationLevel(currentCompleteness.channelRatio)}>
              {`${currentCompleteness.channelRatio}%`}
            </Badge>
          </HeaderContainer>
        </Dropdown.Header>
        <Dropdown.ItemCollection>
            {Object.entries(currentCompleteness.localesCompleteness).map(([localeCode, localeCurrentCompleteness]: [string, LocaleCurrentCompleteness]) => {
              return (
                <LocaleCompletenessContainer key={localeCode}>
                  <ProgressBar
                    title={localeCurrentCompleteness.label}
                    size="small"
                    level={getCompletenessVariationLevel(localeCurrentCompleteness.ratio)}
                    percent={localeCurrentCompleteness.ratio}
                    progressLabel={`${localeCurrentCompleteness.ratio} %`}
                  />
                  {localeCurrentCompleteness.missingCount > 0 && <MissingAttributesContainer>
                    {translate('pim_enrich.entity.product.module.completeness.missing_values', {count: localeCurrentCompleteness.missingCount}, localeCurrentCompleteness.missingCount)}:
                    {localeCurrentCompleteness.missingAttributes.map((missingAttribute: MissingAttribute, index: number) => {
                      return (
                        <Fragment key={missingAttribute.code}>
                          {index > 0 && <MissingAttributeSeparator>|</MissingAttributeSeparator>}
                          <MissingAttributeLink href="" decorated={false}>{missingAttribute.label}</MissingAttributeLink>
                        </Fragment>
                      );
                    })}
                  </MissingAttributesContainer>}
                </LocaleCompletenessContainer>
              );
            })}
            </Dropdown.ItemCollection>
      </Dropdown.Overlay>}
    </Dropdown>
  );
};

export {ProductCurrentCompleteness};
