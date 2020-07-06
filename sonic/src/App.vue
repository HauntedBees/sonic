<template>
    <v-app>
        <v-snackbar v-model="$store.state.showMessage" :color="$store.state.messageType" timeout="2000" top>{{$store.state.messageText}}</v-snackbar>
        <v-app-bar app color="#2141C6" dark>
            <div class="d-flex align-center font-weight-bold">
                <router-link to="/" class="text-decoration-none" style="color:white">
                    <span class="d-flex d-sm-none">Sonic</span>
                    <span v-if="$store.state.auth && $route.path.indexOf('/admin') >= 0" class="d-none d-sm-flex">Sonic Admin View ({{$store.state.username}})</span>
                    <span v-if="!$store.state.auth || $route.path.indexOf('/admin') < 0" class="d-none d-sm-flex">Sonic - The Unethical Consumption Database</span>
                </router-link>
                <v-icon class="d-flex d-sm-none" v-if="!showSearch" style="margin-left:20px" @click="showSearch=true">mdi-magnify</v-icon>
                <CompanyAutocomplete class="d-flex d-sm-none" v-if="showSearch" @select="Select" addtl-style="width: 260px" />
                <CompanyAutocomplete class="d-none d-sm-flex" @select="Select" addtl-style="width: 400px" />
            </div>
            <v-spacer/>
            <v-dialog v-model="feedbackModal" max-width="640px">
                <template v-slot:activator="{on,attrs}">
                    <v-btn text v-bind="attrs" v-on="on" @click="OpenFeedback(-1)">
                        <span class="mr-2">Feedback</span>
                        <v-icon>mdi-comment-quote</v-icon>
                    </v-btn>
                </template>
                <v-card color="#FFDA0C">
                    <v-card-title>
                        Feedback
                        <v-spacer/>
                        <v-btn icon @click="feedbackModal = false">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-card-title>
                    <FeedbackForm :issueID="issueId" :page="$route.path" modal @close="CloseFeedback" />
                </v-card>
            </v-dialog>
            <!--<v-btn text to="/login" v-if="!$store.state.auth">
                <span class="mr-2">Log In</span>
                <v-icon>mdi-keyboard-return</v-icon>
            </v-btn>-->
            <v-btn text to="/admin/company" v-if="$store.state.auth && $route.path.indexOf('/admin') < 0">
                <v-icon>mdi-account-cog</v-icon>
            </v-btn>
            <v-btn text :to="'/admin/company' + $route.path" v-if="$store.state.auth && $route.name === 'company'">
                <v-icon>mdi-pencil</v-icon>
            </v-btn>
        </v-app-bar>
        <v-main>
            <router-view />
        </v-main>
        <v-footer fixed color="#E7E700">
            <v-spacer />
            <div>&copy; 2020<span v-if="currentYear>2020">-{{currentYear}}</span> <a href="https://www.hauntedbees.com">Haunted Bees Productions</a></div>
        </v-footer>
    </v-app>
</template>
<style>
    @import "./assets/style.css";
    @import "./assets/materialicons.min.css";
</style>
<script>
    export default {
        name: "App",
        data: () => ({
            feedbackModal: false,
            issueId: -1,
            showSearch: false
        }),
        computed: {
            currentYear() { return new Date().getFullYear(); }
        },
        provide: function() {
            const me = this;
            return { triggerFeedback: (item) => me.OpenFeedback(item.id) };
        },
        methods: {
            CloseFeedback() { this.feedbackModal = false; },
            Select(val) {
                if(`/${val}` === this.$route.path) { return; }
                this.showSearch = false;
                this.$router.push(`/${val}`);
            },
            OpenFeedback(id) {
                this.issueId = parseInt(id);
                this.feedbackModal = true;
            }
        }
    };
</script>