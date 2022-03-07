import {FileInfo} from 'akeneo-design-system';

type FileTemplateInformation = {
  file_info: FileInfo;
  current_sheet: string;
  sheets: string[];
  header_cells: string[];
};

export type {FileTemplateInformation};
