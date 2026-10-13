import app from 'flarum/common/app';
import flatten from 'flat';
import jsYaml from 'js-yaml';
import fs from 'fs';
import path from 'path';

/** Loads the extension's own strings; the test bootstrap only loads core's. */
export default function loadTranslations() {
  const locale = path.resolve(process.cwd(), '../locale/en.yml');

  app.translator.addTranslations(flatten(jsYaml.load(fs.readFileSync(locale, 'utf8'))));
}
