import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {useSearchProjects} from '@akeneo-pim-ee/activity/src/hooks';
import {Project} from '@akeneo-pim-ee/activity/src/domain';
import {act} from 'react-test-renderer';
import {fetchProjects} from '@akeneo-pim-ee/activity/src/fetchers';
import {aProject, buildProjects} from './utils/provideTwaProjectsHelper';

jest.mock('@akeneo-pim-ee/activity/src/fetchers/fetchProjects');

describe('UseSearchProjects', () => {
  const renderInitialState = (isDropdownOpen: boolean) => {
    return renderHookWithProviders(() => useSearchProjects(isDropdownOpen, 5));
  };

  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.resetAllMocks();
  });

  test('initial render', () => {
    const {result} = renderInitialState(false);
    expect(result.current.projects).toEqual([]);
    expect(result.current.isFetching).toEqual(false);
    expect(result.current.lastResultsLoaded).toEqual(false);
    expect(result.current.searchPage).toEqual(1);
    expect(result.current.searchTerm).toEqual('');
  });

  test('Load results with only 1 page', async () => {
    const projects: Project[] = [aProject('project1'), aProject('project2')];
    fetchProjects.mockResolvedValueOnce(projects);
    const {result, waitForNextUpdate} = renderInitialState(true);

    await act(async () => {
      expect(result.current.isFetching).toEqual(true);
      expect(result.current.projects).toEqual([]);
    });

    await waitForNextUpdate();

    await act(async () => {
      expect(result.current.projects).toEqual(projects);
      expect(result.current.isFetching).toEqual(false);
      expect(result.current.lastResultsLoaded).toEqual(true);
    });
  });

  test('Browse 3 pages of projects without a search', async () => {
    const projects: Project[] = buildProjects(12);
    fetchProjects.mockResolvedValueOnce(projects.slice(0, 5));
    fetchProjects.mockResolvedValueOnce(projects.slice(5, 10));
    fetchProjects.mockResolvedValueOnce(projects.slice(10, 12));
    const {result, waitForNextUpdate, waitForValueToChange} = renderInitialState(true);
    await act(async () => {
      expect(result.current.isFetching).toEqual(true);
      expect(result.current.projects).toEqual([]);
    });

    await waitForNextUpdate();

    await act(async () => {
      expect(result.current.projects).toEqual(projects.slice(0, 5));
      expect(result.current.isFetching).toEqual(false);
      expect(result.current.lastResultsLoaded).toEqual(false);
      expect(result.current.searchPage).toEqual(1);
    });

    await act(async () => {
      result.current.setSearchPage(2);
      await waitForValueToChange(() => {
        return result.current.projects;
      });
    });

    expect(result.current.projects).toEqual(projects.slice(0, 10));
    expect(result.current.lastResultsLoaded).toEqual(false);
    expect(result.current.searchPage).toEqual(2);

    await act(async () => {
      result.current.setSearchPage(3);
      await waitForValueToChange(() => {
        return result.current.projects;
      });
    });

    expect(result.current.projects).toEqual(projects.slice(0, 12));
    expect(result.current.lastResultsLoaded).toEqual(true);
    expect(result.current.searchPage).toEqual(3);
  });

  test('Search projects and browse the 3 pages of results', async () => {
    //project1 -> project18
    const projects: Project[] = buildProjects(18);
    fetchProjects.mockResolvedValueOnce(projects.slice(0, 5));
    //project1, project10, project11, project12, project13
    const firstSearchResultsPage = [projects[0], ...projects.slice(9, 14)];
    fetchProjects.mockResolvedValueOnce(firstSearchResultsPage);
    //project14, project15, project16, project17, project18
    const secondSearchResultsPage = projects.slice(14, 18);
    fetchProjects.mockResolvedValueOnce(secondSearchResultsPage);
    //No projects for the last page
    fetchProjects.mockResolvedValueOnce([]);

    const {result, waitForNextUpdate, waitForValueToChange} = renderInitialState(true);
    await act(async () => {
      expect(result.current.isFetching).toEqual(true);
      expect(result.current.projects).toEqual([]);
    });

    await waitForNextUpdate();

    await act(async () => {
      expect(result.current.projects).toEqual(projects.slice(0, 5));
      expect(result.current.isFetching).toEqual(false);
      expect(result.current.lastResultsLoaded).toEqual(false);
      expect(result.current.searchPage).toEqual(1);
    });

    await act(async () => {
      //Search for projects with "project1" in the label (project1, project10, project11, ..., project18)
      result.current.setSearchTerm('project1');
      //We have to wait for the debounce and that the projects are updated
      await waitForValueToChange(() => {
        return result.current.projects;
      });
    });

    expect(result.current.projects).toEqual(firstSearchResultsPage);
    expect(result.current.lastResultsLoaded).toEqual(false);
    expect(result.current.searchPage).toEqual(1);
    expect(result.current.searchTerm).toEqual('project1');

    await act(async () => {
      result.current.setSearchPage(2);
      await waitForValueToChange(() => {
        return result.current.projects;
      });
    });

    expect(result.current.projects).toEqual([...firstSearchResultsPage, ...projects.slice(14, 18)]);
    expect(result.current.lastResultsLoaded).toEqual(false);
    expect(result.current.searchPage).toEqual(2);
    expect(result.current.searchTerm).toEqual('project1');

    await act(async () => {
      result.current.setSearchPage(3);
      await waitForValueToChange(() => {
        return result.current.projects;
      });
    });

    //Third page has no results, so the projects stay the same
    expect(result.current.projects).toEqual([...firstSearchResultsPage, ...projects.slice(14, 18)]);
    expect(result.current.lastResultsLoaded).toEqual(true);
    expect(result.current.searchPage).toEqual(3);
  });
});
