import styled from "styled-components";
import {getColor, SectionTitle} from "akeneo-design-system";

const FilterBox = styled.div`
  margin-bottom: 10px;
  width: 200px;
`;

const FilterSectionTitleTitle = styled(SectionTitle.Title)`
  color: ${getColor('brand', 100)};
`;
const FilterSectionTitle = styled(SectionTitle)`
  border-bottom-color: ${getColor('brand', 100)};
`;

const FilterContainer = styled.div`
  width: 280px;
  padding: 0 20px 10px;
`;

const FilterButtonContainer = styled.div`
  text-align: center;
`;

export {FilterBox, FilterSectionTitleTitle, FilterSectionTitle, FilterContainer, FilterButtonContainer};
