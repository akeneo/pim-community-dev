export type CategoryPermission = {
  id: number;
  label: string;
};

export type CategoryPermissions = {
  view: CategoryPermission[];
  edit: CategoryPermission[];
  own: CategoryPermission[];
};
