import {FallbackCondition} from "./FallbackCondition";
import {FamilyCondition} from "./FamilyCondition";
import {PimCondition} from "./PimCondition";

export type Condition = FallbackCondition|PimCondition|FamilyCondition;
