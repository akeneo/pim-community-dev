import {denormalize} from "../models/rule-definition-denormalizer";
import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";
import {RuleDefinition} from "../models/RuleDefinition";
import {httpGet} from "./fetch";

export const getByCode = async (ruleDefinitionCode: string, router: Router): Promise<RuleDefinition> => {
  const url = router.generate('pimee_enrich_rule_definition_get', { ruleCode: ruleDefinitionCode });
  const response = await httpGet(url);
  const json = await response.json();

  return denormalize(json);
};
