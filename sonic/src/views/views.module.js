import Vue from "vue";
import VueRouter from "vue-router";
import LandingPage from "src/views/LandingPage";
import CompanyInfoPage from "src/views/CompanyInfoPage";
import LoginPage from "src/views/LoginPage";
import InfoRoute from "src/views/Info/InfoPage.module";
import AdminRoute from "src/views/Admin/AdminPage.module";
import BrowseRoute from "src/views/Browse/BrowsePage.module";
Vue.use(VueRouter);
const routes = [
    {
        path: "/",
        component: LandingPage,
        meta: {
            title: "Sonic - The Unethical Consumption Database"
        }
    },
    {
        path: "/login",
        component: LoginPage,
        meta: {
            title: "You shouldn't be here."
        }
    },
    InfoRoute,
    AdminRoute,
    BrowseRoute,
    {
        path: "/:id",
        name: "company",
        component: CompanyInfoPage,
        meta: {
            titleFunc: to => `Sonic - ${to.params.id}`
        }
    }
];
const router = new VueRouter({
    routes,
    scrollBehavior() { return { x: 0, y: 0 }; }
});
router.afterEach(to => {
    Vue.nextTick(() => {
        document.title = (to.meta.titleFunc ? to.meta.titleFunc(to) : to.meta.title) || "Sonic - The Unethical Consumption Database";
    });
});
export default router;