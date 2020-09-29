import React, {ChangeEvent, FunctionComponent, useEffect, useRef, useState} from "react";
import useFetchFamilies from "../../../../../infrastructure/hooks/Dashboard/useFetchFamilies";
import {DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY} from "../../../../constant/Dashboard";
import styled from "styled-components";
import {debounce} from "lodash";

const __ = require('oro/translator');
const UserContext = require('pim/user-context');

type Labels = {
  [localeCode: string]: string;
};

type Family = {
  code: string;
  labels: Labels;
};

interface FamilyFilterProps {
  familyCode: string | null;
}

const FamilyFilter: FunctionComponent<FamilyFilterProps> = ({familyCode}) => {
  const [isFilterDisplayed, setIsFilterDisplayed] = useState(false);
  const [filteredFamilies, setFilteredFamilies] = useState<Family[]>([]);
  const [searchString, setSearchString] = useState(null);

  const uiLocale = UserContext.get('uiLocale');
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
    // @ts-ignore
    setFilteredFamilies(Object.values(families).filter((family: any) => family.labels[uiLocale].toLowerCase().includes(searchString.toLowerCase())));
  }, [families, searchString]);

  const handleClickFamily = (familyCode: string | null) => {
    window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY, {detail: {
        familyCode: familyCode
      }}));
    setIsFilterDisplayed(false);
    setSearchString(null);
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
    }
  }, []);

  let currentFamilyLabel = __('pim_common.all');
  if(familyCode !== null && Object.keys(families).length > 0) {
    const currentFamily: any = Object.values(families).find((family: any) => family.code === familyCode);
    currentFamilyLabel = currentFamily.labels[uiLocale];
  }

  const FamilyLabel = styled.span`
    text-transform: capitalize;
  `;

  return (

    <div className="AknFilterBox-filterContainer">
      <div className="AknFilterBox-filter" onClick={() => setIsFilterDisplayed(true)} data-testid={'dqiFamilyFilter'}>
        <span className="AknFilterBox-filterLabel">{__('pim_enrich.entity.family.uppercase_label')}</span>
        <button type="button" className="AknFilterBox-filterCriteria ui-multiselect">
          <span> {currentFamilyLabel ? currentFamilyLabel : "[" + familyCode + "]"}</span>
          <span className="AknFilterBox-filterCaret"/>
        </button>
      </div>

      {isFilterDisplayed && (
        <div ref={ref} id="AknFamily-filter" className="ui-multiselect-menu ui-widget ui-widget-content ui-corner-all AknFilterBox-filterCriteria select-filter-widget multiselect-filter-widget">
          <div className="ui-widget-header ui-corner-all ui-multiselect-header ui-helper-clearfix ui-multiselect-hasfilter">
            <div className="ui-multiselect-filter">
              <input autoFocus={true} placeholder={__('pim_enrich.entity.family.uppercase_label')} type="search" onChange={(event: ChangeEvent<HTMLInputElement>) => {
                event.persist();
                debounceOnSearch(event)
              }}/>
            </div>
          </div>
          <ul className="ui-multiselect-checkboxes ui-helper-reset">
            {(searchString === '' || searchString === null) && (
              <li className={familyCode === null ? 'ui-state-active' : ''}>
                <label onClick={() => handleClickFamily(null)} className={familyCode === null ? 'ui-state-active' : ''}>
                  <FamilyLabel>{__('pim_common.all')}</FamilyLabel>
                </label>
              </li>
            )}
            {filteredFamilies && Object.entries(filteredFamilies).map(([identifier, family]:[string, Family]) => {
              return (
                <li key={identifier}>
                  <label onClick={() => handleClickFamily(family.code)} className={family.code === familyCode ? 'ui-state-active' : ''} data-testid={`dqiFamily_${family.code}`}>
                    <FamilyLabel>{family.labels[uiLocale] ? family.labels[uiLocale] : "[" + family.code + "]"}</FamilyLabel>
                  </label>
                </li>
              )
            })}
          </ul>
        </div>
      )}

    </div>

  )

};

export default FamilyFilter;
