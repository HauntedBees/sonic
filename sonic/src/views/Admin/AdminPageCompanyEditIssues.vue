<template>
    <v-col cols="12">
        <v-data-table disable-pagination :headers="headers" :items="issues" :loading="$store.state.loading" hide-default-footer>
            <template v-slot:top>
                <v-dialog v-model="dialog" max-width="500px">
                    <template v-slot:activator="{on, attrs}">
                        <div style="text-align: right">
                            <v-btn color="primary" class="mb-2" dark v-bind="attrs" v-on="on">New Issue</v-btn>
                        </div>
                    </template>
                    <v-card>
                        <v-card-title>
                            <span>{{dialogTitle}}</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12">
                                        <v-textarea v-model="editTarget.issue" outlined label="Issue" />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12">
                                        <v-text-field v-model="editTarget.sourceurl" label="Source" />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12">
                                        <v-text-field v-model="editTarget.contentwarning" label="Content Warning (optional)" />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="6">
                                        <v-select :items="issuetypes" label="Type" v-model="editTarget.type" item-text="name" item-value="id" />
                                        <v-switch v-model="editTarget.ongoing" label="Ongoing Issue" />
                                    </v-col>
                                    <v-col cols="6">
                                        <v-menu ref="menustartdate" v-model="menustartdate" :close-on-content-click="false" offset-y min-width="290px" transition="scale-transition">
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-text-field v-model="editTarget.startdate" label="Date" readonly v-bind="attrs" v-on="on"/>
                                            </template>
                                            <v-date-picker ref="pickerstart" v-model="editTarget.startdate" min="1950-01-01" :max="new Date().toISOString().substr(0, 10)" @change="SaveStartDate" />
                                        </v-menu>
                                        <v-menu ref="menuenddate" v-model="menuenddate" :close-on-content-click="false" offset-y min-width="290px" transition="scale-transition">
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-text-field clearable v-model="editTarget.enddate" label="End Date (Optional)" readonly v-bind="attrs" v-on="on"/>
                                            </template>
                                            <v-date-picker ref="pickerend" v-model="editTarget.enddate" min="1950-01-01" :max="new Date().toISOString().substr(0, 10)" @change="SaveEndDate" />
                                        </v-menu>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <SaveButton @save="Save" modal />
                            <v-btn color="blue darken-1" text @click="Close">Cancel</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </template>
            <template v-slot:item.actions="{item}">
                <v-icon small class="mr-2" @click="Edit(item)">mdi-pencil</v-icon>
                <v-icon small class="mr-2" @click="Delete(item)">mdi-delete</v-icon>
            </template>
            <template v-slot:item.type="{item}">
                <v-tooltip top v-if="item.type > 0">
                    <template v-slot:activator="{on, attrs}">
                        <v-icon v-bind="attrs" v-on="on">mdi-{{(issuetypes.filter(e=>e.id===item.type)[0] || {icon:"alpha-x-box"}).icon}}</v-icon>
                    </template>
                    <span>{{(issuetypes.filter(e=>e.id===item.type)[0] || {name:"Error"}).name}}</span>
                </v-tooltip>
            </template>
            <template v-slot:item.issue="{item}">
                <span :style="{'font-weight':highlightedIssueId===item.id?500:'normal','background-color':highlightedIssueId===item.id?'#FFFF00':''}">{{item.issue}}</span>
            </template>
            <template v-slot:item.sourceurl="{item}">
                <a :href="item.sourceurl" target="_blank">{{FormatURL(item.sourceurl)}}</a>
            </template>
            <template v-slot:item.date="{item}">
                {{item.startdate | moment("from")}}
                <span v-if="item.ongoing"> - present</span>
                <span v-if="!item.ongoing && item.enddate"> - {{item.enddate | moment("from")}}</span>
            </template>
        </v-data-table>
    </v-col>
</template>
<script>
    import { bee, beeSecure } from "src/utils/webmethod.js";
    export default {
        props: {
            "companyId": { type: Number, required: true },
        },
        data () {
            return {
                highlightedIssueId: 0, 
                menustartdate: false,
                menuenddate: false,
                dialog: false,
                defaultItem: { id: 0, entity: 0, type: 0, issue: "", sourceurl: "", startdate: null, enddate: null, ongoing: false, contentwarning: "" },
                editTarget: { id: 0, entity: 0, type: 0, issue: "", sourceurl: "", startdate: null, enddate: null, ongoing: false, contentwarning: "" },
                editIdx: -1,
                headers: [
                    { text: "Type", value: "type" },
                    { text: "Date", value: "date", sortable: "false" },
                    { text: "Issue", value: "issue", sortable: "false" },
                    { text: "Source", value: "sourceurl", sortable: "false" },
                    { text: "Actions", value: "actions", sortable: "false" }
                ],
                issues: [],
                issuetypes: []
            }
        },
        computed: {
            dialogTitle() { return this.editTarget.id <= 0 ? "New Issue" : `Editing Issue`; }
        },
        watch: {
            companyId(val) { val && this.LoadIssues(); },
            menustartdate(val) { val && setTimeout(() => (this.$refs.pickerstart.activePicker = "YEAR")); },
            menuenddate(val) { val && setTimeout(() => (this.$refs.pickerend.activePicker = "YEAR")); }
        },
        created() {
            this.highlightedIssueId = this.$route.query.issueId || 0;
            this.LoadIssues();
            this.LoadIssueTypes();
        },
        methods: {
            FormatURL(url) {
                if(url.length < 50) { return url; }
                const matches = url.match(/^(?:https?:)?(?:\/\/)?(?:[^@\n]+@)?(?:www\.)?([^:/\n]+)/);
                if(matches === null || matches.length === 0) {
                    return url.substr(0, 50) + "...";
                } else {
                    return matches[0] + "/...";
                }
            },
            GetTextColor(color) {
                const rgb = ("0x" + color.slice(1).replace(color.length < 5 && /./g, "$&$&"));
                const r = rgb >> 16, g = rgb >> 8 & 255, b = rgb & 255;
                const hsp = Math.sqrt(0.299 * r * r + 0.587 * g * g + 0.114 * b * b);
                return hsp <= 127.5 ? "white" : "black";
            },
            SaveStartDate(date) { this.$refs.menustartdate.save(date); },
            SaveEndDate(date) { this.$refs.menuenddate.save(date); },
            Delete(item) {
                if(confirm("Are you sure you want to delete this issue?")) {
                    const delIdx = this.issues.indexOf(item);
                    beeSecure.delete("Issue", [item.id], () => { this.issues.splice(delIdx, 1); });
                }
            },
            Edit(item) {
                this.editIdx = this.issues.indexOf(item);
                this.editTarget = Object.assign({}, item);
                this.dialog = true;
            },
            Close() {
                this.dialog = false;
                this.$nextTick(() => { 
                    this.editTarget = Object.assign({}, this.defaultItem);
                    this.editIdx = -1;
                });
            },
            Save() {
                this.editTarget.companyid = this.companyId;
                beeSecure.post("Issue", this.editTarget, data => {
                    this.editTarget.id = data.result;
                    if(this.editIdx < 0) {
                        this.issues.push(this.editTarget);
                    } else {
                        this.issues[this.editIdx] = Object.assign(this.issues[this.editIdx], this.editTarget);
                    }
                    this.Close();
                });
            },
            LoadIssues() { beeSecure.get("Issues", [this.companyId], data => {
                this.issues = data.result;
                this.issues.forEach(e => e.ongoing = e.ongoing === "1");
            }); },
            LoadIssueTypes() { bee.get("IssueTypes", "", data => { this.issuetypes = data.result; }); }
        }
    }
</script>