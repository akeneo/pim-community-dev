export enum Display {
  List = 'list',
  Gallery = 'gallery',
}

export const changeGridDisplay = (display: Display) => {
  return {type: 'CHANGE_GRID_DISPLAY', display};
};
