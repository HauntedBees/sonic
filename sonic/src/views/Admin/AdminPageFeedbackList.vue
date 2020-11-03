<template>
    <v-col cols="12">
        <v-data-table :headers="headers" :items="items" :loading="$store.state.loading">
            <template v-slot:item.name="{item}">
                <span v-if="item.name && item.contact">
                    {{item.name}} ({{item.contact}})
                </span>
                <span v-if="item.name && !item.contact">{{item.name}}</span>
                <span v-if="!item.name && item.contact">{{item.contact}}</span>
            </template>
            <template v-slot:item.path="{item}">
                <!--<router-link v-if="item.issue > 0" :to="'/admin/company/' + item.issueParent">{{item.issueParent}}*</router-link>-->
                <router-link v-if="item.issue > 0" :to="{ path: '/admin/company/' + item.issueParent, query: { issueId: item.issue } }">{{item.issueParent}}*</router-link>
                <router-link v-if="item.issue === null" :to="'/admin/company/' + item.path">{{item.path}}</router-link>
            </template>
            <template v-slot:item.actions="{item}">
                <v-icon small class="mr-2" @click="Dismiss(item)">mdi-check</v-icon>
            </template>
        </v-data-table>
    </v-col>
</template>
<script>
    import { beeSecure } from "src/utils/webmethod.js";
    export default {
        data () {
            return {
                headers: [
                    { text: "Date", value: "date" },
                    { text: "Source", value: "path" },
                    { text: "Feedback", value: "text" },
                    { text: "Submitted by", value: "name", sortable: false },
                    { text: "Dismiss", value: "actions", sortable: "false" }
                ],
                items: []
            }
        },
        created() { this.LoadItems(); },
        methods: {
            Dismiss(item) {
                if(confirm("Dismiss this feedback?")) {
                    const dismissIdx = this.items.indexOf(item);
                    beeSecure.delete("Feedback", [item.id], () => { this.items.splice(dismissIdx, 1); });
                }
            },
            LoadItems() { beeSecure.get("Feedback", "", data => { this.items = data.result; }); }
        }
    }
</script>