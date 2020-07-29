# sonic - an unethical consumption database

## wut
A resource for tracking shitty things companies do, and finding out what companies own the companies you think aren't as shitty.

## license
The source code is licensed with the [GNU Affero General Public License](https://www.gnu.org/licenses/agpl-3.0.en.html) and all text is licensed with the [CC BY-SA 4.0 License](https://creativecommons.org/licenses/by-sa/4.0/legalcode).

## why
I got tired of seeing #hip new products from #trendy new companies only to find out they were subsidiaries of garbage corporations.

## requirements
For the frontend it's just a website. To make meaningful changes it'd probably be a good idea to get [Node.js](https://nodejs.org/en/) and run the build steps below. The backend uses PHP and interacts with a MySQL or MariaDB database.

## dependencies
The following node packages are used:
 * [Vue.js](https://vuejs.org/)
 * [vue-router](https://github.com/vuejs/vue-router)
 * [vuex](https://github.com/vuejs/vuex)
 * [vuex-persist](https://github.com/championswimmer/vuex-persist)
 * [Vuetify](https://vuetifyjs.com/en/)
 * [core-js](https://github.com/zloirock/core-js)
 * [Cytoscape.js](https://js.cytoscape.org/)
 * [cytoscape-popper](https://github.com/cytoscape/cytoscape.js-popper)
 * [cytoscape-fcose](https://github.com/iVis-at-Bilkent/cytoscape.js-fcose)
   * U. Dogrusoz, E. Giral, A. Cetintas, A. Civril, and E. Demir, "[A Layout Algorithm For Undirected Compound Graphs](http://www.sciencedirect.com/science/article/pii/S0020025508004799)", Information Sciences, 179, pp. 980-994, 2009.
   * A. Civril, M. Magdon-Ismail, and E. Bocek-Rivele, "[SSDE: Fast Graph Drawing Using Sampled Spectral Distance Embedding](https://link.springer.com/chapter/10.1007/978-3-540-70904-6_5)", International Symposium on Graph Drawing, pp. 30-41, 2006.
 * [Popper](https://popper.js.org/)
 * [Tippy.js](https://atomiks.github.io/tippyjs/)
 * [vue-moment](https://github.com/brockpetrie/vue-moment)
 * [Moment.js](https://momentjs.com/)
 * [FileSaver.js](https://github.com/eligrey/FileSaver.js)

As are the following dev dependencies:
 * [vue-template-compiler](https://www.npmjs.com/package/vue-template-compiler)
 * [vue-cli](https://cli.vuejs.org/)
 * [vue-cli service](https://cli.vuejs.org/guide/cli-service.html)
 * [vue-cli babel plugin](https://cli.vuejs.org/core-plugins/babel.html)
 * [vue-cli eslint plugin](https://cli.vuejs.org/core-plugins/eslint.html)
 * [vue-cli router plugin](https://www.npmjs.com/package/@vue/cli-plugin-router)
 * [vue-cli vuex plugin](https://www.npmjs.com/package/@vue/cli-plugin-vuex)
 * [vue-cli vuetify plugin](https://github.com/vuetifyjs/vue-cli-plugins/tree/master/packages/vue-cli-plugin-vuetify)
 * [vuetify-loader](https://github.com/vuetifyjs/vuetify-loader)
 * [babel-eslint](https://github.com/babel/babel-eslint)
 * [ESLint](https://eslint.org/)
 * [es-plugin-vue](https://github.com/vuejs/eslint-plugin-vue)
 * [Sass](https://sass-lang.com/)
 * [sass-loader](https://webpack.js.org/loaders/sass-loader/)

For the webservice, the following PHP library is used:
 * [Gregwar Captcha](https://github.com/Gregwar/Captcha)

## building
### setup
```
npm install
```
### compile for development
```
npm run serve
```
### compile and minify for production
```
npm run build
```
### lint and fix files
```
npm run lint
```
### custom configuration
See [Configuration Reference](https://cli.vuejs.org/config/).

## want to make changes?
cool. do the forking and submitting or whatever. if it's good stuff I'll probably merge it up. if your issue is with the data itself on the live site or in the data.sql file here, [please use the Feedback page](https://www.hauntedbees.com/sonic.html#/info/feedback) or contact me through one of the methods described there.