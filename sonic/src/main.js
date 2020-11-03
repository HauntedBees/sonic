import Vue from "vue";
import Vuex from "vuex";
import VueI18n from "vue-i18n";
import VuexPersist from "vuex-persist";
import VueMoment from "vue-moment";
import App from "src/App.vue";
import vuetify from "src/plugins/vuetify";
import messages from "src/utils/lang";
import "src/components/components.module";
import router from "src/views/views.module";
Vue.use(VueMoment);
Vue.use(Vuex);
Vue.use(VueI18n);
const i18n = new VueI18n({ locale: "en", messages });
const vuexStorage = new VuexPersist({
    key: "vuex",
    storage: window.localStorage,//window.sessionStorage,
    reducer: state => ({
        auth: state.auth,
        username: state.username
    })
});
const store = new Vuex.Store({
    state: {
        loading: false,
        showMessage: false,
        messageText: "",
        messageType: "",
        auth: false,
        username: "",
        token: ""
    },
    mutations: {
        login(state, res) {
            state.auth = true;
            state.username = res[0];
            state.token = res[1];
            console.log("TOKEN: " + state.token);
        },
        logout(state) {
            state.auth = false;
            state.username = "";
            state.token = "";
        },
        startLoad(state) {
            state.loading = true;
        },
        endLoad(state) {
            state.loading = false;
        },
        triggerError(state, msg) {
            state.showMessage = true;
            state.messageText = msg;
            state.messageType = "error";
        },
        triggerMessage(state, msg) {
            state.showMessage = true;
            state.messageText = msg;
            state.messageType = "success";
        }
    },
    plugins: [vuexStorage.plugin]
});
new Vue({
    vuetify,
    router,
    store,
    i18n,
    render: h => h(App)
}).$mount("#app");
export default store;