import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { EntityTypeConditionLine } from '../../pages/EditRules/components/conditions/EntityTypeConditionLine';

const FIELD = 'entity_type';

const EntityTypeOperators: Operator[] = [Operator.EQUALS];

enum EntityType {
  PRODUCT = 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductInterface',
  PRODUCT_MODEL = 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModelInterface',
}

type EntityTypeCondition = {
  field: string;
  operator: Operator;
  value: EntityType;
};

const entityTypeConditionPredicate = (json: any): boolean => {
  return (
    json.field === FIELD &&
    EntityTypeOperators.includes(json.operator) &&
    typeof json.value === 'string'
  );
};

const getEntityTypeConditionModule: ConditionModuleGuesser = json => {
  if (!entityTypeConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(EntityTypeConditionLine);
};

const createEntityTypeCondition: ConditionFactory = (
  fieldCode: any
): Promise<EntityTypeCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<EntityTypeCondition>({
    field: FIELD,
    operator: EntityTypeOperators[0],
    value: EntityType.PRODUCT,
  });
};

export {
  EntityType,
  EntityTypeCondition,
  EntityTypeOperators,
  createEntityTypeCondition,
  getEntityTypeConditionModule,
};
