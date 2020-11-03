<template>
    <v-col cols="12">
        <v-data-table :search="search"  :headers="headers" :items="issuetypes" :loading="$store.state.loading">
            <template v-slot:top>
                <v-col cols="10">
                    <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" />
                </v-col>
                <v-col cols="2">
                    <v-dialog v-model="dialog" max-width="500px">
                        <template v-slot:activator="{on, attrs}">
                            <div style="text-align: right">
                                <v-btn color="primary" class="mb-2" dark v-bind="attrs" v-on="on">New Issue Type</v-btn>
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
                                            <v-text-field v-model="editTarget.name" label="Issue Type" />
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="6">
                                            <v-text-field v-model="editTarget.icon" label="Material Icon"></v-text-field>
                                            <div style="text-align:center">
                                                <v-icon>mdi-{{editTarget.icon}}</v-icon>
                                            </div>
                                            <div style="margin-top:20px">
                                                <a rel="external nofollow noopener noreferrer" href="https://materialdesignicons.com/" target="_blank">Icon Reference</a>
                                            </div>
                                            <v-switch v-model="editTarget.showOnTop" label="Ongoing Issue Type" />
                                        </v-col>
                                        <v-col cols="6">
                                            <v-color-picker canvas-height="100" v-model="editTarget.color" hide-mode-switch mode="hexa" />
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
                </v-col>
            </template>
            <template v-slot:item.actions="{item}">
                <v-icon small class="mr-2" @click="Edit(item)">mdi-pencil</v-icon>
            </template>
            <template v-slot:item.icon="{item}">
                <v-icon>mdi-{{item.icon}}</v-icon>
            </template>
            <template v-slot:item.color="{item}">
                <v-chip :color="item.color" :text-color="GetTextColor(item.color)" style="font-weight:bold">{{item.color}}</v-chip>
            </template>
        </v-data-table>
    </v-col>
</template>
<script>
    import { bee, beeSecure } from "src/utils/webmethod.js";
    export default {
        data () {
            return {
                search: "",
                dialog: false,
                defaultItem: { id: 0, name: "", icon: "", color: "#FF0000", showOnTop: false },
                editTarget: { id: 0, name: "", icon: "", color: "#FF0000", showOnTop: false },
                editIdx: -1,
                headers: [
                    { text: "Issue Type", value: "name" },
                    { text: "Display Icon", value: "icon", sortable: "false" },
                    { text: "Display Color", value: "color", sortable: "false" },
                    { text: "Actions", value: "actions", sortable: "false" }
                ],
                issuetypes: []
            }
        },
        computed: {
            dialogTitle() { return this.editTarget.id === 0 ? "New Issue Type" : `Editing '${this.editTarget.name}'`; }
        },
        created() { this.LoadIssueTypes(); },
        methods: {
            GetTextColor(color) {
                const rgb = ("0x" + color.slice(1).replace(color.length < 5 && /./g, "$&$&"));
                const r = rgb >> 16, g = rgb >> 8 & 255, b = rgb & 255;
                const hsp = Math.sqrt(0.299 * r * r + 0.587 * g * g + 0.114 * b * b);
                return hsp <= 127.5 ? "white" : "black";
            },
            Edit(item) {
                this.editIdx = this.issuetypes.indexOf(item);
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
                beeSecure.post("IssueType", this.editTarget, data => {
                    this.editTarget.id = data.result;
                    if(this.editIdx < 0) {
                        this.issuetypes.push(this.editTarget);
                    } else {
                        this.issuetypes[this.editIdx] = Object.assign(this.issuetypes[this.editIdx], this.editTarget);
                    }
                    this.Close();
                });
            },
            LoadIssueTypes() { bee.get("IssueTypes", "", data => { this.issuetypes = data.result; }); }
        }
    }
</script>