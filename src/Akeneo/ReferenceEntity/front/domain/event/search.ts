export const dataReceived = <Item>(items: Item[], total: number, append: boolean) => {
  return {type: 'GRID_DATA_RECEIVED', data: {items}, total, append};
};
