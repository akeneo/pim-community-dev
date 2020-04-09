import {denormalize} from "../models/rule-definition-denormalizer";
import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";
import {RuleDefinition} from "../models/RuleDefinition";

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

export const getByCode = async (ruleDefinitionCode: string, router: Router): Promise<RuleDefinition> => {
  const url = router.generate('pimee_enrich_rule_definition_get', { ruleCode: ruleDefinitionCode });
  const response = await get(url);
  const json = await response.json();

  return denormalize(json);
};
