import styled from 'styled-components';
import {AkeneoThemedProps, CommonStyle, getColor} from '../theme';

const StoryStyle = styled.div`
  ${CommonStyle}
  & > * {
    margin: 0 10px 20px 0;
  }
`;

const PreviewGrid = styled.div<{width: number}>`
  display: grid;
  grid-template-columns: repeat(auto-fill, ${({width}) => width}px);
  gap: 16px;
  margin-bottom: 50px;
`;

PreviewGrid.defaultProps = {
  width: 140,
};

const PreviewCard = styled.div`
  display: flex;
  flex-direction: column;
  height: 100%;
  text-align: center;
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: rgba(0, 0, 0, 0.1) 0 1px 3px 0;
  border-radius: 4px;
`;

const PreviewContainer = styled.div`
  padding: 20px;
  color: ${getColor('grey100')};
  overflow: hidden;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
`;

const LabelContainer = styled.div`
  padding: 8px 0px;
  max-width: 100%;
  white-space: nowrap;
  word-break: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
`;

const Subtitle = styled.h2`
  text-transform: Capitalize;
`;

const Content = styled.div<{width: number; height: number} & AkeneoThemedProps>`
  width: ${({width}) => width}px;
  height: ${({height}) => height}px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid ${getColor('blue', 40)};
  background-color: ${getColor('blue', 10)};
  box-sizing: border-box;
`;

const ListContextContainer = styled.div`
  display: flex;
  gap: 10px;

  & > * {
    max-width: 120px;
  }
`;

const Section = styled.div`
  display: flex;
  gap: 20px;
  flex-direction: column;
`;

const SpaceBetweenContainer = styled.div<{direction: string}>`
  display: flex;
  flex-direction: ${({direction}) => direction};
  justify-content: space-between;
  align-items: center;
`;

SpaceBetweenContainer.defaultProps = {
  direction: 'row',
};

const MessageBarContainer = styled.div`
  padding: 5px;
  width: 600px;
  height: 110px;
  overflow: clip;
`;

const Scrollable = styled.div<{height: number}>`
  overflow: auto;
  height: ${({height}) => height}px;
`;

const SpaceContainer = styled.div<{width?: number; height?: number; gap?: number}>`
  width: ${({width}) => (width ? `${width}px` : 'auto')};
  height: ${({height}) => (height ? `${height}px` : 'auto')};
  display: flex;
  flex-direction: column;
  gap: ${({gap}) => (gap ? `${gap}px` : '0')};
`;

const fakeFetcher = async (page = 0, searchValue = '') => {
  const items = [
    {
      id: `name_${page}`,
      text: `Name (page ${page})`,
    },
    {
      id: `collection_${page}`,
      text: 'Collection',
    },
    {
      id: `description_${page}`,
      text: 'Description',
    },
    {
      id: `brand_${page}`,
      text: 'Brand',
    },
    {
      id: `response_time_${page}`,
      text: 'Response time (ms)',
    },
    {
      id: `variation_name_${page}`,
      text: 'Variant Name',
    },
    {
      id: `variation_description_${page}`,
      text: 'Variant description',
    },
    {
      id: `release_date_${page}`,
      text: 'Release date',
    },
    {
      id: `release_date_${page}`,
      text: 'Release date',
    },
    {
      id: `asset_collection_${page}`,
      text: 'Asset collection',
    },
    {
      id: `associations_${page}`,
      text: 'Associations',
    },
    {
      id: `enabled_${page}`,
      text: 'Enabled',
    },
    {
      id: `price_${page}`,
      text: 'Price',
    },
    {
      id: `promotion_${page}`,
      text: 'Promotion',
    },
    {
      id: `city_${page}`,
      text: 'City',
    },
    {
      id: `supplier_${page}`,
      text: 'Supplier',
    },
    {
      id: `label_${page}`,
      text: 'Label',
    },
    {
      id: `category_${page}`,
      text: 'Category',
    },
    {
      id: `updated_at_${page}`,
      text: 'Updated at',
    },
    {
      id: `created_at_${page}`,
      text: 'Created at',
    },
  ];
  if (page > 3) return Promise.resolve([]);
  const filteredItems =
    searchValue !== '' && 0 === page ? items.filter(item => -1 !== item.text.indexOf(searchValue)) : items;
  if (searchValue !== '' && page > 0) return Promise.resolve([]);
  return new Promise(resolve => {
    setTimeout(() => resolve(filteredItems), 200);
  });
};

export {
  Content,
  fakeFetcher,
  LabelContainer,
  ListContextContainer,
  MessageBarContainer,
  PreviewCard,
  PreviewContainer,
  PreviewGrid,
  Scrollable,
  Section,
  SpaceBetweenContainer,
  SpaceContainer,
  StoryStyle,
  Subtitle,
};
