<template>
    <v-container>
        <v-row>
            <v-col cols="2" class="d-none d-sm-block">
                <h2 class="beesubmessage" style="margin-bottom:20px">Issues ({{count}})</h2>
            </v-col>
            <v-col>
                <v-chip-group v-model="selectedIdxes" column multiple>
                    <v-chip dark v-for="it in issuetypes" :key="it.id" class="selectionchip" :style="{ 'border-right': `2px solid ${it.color}`, 'border-bottom': `2px solid ${it.color}` }">
                        <v-icon>mdi-{{it.icon}}</v-icon> {{it.name}}
                    </v-chip>
                </v-chip-group>
            </v-col>
        </v-row>
        <v-row>
            <v-list color="#00000000" v-if="issues.length > 0" two-line subheader>
                <BeeIssue v-for="item in issues" :item="item" :key="item.id" :companyId="0" />
            </v-list>
        </v-row>
        <div v-show="!endOfList" class="row" ref="loadBottom" style="margin-bottom: 40px; margin-top: 20px">
            <v-progress-circular style="margin: 0 auto" color="#F90018" size="64" width="2" indeterminate />
        </div>
        <div v-show="endOfList" class="row" style="margin-bottom: 40px; margin-top: 20px">
            <BeeSubheader style="margin: 0 auto" text="That's everything we have right now."/>
        </div>
    </v-container>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        data () {
            return {
                offset: 0,
                count: 0,
                selectedIdxes: [],
                selectedTypes: [],
                issues: [],
                issuetypes: [],
                endOfList: false, 
                observer: null
            }
        },
        watch: {
            selectedIdxes() {
                this.selectedTypes = this.selectedIdxes.map(i=>this.issuetypes[i].id);
                this.endOfList = false;
                this.issues = [];
                this.offset = 0;
                this.LoadIssues();
            }
        },
        beforeMount() {
            this.LoadIssues();
            this.LoadIssueTypes();
        },
        mounted() {
            this.observer = new IntersectionObserver(() => this.ReachedBottom(), { root: null, threshold: 1 });
            this.observer.observe(this.$refs.loadBottom);
        },
        methods: {
            ReachedBottom() {
                if(this.issues.length === 0 || this.$store.state.loading) { return; }
                this.LoadIssues();
            },
            LoadIssueTypes() { bee.get("IssueTypes", "", data => { this.issuetypes = data.result; }); },
            LoadIssues() {
                bee.get("IssuesPage", [this.selectedTypes, this.offset], data => {
                    const resItems = data.result;
                    this.count = data.count;
                    if(resItems.length === 0) {
                        this.endOfList = true;
                    } else {
                        this.endOfList = resItems.length < 15; // TODO: this probably works; adjust if page size changes on server
                        resItems.forEach(e => e.ongoing = e.ongoing === "1");
                        this.issues.push(...resItems);
                        this.offset += 1;
                    }
                });
            }
        }
    }
</script>