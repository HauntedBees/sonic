<template>
    <v-col cols="12">
        <v-data-table :search="search" :headers="headers" :items="categories" :loading="$store.state.loading">
            <template v-slot:top>
                <v-col cols="10">
                    <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" />
                </v-col>
                <v-col cols="2">
                    <v-dialog v-model="dialog" max-width="500px">
                        <template v-slot:activator="{on, attrs}">
                            <div style="text-align: right">
                                <v-btn color="primary" class="mb-2" dark v-bind="attrs" v-on="on">New Category</v-btn>
                            </div>
                        </template>
                        <v-card>
                            <v-card-title>
                                <span>{{dialogTitle}}</span>
                            </v-card-title>
                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="4">
                                            <v-text-field v-model="editTarget.icon" label="Material Icon"></v-text-field>
                                            <div style="text-align:center">
                                                <v-icon>mdi-{{editTarget.icon}}</v-icon>
                                            </div>
                                            <div style="margin-top:20px">
                                                <a rel="external nofollow noopener noreferrer" href="https://materialdesignicons.com/" target="_blank">Icon Reference</a>
                                            </div>
                                        </v-col>
                                        <v-col cols="8">
                                            <v-text-field v-model="editTarget.name" label="Category Name/Description" />
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col>
                                            <v-autocomplete
                                                v-model="editTarget.parents"
                                                :loading="$store.state.loading"
                                                :items="categories"
                                                :search-input.sync="searchParent"
                                                clearable
                                                cache-items
                                                flat
                                                hide-no-data
                                                hide-details
                                                item-text="name"
                                                item-value="id"
                                                multiple
                                                label="Specify Parent Categories"
                                                />
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
            <template v-slot:item.icon="{item}">
                <v-icon>mdi-{{item.icon}}</v-icon>
            </template>
            <template v-slot:item.actions="{item}">
                <v-icon small class="mr-2" @click="Edit(item)">mdi-pencil</v-icon>
            </template>
        </v-data-table>
    </v-col>
</template>
<script>
    import { beeSecure } from "src/utils/webmethod.js";
    export default {
        data () {
            return {
                searchParent: "",
                search: "",
                dialog: false,
                defaultItem: { id: 0, icon: "", parents: [], name: "" },
                editTarget: { id: 0, icon: "", parents: [], name: "" },
                editIdx: -1,
                headers: [
                    { text: "", value: "icon" },
                    { text: "Category", value: "name" },
                    { text: "Businesses", value: "count" },
                    { text: "Actions", value: "actions", sortable: "false" }
                ],
                categories: []
            }
        },
        computed: {
            dialogTitle() { return this.editTarget.id === 0 ? "New Category" : `Editing '${this.editTarget.name}'`; }
        },
        created() { this.LoadCategories(); },
        methods: {
            Edit(item) {
                this.editIdx = this.categories.indexOf(item);
                this.editTarget = Object.assign({}, item);
                beeSecure.get("CategoryParents", [this.editTarget.id], data => {
                    this.editTarget.parents = data.result;
                    this.dialog = true;
                });
            },
            Close() {
                this.dialog = false;
                this.$nextTick(() => { 
                    this.editTarget = Object.assign({}, this.defaultItem);
                    this.editIdx = -1;
                });
            },
            Save() {
                beeSecure.post("Category", this.editTarget, data => {
                    this.editTarget.id = data.result;
                    if(this.editIdx < 0) {
                        this.categories.push(this.editTarget);
                    } else {
                        this.categories[this.editIdx] = Object.assign(this.categories[this.editIdx], this.editTarget);
                    }
                    this.Close();
                });
            },
            LoadCategories() { beeSecure.get("Categories", "", data => { this.categories = data.result; }); }
        }
    }
</script>