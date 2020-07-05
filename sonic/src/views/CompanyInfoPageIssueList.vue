<template>
    <div>
        <v-row v-if="noIssues">
            <v-col class="beesubmessage" style="max-width:640px;margin:0 auto;text-align:center">
                We don't have any issues on this. If you have something you'd like to share, click 
                <span class="beelink" @click="GiveDetails()">here</span> to submit some feedback!
            </v-col>
        </v-row>
        <v-list color="#00000000" v-if="issues.length > 0" two-line subheader>
            <BeeIssue v-for="item in issues" :item="item" :key="item.id" :companyId="companyId" :companyName="companyName" :namePaths="namePaths" />
        </v-list>
    </div>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        inject: ["triggerFeedback"],
        props: {
            "companyId": { type: Number, required: true },
            "companyName": { type: String, required: true }
        },
        data () {
            return {
                headers: [
                    { text: "Type", value: "type" },
                    { text: "Date", value: "date", sortable: "false" },
                    { text: "Issue", value: "issue", sortable: "false" },
                    { text: "Source", value: "sourceurl", sortable: "false" }
                ],
                namePaths: [],
                issues: [],
                noIssues: false
            }
        },
        watch: {
            companyId(val) { val && this.LoadIssues(); }
        },
        created() { this.LoadIssues(); },
        methods: {
            LoadIssues() {
                bee.get("GetAllIssues", this.companyId, data => {
                    this.issues = data.result;
                    this.noIssues = this.issues.length === 0;
                    this.namePaths = [...new Set(this.issues.map(i=>i.namepath))];
                    this.issues.forEach(e => e.ongoing = e.ongoing === "1");
                });
            },
            GiveDetails() {
                this.triggerFeedback(-1);
            }
        }
    }
</script>