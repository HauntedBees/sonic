<template>
    <v-container class="hasSidebar">
        <DualNav :items="menuItems"/>
        <router-view />
    </v-container>
</template>
<script>
    export default {
        data () {
            return {
                menuItems: [
                    { title: "Companies", icon: "mdi-domain", href: "companies" },
                    { title: "Issue List", icon: "mdi-clipboard-alert", href: "issues" },
                    { title: "Categories", icon: "mdi-shape", href: "categories" },
                    { title: "Graph", icon: "mdi-graph", href: "graph" }
                ]
            }
        }
    }
</script>