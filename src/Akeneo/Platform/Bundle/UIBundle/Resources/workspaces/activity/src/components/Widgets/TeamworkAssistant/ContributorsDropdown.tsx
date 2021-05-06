import React, {FC, UIEvent, useEffect, useRef, useState} from 'react';
import {Dropdown, SwitcherButton, useBooleanState, Search} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Contributor} from '../../../domain';
import {useSearchContributors} from '../../../hooks';
import {SearchingPlaceholder} from './SearchingPlaceholder';
import {NoResults} from './NoResults';
import styled from 'styled-components';

const loadNextPageThreshold = 100; //value in pixels
const contributorsPerPage = 20;

type ContributorsDropdownProps = {
  setCurrentContributorUsername: (contributorUsername: string | null) => void;
  currentProjectCode: string;
};

const ContributorsDropdown: FC<ContributorsDropdownProps> = ({setCurrentContributorUsername, currentProjectCode}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const [isContributorsDropdownOpen, openContributorsDropdown, closeContributorsDropdown] = useBooleanState(false);
  const {
    contributors,
    isFetching,
    lastResultsLoaded,
    searchPage,
    setSearchPage,
    searchTerm,
    setSearchTerm,
    debouncedSearchPage,
    isSearchResults,
  } = useSearchContributors(isContributorsDropdownOpen, contributorsPerPage, currentProjectCode);
  const dropdownRef = useRef<HTMLDivElement>(null);
  const [selectedContributorLabel, setSelectedContributorLabel] = useState<string>(translate('pim_common.all'));

  const onSelectContributor = (contributor: Contributor) => {
    setCurrentContributorUsername(contributor.code);
    closeContributorsDropdown();
    setSelectedContributorLabel(getContributorLabel(contributor));
  };

  const onResetContributor = () => {
    setCurrentContributorUsername(null);
    closeContributorsDropdown();
    setSelectedContributorLabel(translate('pim_common.all'));
  };

  const getContributorLabel = (contributor: Contributor) => {
    const currentUserLabel =
      contributor.code === userContext.get('username') ? ` (${translate('teamwork_assistant.widget.you')})` : '';

    return `${contributor.first_name} ${contributor.last_name}${currentUserLabel}`;
  };

  const onScroll = (event: UIEvent) => {
    if (
      event.currentTarget.scrollTop + event.currentTarget.clientHeight >=
        event.currentTarget.scrollHeight - loadNextPageThreshold &&
      !isFetching &&
      !lastResultsLoaded &&
      debouncedSearchPage === searchPage
    ) {
      setSearchPage(searchPage + 1);
    }
  };

  useEffect(() => {
    if (dropdownRef.current && contributors.length > contributorsPerPage * searchPage) {
      dropdownRef.current.scrollTo({top: 0});
    }
  }, [contributors, contributorsPerPage, searchPage]);

  return (
    <Dropdown>
      <SwitcherButton label={translate('teamwork_assistant.widget.contributors')} onClick={openContributorsDropdown}>
        <ContributorLabel className="contributor-selector">{selectedContributorLabel}</ContributorLabel>
      </SwitcherButton>
      {isContributorsDropdownOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={closeContributorsDropdown}>
          <Dropdown.Header>
            <Search
              onSearchChange={setSearchTerm}
              searchValue={searchTerm}
              placeholder={translate('pim_common.search')}
            />
          </Dropdown.Header>
          {isFetching && contributors.length === 0 && (
            <SearchingPlaceholder>{translate('teamwork_assistant.widget.searching')}</SearchingPlaceholder>
          )}
          {!isFetching && contributors.length === 0 && <NoResults />}
          <Dropdown.ItemCollection ref={dropdownRef} onScroll={onScroll}>
            {contributors.length > 0 && !isSearchResults && (
              <Dropdown.Item onClick={onResetContributor}>{translate('pim_common.all')}</Dropdown.Item>
            )}
            {(!isFetching || contributors.length > 0) &&
              contributors.map((contributor: Contributor) => {
                return (
                  <Dropdown.Item
                    onClick={() => onSelectContributor(contributor)}
                    key={contributor.code}
                    className="contributor-label"
                  >
                    {getContributorLabel(contributor)}
                  </Dropdown.Item>
                );
              })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

const ContributorLabel = styled.div`
  max-width: 200px;
  display: block;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
`;

export {ContributorsDropdown};
