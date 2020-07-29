<template>
    <v-autocomplete
        ref="autocomplete"
        :style="addtlStyle"
        v-model="select"
        :loading="$store.state.loading"
        :items="items"
        :search-input.sync="search"
        :filter="Filter"
        class="mx-4"
        flat
        hide-no-data
        hide-details
        dark
        clearable
        label="Search for a company or brand." />
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
            select(val) { val && this.$emit("select", val.trim()); }
        },
        methods: {
            Focus() { this.$refs.autocomplete.focus(); },
            Filter(/*item, query, itemText*/) {
                // TODO: make me matter?
                //console.log(item, query, itemText);
                return true;
            },
            Query(query) {
                if(query === "") { return; }
                bee.get("SearchCompanies", query, data => { this.items = data.result.map(e => e.name); });
            }
        }
    }
</script>