export type Extension = {
    id: string;
    name: string;
    logo: string;
    author: string;
    partner: string | null;
    description: string | null;
    url: string;
    categories: string[];
    certified: boolean;
};

export type Extensions = {
    total: number;
    extensions: Extension[];
};
