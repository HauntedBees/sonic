<template>
    <div>
        <GraphDisplay :ready="graphLoaded" :nodes="nodes" :links="links" :big="true" />
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