import React, {ChangeEvent, FC, useEffect, useRef, useState} from 'react';
import styled from 'styled-components';
import useFetchFamilies from '../../../../infrastructure/hooks/Dashboard/useFetchFamilies';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY} from '../../../constant';
import {debounce} from 'lodash';
import {useDashboardContext} from '../../../context/DashboardContext';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {ArrowDownIcon} from 'akeneo-design-system';

type Labels = {
  [localeCode: string]: string;
};

type Family = {
  code: string;
  labels: Labels;
};

type Props = {
  familyCode: string | null;
};

const FamilyLabel = styled.span`
  text-transform: capitalize;
`;

const Container = styled.div.attrs(() => ({
  className: 'AknDropdown AknButtonList-item',
}))`
  position: relative;
`;
const Toggle = styled.button.attrs(() => ({
  className: 'AknActionButton AknActionButton--withoutBorder',
}))`
  color: ${({theme}) => theme.color.grey140};
  font-size: ${({theme}) => theme.fontSize.default};
  white-space: nowrap;
`;

const ToggleCaret = styled(ArrowDownIcon).attrs(() => ({
  size: 12,
}))`
  color: ${({theme}) => theme.color.grey120};
  margin-left: 3px;
  vertical-align: middle;
`;

const ToggleSelection = styled.span`
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.default};
  margin-left: 3px;
`;

const Menu = styled.div.attrs(() => ({
  className:
    'AknDropdown-menu ui-multiselect-menu ui-widget ui-widget-content ui-corner-all AknFilterBox-filterCriteria select-filter-widget multiselect-filter-widget',
}))`
  color: ${({theme}) => theme.color.grey140};
  font-size: ${({theme}) => theme.fontSize.default};
`;

const FamilyFilter: FC<Props> = ({familyCode}) => {
  const [isFilterDisplayed, setIsFilterDisplayed] = useState(false);
  const [filteredFamilies, setFilteredFamilies] = useState<Family[]>([]);
  const [searchString, setSearchString] = useState(null);
  const {updateDashboardFilters} = useDashboardContext();

  const translate = useTranslate();
  const userContext = useUserContext();

  const uiLocale = userContext.get('uiLocale');
  let families: Family[] = useFetchFamilies(isFilterDisplayed, uiLocale);
  const ref = useRef(null);

  const handleSearchFamily = (event: ChangeEvent) => {
    // @ts-ignore
    setSearchString(event.target.value);
  };
  const debounceOnSearch = React.useCallback(debounce(handleSearchFamily, 250), []);

  useEffect(() => {
    if (searchString === null || searchString === '') {
      setFilteredFamilies(families);
      return;
    }
    setFilteredFamilies(
      // @ts-ignore
      Object.values(families).filter((family: any) =>
        // @ts-ignore
        family.labels[uiLocale].toLowerCase().includes(searchString.toLowerCase())
      )
    );
  }, [families, searchString]);

  const handleClickFamily = (familyCode: string | null) => {
    window.dispatchEvent(
      new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY, {
        detail: {
          familyCode: familyCode,
        },
      })
    );
    setIsFilterDisplayed(false);
    setSearchString(null);
    updateDashboardFilters(familyCode, null);
  };

  const handleClickOutside = (event: MouseEvent) => {
    // @ts-ignore
    if (ref.current !== null && !ref.current.contains(event.target)) {
      setIsFilterDisplayed(false);
      setSearchString(null);
    }
  };

  useEffect(() => {
    document.addEventListener('mousedown', handleClickOutside);

    return () => {
      document.removeEventListener('mousedown', handleClickOutside, true);
    };
  }, []);

  let currentFamilyLabel = translate('pim_common.all');
  if (familyCode !== null && Object.keys(families).length > 0) {
    const currentFamily: any = Object.values(families).find((family: any) => family.code === familyCode);
    currentFamilyLabel = currentFamily.labels[uiLocale];
  }

  return (
    <Container>
      <Toggle onClick={() => setIsFilterDisplayed(true)} data-testid={'dqiFamilyFilter'}>
        {translate('pim_enrich.entity.family.uppercase_label')}:
        <ToggleSelection>
          {currentFamilyLabel ? currentFamilyLabel : '[' + familyCode + ']'}
          <ToggleCaret />
        </ToggleSelection>
      </Toggle>

      {isFilterDisplayed && (
        <Menu ref={ref} id="AknFamily-filter">
          <div className="ui-widget-header ui-corner-all ui-multiselect-header ui-helper-clearfix ui-multiselect-hasfilter">
            <div className="ui-multiselect-filter">
              <input
                autoFocus={true}
                placeholder={translate('pim_enrich.entity.family.uppercase_label')}
                type="search"
                onChange={(event: ChangeEvent<HTMLInputElement>) => {
                  event.persist();
                  debounceOnSearch(event);
                }}
              />
            </div>
          </div>
          <ul className="ui-multiselect-checkboxes ui-helper-reset">
            {(searchString === '' || searchString === null) && (
              <li className={familyCode === null ? 'ui-state-active' : ''}>
                <label onClick={() => handleClickFamily(null)} className={familyCode === null ? 'ui-state-active' : ''}>
                  <FamilyLabel>{translate('pim_common.all')}</FamilyLabel>
                </label>
              </li>
            )}
            {filteredFamilies &&
              Object.entries(filteredFamilies).map(([identifier, family]: [string, Family]) => {
                return (
                  <li key={identifier}>
                    <label
                      onClick={() => handleClickFamily(family.code)}
                      className={family.code === familyCode ? 'ui-state-active' : ''}
                      data-testid={`dqiFamily_${family.code}`}
                    >
                      <FamilyLabel>
                        {family.labels[uiLocale] ? family.labels[uiLocale] : '[' + family.code + ']'}
                      </FamilyLabel>
                    </label>
                  </li>
                );
              })}
          </ul>
        </Menu>
      )}
    </Container>
  );
};

export {FamilyFilter};
