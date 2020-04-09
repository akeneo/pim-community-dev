import React from "react";
import styled from "styled-components";
import infoIcon from "../../assets/icons/info.svg";

interface Level {
  level: "info" | "warning";
}

const SubsectionHint = styled.div<Level>`
  align-items: center;
  background: ${props =>
    "info" === props.level
      ? props.theme.color.blue10
      : props.theme.color.yellow10};
  display: flex;
  margin-bottom: 1px;
`;

const HintIcon = styled.div<Level>`
  background-image: url(${infoIcon});
  background-position: center;
  background-repeat: no-repeat;
  background-size: 20px;
  flex-shrink: 0;
  height: 20px;
  margin: 12px;
  width: 20px;
`;

const HintTitle = styled.div`
  border-left: 1px solid ${({ theme }) => theme.color.grey80};
  flex-grow: 1;
  font-weight: 400;
  padding: 10px 10px 10px 16px;
`;

type Props = { warning?: boolean } | { info?: boolean };

const SmallHelper: React.FC<Props> = ({ children, ...props }) => {
  const level = "warning" in props ? "warning" : "info";

  return (
    <SubsectionHint className="AknSubsection" level={level}>
      <HintIcon level={level} />
      <HintTitle>{children}</HintTitle>
    </SubsectionHint>
  );
};

SmallHelper.displayName = "SmallHelper";

export { SmallHelper };
