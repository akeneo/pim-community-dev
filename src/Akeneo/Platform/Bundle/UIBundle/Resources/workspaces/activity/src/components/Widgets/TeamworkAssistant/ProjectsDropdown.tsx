import React, {FC, UIEvent, useEffect, useRef} from 'react';
import {Badge, Dropdown, SwitcherButton, useBooleanState, Search} from 'akeneo-design-system';
import {Project} from '../../../domain';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useSearchProjects} from '../../../hooks';
import {SearchingPlaceholder} from './SearchingPlaceholder';
import {NoResults} from './NoResults';
import styled from 'styled-components';

const loadNextPageThreshold = 100; //value in pixels
const projectsPerPage = 20;

type ProjectsDropdownProps = {
  project: Project;
  setCurrentProjectCode: (projectCode: string) => void;
};

const ProjectsDropdown: FC<ProjectsDropdownProps> = ({project, setCurrentProjectCode}) => {
  const translate = useTranslate();
  const [isProjectsDropdownOpen, openProjectsDropdown, closeProjectsDropdown] = useBooleanState(false);
  const {
    projects,
    isFetching,
    lastResultsLoaded,
    searchPage,
    setSearchPage,
    searchTerm,
    setSearchTerm,
    debouncedSearchPage,
  } = useSearchProjects(isProjectsDropdownOpen, projectsPerPage, project.code);
  const dropdownRef = useRef<HTMLDivElement>(null);

  const onSelectProject = (projectCode: string) => {
    setCurrentProjectCode(projectCode);
    closeProjectsDropdown();
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
    if (dropdownRef.current && projects.length > projectsPerPage * searchPage) {
      dropdownRef.current.scrollTo({top: 0});
    }
  }, [projects, projectsPerPage, searchPage]);

  const projectCompleteness: any = project && Math.round(project.completeness.ratio_done);

  return (
    <Dropdown>
      <SwitcherButton label={translate('teamwork_assistant.widget.projects')} onClick={openProjectsDropdown}>
        <ProjectLabelContainer className="project-selector">
          <ProjectLabel>{project.label} &nbsp;</ProjectLabel>
          <Badge level={projectCompleteness < 100 ? 'warning' : 'primary'}>{projectCompleteness}%</Badge>
        </ProjectLabelContainer>
      </SwitcherButton>
      {isProjectsDropdownOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={closeProjectsDropdown}>
          <Dropdown.Header>
            <Search
              onSearchChange={setSearchTerm}
              searchValue={searchTerm}
              placeholder={translate('pim_common.search')}
            />
          </Dropdown.Header>
          {isFetching && projects.length === 0 && (
            <SearchingPlaceholder>{translate('teamwork_assistant.widget.searching')}</SearchingPlaceholder>
          )}
          {!isFetching && projects.length === 0 && <NoResults />}
          <Dropdown.ItemCollection ref={dropdownRef} onScroll={onScroll}>
            {(!isFetching || projects.length > 0) &&
              projects.map((project: Project) => {
                const projectCompleteness: number = Math.round(project.completeness.ratio_done);

                return (
                  <Dropdown.Item
                    onClick={() => onSelectProject(project.code)}
                    key={project.code}
                    className="project-label"
                  >
                    {project.label}
                    <Badge level={projectCompleteness < 100 ? 'warning' : 'primary'}>{projectCompleteness}%</Badge>
                  </Dropdown.Item>
                );
              })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

const ProjectLabelContainer = styled.div`
  display: flex;
`;

const ProjectLabel = styled.div`
  max-width: 280px;
  display: block;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
`;

export {ProjectsDropdown};
