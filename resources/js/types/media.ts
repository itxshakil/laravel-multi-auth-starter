export type Media = {
    id: number;
    model_type: string;
    model_id: number;
    name: string;
    file_name: string;
    mime_type: string;
    path: string;
    disk: string;
    collection: string;
    order: string | null;
    size: number;
    full_url: string;
    extension: string;
    human_readable_size: string;
    deleted_at: string | null;
    created_at: string;
    updated_at: string;
};
