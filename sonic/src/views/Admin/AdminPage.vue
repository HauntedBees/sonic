<template>
    <v-container class="hasSidebar">
        <DualNav :items="menuItems"/>
        <v-container>
            <v-row class="adminView">
                <router-view />
            </v-row>
        </v-container>
    </v-container>
</template>
<script>
    import { beeSecure } from "src/utils/webmethod.js";
    export default {
        data () {
            return {
                menuItems: [
                    { title: "Company", icon: "mdi-domain", href: "/admin/company" },
                    { title: "Issue Types", icon: "mdi-alert-octagram", href: "/admin/issuetype" },
                    { title: "Category", icon: "mdi-shape", href: "/admin/category" },
                    { title: "Category Graph", icon: "mdi-graph", href: "/admin/categorygraph" },
                    { title: "Feedback", icon: "mdi-comment-quote", href: "/admin/feedback" }
                ]
            }
        },
        beforeRouteEnter(to, from, next) {
            beeSecure.auth(() => {
                next();
            }, () => {
                next("/login");
            });
        }
    }
</script>