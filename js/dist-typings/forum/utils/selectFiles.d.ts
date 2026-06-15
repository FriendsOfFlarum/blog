/**
 * Asynchronously select file(s).
 *
 * @param contentType The content type of files you wish to select. For instance, use "image/*" to select all types of images.
 * @param multiple Indicates if the user can select multiple files.
 * @returns A promise of a file or array of files in case the multiple parameter is true.
 */
declare function selectFiles(contentType: string, multiple: false): Promise<File>;
declare function selectFiles(contentType: string, multiple: true): Promise<File[]>;
export default selectFiles;
