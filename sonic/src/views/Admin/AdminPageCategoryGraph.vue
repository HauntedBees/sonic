<template>
    <v-col cols="12">
        <GraphDisplay :ready="graphLoaded" :nodes="nodes" :links="links" :big="true" />
    </v-col>
</template>
<script>
    import { beeSecure } from "src/utils/webmethod.js";
    export default {
        data: () => ({
            nodes: [],
            links: [],
            graphLoaded: false
        }),
        created() { this.LoadGraph() },
        methods: {
            LoadGraph() {
                beeSecure.get("FullCategoryGraphData", "", data => {
                    this.nodes = data.nodes.map(n => ({
                        id: n.id,
                        name: n.name,
                        label: n.name,
                        selected: false,
                        iconx: NaN, 
                        icony: NaN,
                        size: n.count
                    }));
                    this.links = data.links;
                    this.graphLoaded = true;
                });
            }
        }
    }
</script>