import React from "react";
import { Translate } from "../../../dependenciesTools";
import { RuleAddToCategory } from "./RuleAddToCategory";
import { RuleProductSelection } from "./RuleProductSelection";

type Props = {
  translate: Translate;
};

const RulesBuilder: React.FC<Props> = ({ translate }) => {
  return (
    <>
      <RuleProductSelection translate={translate} />
      <RuleAddToCategory translate={translate} />
    </>
  );
};

RulesBuilder.displayName = "RulesBuilder";

export { RulesBuilder };
