<template>
    <div>
        <GraphDisplay :ready="graphLoaded" :nodes="nodes" :links="links" :big="true" :cached="true" />
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
                bee.get("CachedFullGraphData", "", data => {
                    this.nodes = data.nodes;
                    this.links = data.links;
                    this.graphLoaded = true;
                });
            }
        }
    }
    window.GetGraphDataForCache = () => {
        console.log("STARTING:");
        if(window.unloadedImages < 0) {
            console.log("HOLD UP, LOADING THE IMAGE");
            window.InitializeLogoImages(window.GetGraphDataForCache);
            return;
        }
        bee.get("FullGraphData", "", data => {
            try {
                const res = { success: true };
                res.nodes = data.nodes.map(n => ({
                    id: n.id,
                    name: n.name,
                    img: window.GetLogo(null, n.iconx, n.icony, parseInt(n.img)),
                    selected: false,
                    label: (n.name.indexOf("The")===0?n.name[4]:n.name[0]),
                    size: data.links.filter(e => e.source === n.id).length
                }));
                res.links = data.links.map(l => ({
                    source: l.source,
                    target: l.target,
                    relationtype: l.relationtype            
                }));
                console.log("JSON:");
                console.log(JSON.stringify(res));
            } catch(e) {
                console.log(e);
            }
        });
    }
</script>