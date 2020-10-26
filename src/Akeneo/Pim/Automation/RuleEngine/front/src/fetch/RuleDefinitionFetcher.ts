import {denormalize} from '../models/rule-definition-denormalizer';
import {Router} from '../dependenciesTools/provider/applicationDependenciesProvider.type';
import {RuleDefinition} from '../models/RuleDefinition';
import {httpGet} from './fetch';
import {ServerException} from '../exceptions';

export const getRuleDefinitionByCode = async (
  ruleDefinitionCode: string,
  router: Router
): Promise<RuleDefinition> => {
  const url = router.generate('pimee_enrich_rule_definition_get', {
    ruleCode: ruleDefinitionCode,
  });
  const response = await httpGet(url);
  if (response.status !== 200) {
    throw new ServerException(
      response.status,
      'pimee_catalog_rule.exceptions.loading'
    );
  }

  const json = await response.json();

  return denormalize(json, router);
};
