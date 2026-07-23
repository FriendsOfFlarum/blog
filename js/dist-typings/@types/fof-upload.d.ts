/**
 * fof/upload is an optional integration and is not installed in this dev
 * environment, so its typings are shimmed with the minimal surface this
 * extension consumes.
 */
declare module 'ext:fof/upload/forum/handler/Uploader' {
  export default class Uploader {}
}

declare module 'ext:fof/upload/forum/components/FileManagerModal' {
  const FileManagerModal: any;
  export default FileManagerModal;
}
