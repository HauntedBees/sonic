<template>
    <v-autocomplete
        ref="autocomplete"
        :style="addtlStyle"
        v-model="select"
        :loading="$store.state.loading"
        :items="items"
        :search-input.sync="search"
        :filter="Filter"
        item-text="name"
        item-value="name"
        class="mx-4"
        flat
        hide-no-data
        hide-details
        dark
        clearable
        :label="$t('searchBoxText')">
        <template v-slot:item="{item}">
            <v-list-item-content v-if="item.notFound === true">
                <v-list-item-title v-text="`No results found for '${item.name}'`" />
            </v-list-item-content>
            <v-list-item-content v-if="item.notFound !== true">
                <v-list-item-title v-text="item.name" />
                 <v-list-item-subtitle v-if="item.parent !== null" v-text="'Owned by ' + item.parent" />
            </v-list-item-content>
        </template>
    </v-autocomplete>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        props: {
            "addtlStyle": { type:String }
        },
        data: () => ({
            items: [],
            search: null,
            select: null
        }),
        watch: {
            search(val) { val && val !== this.select && this.Query(val.trim()); },
            select(val) { val && this.$emit("select", val); }
        },
        methods: {
            Focus() { this.$refs.autocomplete.focus(); },
            Filter() { return true; },
            Query(query) {
                if(query === "") { return; }
                bee.get("CompanySearch", [query], data => {
                    this.items = data.result;
                    if(this.items.length === 0) {
                        this.items = [{
                            name: query,
                            notFound: true
                        }]
                    }
                });
            }
        }
    }
</script>