<template>
    <v-col>
        <v-data-table :search="search" :headers="headers" :items="companies" :loading="$store.state.loading">
            <template v-slot:top>
                <v-row>
                    <v-col cols="10">
                        <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" />
                    </v-col>
                    <v-col cols="2">
                        <v-btn color="primary" class="mb-2" dark @click="Edit(null)">New Company</v-btn>
                    </v-col>
                </v-row>
            </template>
            <template v-slot:item.name="{item}"><div style="cursor:pointer" @click="Edit(item)">{{item.name}}</div></template>
            <template v-slot:item.categoryname="{item}"><div style="cursor:pointer" @click="Edit(item)">{{item.categoryname}}</div></template>
        </v-data-table>
    </v-col>
</template>
<script>
    import { beeSecure } from "src/utils/webmethod.js";
    export default {
        data () {
            return {
                search: "",
                headers: [
                    { text: "Company", value: "name", width: "60%" },
                    { text: "Type", value: "categoryname"},
                    { text: "Issues", value: "issues"},
                    { text: "Subsidiaries", value: "children"}
                ],
                companies: []
            }
        },
        created() { this.LoadCompanies(); },
        methods: {
            Edit(item) { 
                this.$router.push("/admin/company/" + (item === null ? "new" : item.name));
            },
            LoadCompanies() { beeSecure.get("GetCompanies", "", data => { this.companies = data.result; }); }
        }
    }
</script>