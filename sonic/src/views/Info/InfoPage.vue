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
                    { title: "About", icon: "mdi-information-variant", href: "about" },
                    { title: "Privacy", icon: "mdi-lock", href: "privacy" },
                    { title: "FAQ", icon: "mdi-comment-question", href: "faq" },
                    { title: "Feedback", icon: "mdi-comment-quote", href: "feedback" },
                    { title: "Credits", icon: "mdi-account-group", href: "credits" }
                ]
            }
        }
    }
</script>