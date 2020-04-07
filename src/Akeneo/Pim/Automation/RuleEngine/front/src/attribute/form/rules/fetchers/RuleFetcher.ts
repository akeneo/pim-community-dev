import {denormalize} from "../models/rules-denormalizer";
import Rule from "../models/Rule";
import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";

function get(url: string) {
  return fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include',
    method: 'GET'
  });
}

export const getByCode = async (ruleCode: string, router: Router): Promise<Rule> => {
  const url = router.generate('pimee_enrich_rule_definition_get', { ruleCode: ruleCode });
  const response = await get(url);
  const json = await response.json();

  return denormalize(json);
};
