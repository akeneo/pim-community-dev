import {FileInfo} from 'akeneo-design-system';

type FileTemplateInformation = {
  file_info: FileInfo;
  current_sheet: string;
  sheet_names: string[];
  header_cells: string[];
};

export type {FileTemplateInformation};
