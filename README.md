# sonic - a relational database visualization

## wut
A web app that displays relationships between entities and data points about those entities - for example, if A is a parent of B and C, and C has some data, you will see C's 
data point on B's page as a related entity, on A's page as a child entity, and so on. Admin users can add entities, link them, add data points, and assign categories to both the 
entities and data points. Users can search for entities, browse entities and data points by name and category, and view graphs showing the relationships between entities.

This web app was developed for [sonic - an unethical consumption database](https://www.hauntedbees.com/sonic.html), a website that shows corporations and brands, their often 
hidden relationships, and questionable behaviors from them (i.e. companies that use child labor, CEOs assaulting employees).

This code can be used for any relational data, but there is some terminology directly tied to the "corporations and their issues" relationship, most of which is in the `src/utils/lang.js` file. The logic itself is largely agnostic to the intended use.

## license
The source code is licensed with the [GNU Affero General Public License](https://www.gnu.org/licenses/agpl-3.0.en.html) and all text in the code and database are licensed
with the [CC BY-SA 4.0 License](https://creativecommons.org/licenses/by-sa/4.0/legalcode). All corporate logos belong to their respective owners, and the "THERE IS NO SUCH THING
AS ETHICAL CONSUMPTION UNDER CAPITALISM" image is from a meme - source currently unknown.

## why
I got tired of seeing #hip new products from #trendy new companies only to find out they were subsidiaries of garbage corporations.

## requirements
The frontend is a VueJS website. The backend is written in PHP and interacts with a MySQL/MariaDB database.

## dependencies
The following node packages are used:
 * [Vue.js](https://vuejs.org/)
 * [vue-router](https://github.com/vuejs/vue-router)
 * [vuex](https://github.com/vuejs/vuex)
 * [vuex-persist](https://github.com/championswimmer/vuex-persist)
 * [Vue I18n](https://kazupon.github.io/vue-i18n/)
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