import React from "react";
import { Translate } from "../../../dependenciesTools";
import { RuleAddToCategory } from "./RuleAddToCategory";
import { RuleProductSelection } from "./RuleProductSelection";
import {RuleDefinition} from "../../../models/RuleDefinition";

type Props = {
  translate: Translate;
  ruleDefinition: RuleDefinition;
};

const RulesBuilder: React.FC<Props> = ({ ruleDefinition, translate }) => {
  return (
    <>
      <RuleProductSelection ruleDefinition={ruleDefinition} translate={translate} />
      <RuleAddToCategory translate={translate} />
    </>
  );
};

RulesBuilder.displayName = "RulesBuilder";

export { RulesBuilder };
