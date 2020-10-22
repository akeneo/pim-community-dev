/* tslint:disable */
export interface Announcement {
  id: string;
  title: string;
  description: string;
  img: string | null;
  altImg: string | null;
  link: string;
  tags: string[];
  startDate: string;
}
