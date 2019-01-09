export const dataReceived = <Item>(items: Item[], matchesCount: number, totalCount: number, append: boolean) => {
  return {type: 'GRID_DATA_RECEIVED', data: {items}, matchesCount, totalCount, append};
};
