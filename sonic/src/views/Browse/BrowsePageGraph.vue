<template>
    <div>
        <div v-if="nodes.length===0" class="graph" style="width:75%; height: 360px">
            <v-progress-circular dark style="left:47%;top:144px" color="#FFFFFF" size="64" width="4" indeterminate />
        </div>
        <GraphDisplay :ready="graphLoaded" :nodes="nodes" :links="links" :big="true" />
        <div class="beesubmessage beebar" v-show="nodes.length === 0" style="text-align:center">
            This may take a moment to load...
        </div>
    </div>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        data: () => ({
            nodes: [],
            links: [],
            graphLoaded: false
        }),
        created() { this.LoadGraph() },
        methods: {
            LoadGraph() {
                bee.get("GetFullGraphData", "", data => {
                    this.nodes = data.nodes.map(n => ({
                        id: n.id,
                        name: n.name,
                        label: (n.name.indexOf("The")===0?n.name[4]:n.name[0]),
                        selected: false,
                        iconx: parseInt(n.iconx), 
                        icony: parseInt(n.icony),
                        size: data.links.filter(e => e.source === n.id).length
                    }));
                    this.links = data.links;
                    this.graphLoaded = true;
                });
            }
        }
    }
</script>