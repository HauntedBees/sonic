<template>
    <v-app>
        <v-snackbar v-model="$store.state.showMessage" :color="$store.state.messageType" timeout="2000" top>{{$store.state.messageText}}</v-snackbar>
        <v-app-bar app color="#2141C6" dark>
            <div class="d-flex align-center font-weight-bold">
                <router-link to="/" class="text-decoration-none" style="color:white">
                    <span v-if="$store.state.auth && $route.path.indexOf('/admin') >= 0" class="d-none d-sm-flex">Sonic Admin View ({{$store.state.username}})</span>
                    <span
                        v-if="!$store.state.auth || $route.path.indexOf('/admin') < 0"
                        class="d-none d-sm-flex">
                        Sonic - The Unethical Consumption Database 
                        <span v-show="issues > 0" style="margin-left:10px;margin-top:2px;font-size:small;font-style:italic"> ({{issues}} issues across {{entities}} companies and brands)</span>
                    </span>
                </router-link>
                <router-link
                    to="/"
                    class="d-flex d-sm-none beesubheader"
                    style="margin-top:7px;margin-right:5px;color:#FFFFFF;text-decoration:none">
                        <span>S</span>
                        <i/>
                </router-link>
                <v-icon class="d-flex d-sm-none" v-if="!onHomePage && !showSearch" @click="ShowSearchBox()">mdi-magnify</v-icon>
                <v-icon class="d-flex d-sm-none" v-if="!onHomePage && showSearch" @click="showSearch=false">mdi-close</v-icon>
                <CompanyAutocomplete v-show="!onHomePage && showSearch" ref="mobilesearch" :class="{'d-flex':showSearch, 'd-sm-none':showSearch}" @select="Select" addtl-style="width: 260px" />
                <CompanyAutocomplete v-show="!onHomePage" :class="{'d-none d-sm-flex': $route.path!=='/'}" @select="Select" addtl-style="width: 400px" />
            </div>
            <v-spacer/>
            <div>
                <v-dialog v-model="feedbackModal" max-width="640px">
                    <template v-slot:activator="{on,attrs}">
                        <v-btn text v-bind="attrs" v-on="on" @click="OpenFeedback(-1)">
                            <span v-show="!showSearch" class="mr-2">Feedback</span>
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
                        <FeedbackForm v-if="feedbackModal" :issueID="issueId" :page="$route.path" modal @close="CloseFeedback" />
                    </v-card>
                </v-dialog>
                <v-btn text to="/admin/company" v-if="$store.state.auth && $route.path.indexOf('/admin') < 0">
                    <v-icon>mdi-account-cog</v-icon>
                </v-btn>
                <v-btn text :to="'/admin/company' + $route.path" v-if="$store.state.auth && $route.name === 'company'">
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
            </div>
        </v-app-bar>
        <v-main>
            <router-view />
        </v-main>
        <v-footer fixed color="#FFDA0C">
            <v-spacer />
            <div>&copy; 2020<span v-if="currentYear>2020">-{{currentYear}}</span> <a href="https://www.hauntedbees.com">Haunted Bees Productions</a></div>
        </v-footer>
    </v-app>
</template>
<style>
    @import "./assets/formatting.css";
    @import "./assets/style.css";
    @import "./assets/materialicons.min.css";
</style>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        name: "App",
        data: () => ({
            feedbackModal: false,
            issueId: -1,
            showSearch: false,
            entities: 0,
            issues: 0
        }),
        computed: {
            onHomePage() { return this.$route.path === "/"; },
            currentYear() { return new Date().getFullYear(); }
        },
        created() {
            bee.get("Counts", "", data => {
                this.issues = data.issues;
                this.entities = data.entities;
            });
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
            },
            ShowSearchBox() {
                this.showSearch = true;
                this.$refs.mobilesearch.Focus();
            }
        }
    };
</script>