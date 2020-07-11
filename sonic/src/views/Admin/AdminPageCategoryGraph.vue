<template>
    <v-col cols="12">
        <div v-if="nodes.length===0" class="graph" style="width:75%; height: 360px">
            <v-progress-circular dark style="left:47%;top:144px" color="#FFFFFF" size="64" width="4" indeterminate />
        </div>
        <GraphDisplay :ready="graphLoaded" :nodes="nodes" :links="links" :big="true" />
        <div class="beesubmessage beebar" v-show="nodes.length === 0" style="text-align:center">
            This may take a moment to load...
        </div>
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
                beeSecure.get("GetFullCategoryGraphData", "", data => {
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