<template>
    <div>
        <v-row v-if="noIssues">
            <v-col class="beesubmessage" style="max-width:640px;margin:0 auto;text-align:center">
                We don't have any issues on this. If you have something you'd like to share, click 
                <span class="beelink" @click="GiveDetails()">here</span> to submit some feedback!
            </v-col>
        </v-row>
        <div class="text-center" style="margin-bottom:10px">
            <BeeTopIssue v-for="(item, idx) in topIssues" :key="idx" :item="item" />
        </div>
        <v-list color="#00000000" v-if="standardIssues.length > 0" two-line subheader>
            <BeeIssue v-for="(item, idx) in standardIssues" :item="item" :key="idx" :companyId="companyId" :companyName="companyName" />
        </v-list>
    </div>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        inject: ["triggerFeedback"],
        props: {
            "companyId": { type: Number, required: true },
            "companyName": { type: String, required: true },
            "showAllRelationships": { type: Boolean, required: true }
        },
        data () {
            return {
                headers: [
                    { text: "Type", value: "type" },
                    { text: "Date", value: "date", sortable: "false" },
                    { text: "Issue", value: "issue", sortable: "false" },
                    { text: "Source", value: "sourceurl", sortable: "false" }
                ],
                issues: [],
                noIssues: false
            }
        },
        watch: {
            companyId(val) { val && this.LoadIssues(); },
            showAllRelationships() { this.LoadIssues(); }
        },
        computed: {
            topIssues() {
                const topIssues = {};
                this.issues.forEach(e => {
                    if(e.ongoing && e.showOnTop) {
                        if(topIssues[e.issueTypeId] === undefined) {
                            topIssues[e.issueTypeId] = {
                                id: e.id,
                                issueColor: e.issueColor,
                                issueType: e.issueType,
                                issueIcon: e.issueIcon,
                                sources: [e.sourceurl]
                            };
                        } else {
                            topIssues[e.issueTypeId].sources.push(e.sourceurl);
                        }
                    }
                });
                return topIssues;
            },
            standardIssues() {
                return this.issues.filter(e => !e.ongoing || !e.showOnTop);
            }
        },
        created() { this.LoadIssues(); },
        methods: {
            LoadIssues() {
                this.issues = [];
                bee.get("CompanyIssues", [this.companyId, this.showAllRelationships], data => {
                    this.issues = data.result.filter(i => i.issueTypeId !== 0);
                    this.noIssues = this.issues.length === 0;
                    this.issues.forEach(e => e.ongoing = e.ongoing === "1");
                    const namePaths = [...new Set(data.result.map(i=>i.namepath))];
                    namePaths.forEach(pathStr => {
                        const pathsArr = pathStr.split("|");
                        FillRelationships(pathsArr);
                    });
                });
            },
            GiveDetails() {
                this.triggerFeedback(-1);
            }
        }
    }
    window.companyRelations = {};
    function InitRelationship(entityName) {
        if(window.companyRelations[entityName] === undefined) {
            window.companyRelations[entityName] = {};
        }
        return window.companyRelations[entityName];
    }
    function FillRelationships(namePathArr, idx) {
        idx = idx || 0;
        if(idx === (namePathArr.length - 1)) { return; }
        let me = namePathArr[idx];
        if(me[0] === ">" || me[0] === "[") { me = me.substring(1); }
        let child = namePathArr[idx + 1];
        const childRelType = child[0];
        let relationType = "std";
        if(childRelType === ">") {
            relationType = "inv";
            child = child.substring(1);
        } else if(childRelType === "[") {
            relationType = "misc";
            child = child.substring(1);
        }
        const meR = InitRelationship(me);
        const childR = InitRelationship(child);
        switch(relationType) {
            case "std":
                meR[child] = "c";
                childR[me] = "p";
                break;
            case "inv":
                meR[child] = "m";
                childR[me] = "o";
                break;
            case "misc":
                meR[child] = "x";
                childR[me] = "x";
            break;
        }
        FillRelationships(namePathArr, idx + 1);
    }
</script>