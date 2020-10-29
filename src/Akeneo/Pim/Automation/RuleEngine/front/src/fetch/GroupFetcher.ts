import {httpGet} from './fetch';
import {Group, GroupCode} from '../models';
import {Router} from '../dependenciesTools';

type IndexedGroups = {[groupCode: string]: Group};

const fetchGroupsByIdentifiers = async (
  identifiers: GroupCode[],
  router: Router
): Promise<IndexedGroups> => {
  const url = router.generate('pim_enrich_group_rest_search', {
    identifiers: identifiers.join(','),
  });
  const response = await httpGet(url);
  const json = response.status === 404 ? null : await response.json();

  const results: IndexedGroups = {};
  json.results?.forEach((element: {id: string; text: string}) => {
    results[element.id] = {
      code: element.id,
      label: element.text,
    };
  });

  return results;
};

export {fetchGroupsByIdentifiers, IndexedGroups};
