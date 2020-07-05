import Vue from "vue";
import Vuex from "vuex";
import VuexPersist from "vuex-persist";
import VueMoment from "vue-moment";
import App from "src/App.vue";
import vuetify from "src/plugins/vuetify";
import "src/components/components.module";
import router from "src/views/views.module";
Vue.use(VueMoment);
Vue.use(Vuex);
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
        username: ""
    },
    mutations: {
        login(state, username) {
            state.auth = true;
            state.username = username;
        },
        logout(state) {
            state.auth = false;
            state.username = "";
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
    render: h => h(App)
}).$mount("#app");
export default store;